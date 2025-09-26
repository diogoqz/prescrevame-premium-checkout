<?php
session_start();

// Carregar configurações
require_once '../settings.php';
header('Referrer-Policy: no-referrer');

// Função para fazer requisições à API
function makeApiRequest($endpoint, $method = 'GET', $data = null) {
    $url = ABACATE_API_BASE_URL . $endpoint;
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . ABACATE_API_KEY,
        'Accept: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("Erro de conexão: " . $error);
    }
    
    $result = json_decode($response, true);
    
    if ($httpCode >= 400) {
        $errorMsg = isset($result['error']) ? $result['error'] : "Erro na API: HTTP $httpCode";
        throw new Exception($errorMsg);
    }
    
    return $result;
}

// Função para enviar webhook
function sendWebhook($event, $data) {
    $webhookUrl = getWebhookUrl();
    if (!$webhookUrl) {
        return false;
    }
    $payload = [
        'event' => $event,
        'timestamp' => date('c'),
        'data' => $data
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhookUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: PrescrevaMe-Webhook/1.0'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log do webhook (opcional)
    error_log("Webhook enviado: $event - HTTP $httpCode - $response");
    
    return $httpCode >= 200 && $httpCode < 300;
}

// Função para validar CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11) {
        return false;
    }
    
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}

// Função para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Função para formatar CPF
function formatarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

// Obter configurações do desconto renovação
$config = getDescontoRenovacaoConfig();

// Processar formulário
$errors = [];
$success = false;
$pixData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'generate_pix') {
        try {
            // Validar dados
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $codigo_pais = $_POST['codigo_pais'] ?? '+55';
            $cpf = trim($_POST['cpf'] ?? '');
            
            if (empty($nome)) {
                $errors[] = 'Nome é obrigatório';
            }
            
            if (empty($email) || !validarEmail($email)) {
                $errors[] = 'E-mail inválido';
            }
            
            if (empty($telefone)) {
                $errors[] = 'Telefone é obrigatório';
            }
            
            // Validar código do país
            if (empty($codigo_pais) || !preg_match('/^\+\d{1,3}$/', $codigo_pais)) {
                $errors[] = 'Código do país inválido';
            }
            
            if (empty($cpf) || !validarCPF($cpf)) {
                $errors[] = 'CPF inválido';
            }
            
            if (empty($errors)) {
                // Preparar dados para a API
                $apiData = [
                    'amount' => $config['price'],
                    'expiresIn' => $config['expires_in'],
                    'description' => $config['name'],
                    'customer' => [
                        'name' => $nome,
                        'cellphone' => $codigo_pais . ' ' . $telefone,
                        'email' => $email,
                        'taxId' => preg_replace('/[^0-9]/', '', $cpf)
                    ],
                    'externalId' => 'prescreva-me-desconto-renovacao-' . time()
                ];
                
                // Chamar API para criar PIX
                $response = makeApiRequest('/pixQrCode/create', 'POST', $apiData);
                
                if (isset($response['data'])) {
                    $pixData = $response['data'];
                    $_SESSION['pix_data'] = $pixData;
                    $_SESSION['customer_data'] = [
                        'nome' => $nome,
                        'email' => $email,
                        'telefone' => $telefone,
                        'cpf' => formatarCPF($cpf)
                    ];
                    $success = true;
                    
                    // Disparar webhook quando PIX for gerado
                    $webhookData = [
                        'pix_id' => $pixData['id'],
                        'amount' => $apiData['amount'],
                        'customer' => [
                            'name' => $nome,
                            'email' => $email,
                            'phone' => $telefone,
                            'cpf' => formatarCPF($cpf)
                        ],
                        'pix_data' => $pixData,
                        'campaign' => 'desconto-renovacao'
                    ];
                    
                    if (isWebhookEnabled()) {
                        sendWebhook('pix.created', $webhookData);
                    }
                } else {
                    $errors[] = 'Erro ao gerar PIX: ' . ($response['error'] ?? 'Erro desconhecido');
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Erro: ' . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'check_payment') {
        try {
            if (isset($_SESSION['pix_data']['id'])) {
                $response = makeApiRequest('/pixQrCode/check?id=' . $_SESSION['pix_data']['id']);
                
                if (isset($response['data'])) {
                    $pixData = $response['data'];
                    $_SESSION['pix_data'] = $pixData;
                    
                    // Retornar JSON para AJAX
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => $pixData['status'],
                        'id' => $pixData['id'],
                        'expiresAt' => $pixData['expiresAt'] ?? null
                    ]);
                    exit;
                }
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao verificar pagamento: ' . $e->getMessage()]);
            exit;
        }
    }
}

// Se PIX foi pago, redirecionar para recibo
if (isset($_SESSION['pix_data']['status']) && $_SESSION['pix_data']['status'] === 'PAID') {
    header('Location: receipt.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['name']; ?> - <?php echo COMPANY_NAME; ?></title>
    <meta name="description" content="Finalize seu pagamento de forma segura">
    <meta name="referrer" content="no-referrer">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="<?php echo COMPANY_LOGO_URL; ?>" alt="<?php echo COMPANY_NAME; ?>" class="logo">
            <h1><?php echo $config['header_title']; ?></h1>
            <h2><?php echo $config['header_description']; ?></h2>
            <h4><?php echo $config['header_subdescription']; ?></h4>
        </div>

        <!-- Product Summary (compact) -->
        <div class="card product-summary">
            <img class="summary-thumb" src="<?php echo $config['image_url']; ?>" alt="<?php echo $config['header_title']; ?>">
            <div class="summary-text">
                <div class="summary-title"><?php echo $config['header_title']; ?></div>
                <div class="summary-subtitle">Autor: <?php echo COMPANY_FULL_NAME; ?></div>
                <div class="summary-note"><?php echo $config['summary_note']; ?></div>
            </div>
            <div class="summary-price">
                <span class="summary-original"><?php echo $config['original_price_display']; ?></span>
                <span class="summary-discount"><?php echo $config['price_display']; ?></span>
            </div>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="card">
                <div class="status-message status-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <?php if (!$success): ?>
            <div class="card">
                <form method="POST" id="checkout-form">
                    <input type="hidden" name="action" value="generate_pix">
                    
                    <div class="form-group">
                        <label class="form-label">Nome completo *</label>
                        <input 
                            type="text" 
                            name="nome" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">E-mail *</label>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">WhatsApp *</label>
                        <div class="phone-input-group">
                            <input type="text" name="codigo_pais" class="form-input country-code" 
                                   value="<?php echo htmlspecialchars($_POST['codigo_pais'] ?? '+55'); ?>" 
                                   placeholder="+55" maxlength="4">
                            <input 
                                type="tel" 
                                name="telefone"
                                class="form-input phone-number" 
                                value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>"
                                placeholder="(11) 99999-9999"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">CPF *</label>
                        <input 
                            type="text" 
                            name="cpf" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>"
                            placeholder="000.000.000-00"
                            maxlength="14"
                            required
                        >
                    </div>

                    <?php 
                        $economiaCentavos = max(0, $config['original_price'] - $config['price']);
                        $economia = 'R$ ' . number_format($economiaCentavos / 100, 2, ',', '.');
                    ?>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <span id="btn-text">Gerar PIX para Pagamento</span>
                        <div id="loading-spinner" class="loading-spinner hidden"></div>
                    </button>
                    <div class="saving-hint">Ao pagar com PIX você economiza <?php echo $economia; ?>.</div>
                </form>
                
                <!-- Badges de Segurança -->
                <div class="security-badges">
                    <img src="../site-seguro.png" alt="Badges de Segurança - Site Certificado" class="security-badges-img">
                </div>
                
                <!-- Termos e Condições -->
                <div class="terms-disclaimer">
                    <p class="terms-text">
                        <?php echo TERMS_TEXT; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- PIX Payment -->
        <?php if ($success && $pixData): ?>
            <div class="card pix-payment">
                <h2 style="margin-bottom: 16px; color: var(--text-dark);">Pagamento via PIX</h2>
                
                <div class="pix-qr-container">
                    <?php if (!empty($pixData['brCodeBase64'])): ?>
                        <img src="<?php echo htmlspecialchars($pixData['brCodeBase64']); ?>" 
                             alt="QR Code PIX" class="pix-qr-code">
                    <?php elseif (!empty($pixData['qrCodeImage'])): ?>
                        <img src="data:image/png;base64,<?php echo htmlspecialchars($pixData['qrCodeImage']); ?>" 
                             alt="QR Code PIX" class="pix-qr-code">
                    <?php else: ?>
                        <div id="qrcode" class="pix-qr-code"></div>
                    <?php endif; ?>
                </div>

                <div class="pix-copy-section">
                    <div class="pix-code"><?php echo $pixData['brCode']; ?></div>
                    <button class="copy-btn" onclick="copyPixCode()">
                        <span id="copy-text">Copiar código PIX</span>
                    </button>
                </div>

                <div id="countdown" class="countdown">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                        </svg>
                        <span>PIX expira em:</span>
                        <span id="countdown-timer" style="font-weight: 700; color: var(--warning-orange);"><?php echo isset($pixData['expiresAt']) ? 'Calculando...' : $config['expires_display']; ?></span>
                    </div>
                </div>

                <div id="payment-status" class="status-message status-warning">
                    ⏳ Aguardando pagamento...
                </div>

                <div class="security-info">
                    <div class="security-item">
                        <span>🔒</span>
                        <span>Pagamento 100% seguro</span>
                    </div>
                    <div class="security-item">
                        <span>⚡</span>
                        <span>Aprovação instantânea</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Formatação do CPF
        const cpfInput = document.querySelector('input[name="cpf"]');
        if (cpfInput) {
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            });
        }

        // Formatação do código do país
        const countryCodeInput = document.querySelector('input[name="codigo_pais"]');
        if (countryCodeInput) {
            countryCodeInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^\d+]/g, '');
                if (!value.startsWith('+')) {
                    value = '+' + value;
                }
                e.target.value = value;
            });
        }

        // Formatação do telefone
        const phoneInput = document.querySelector('input[name="telefone"]');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 11) {
                    if (value.length <= 10) {
                        value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                    } else {
                        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                    }
                }
                e.target.value = value;
            });
        }

        // Loading no submit
        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function() {
                const submitBtn = document.getElementById('submit-btn');
                const btnText = document.getElementById('btn-text');
                const loadingSpinner = document.getElementById('loading-spinner');
                
                if (submitBtn) submitBtn.disabled = true;
                if (btnText) btnText.textContent = 'Gerando PIX...';
                if (loadingSpinner) loadingSpinner.classList.remove('hidden');
            });
        }

        // Função para copiar código PIX
        function copyPixCode() {
            const pixCode = '<?php echo isset($pixData['brCode']) ? $pixData['brCode'] : ''; ?>';
            if (!pixCode) return;
            
            navigator.clipboard.writeText(pixCode).then(function() {
                const btn = document.querySelector('.copy-btn');
                const text = document.getElementById('copy-text');
                if (btn) btn.classList.add('copied');
                if (text) text.textContent = 'Copiado!';
                setTimeout(function() {
                    if (btn) btn.classList.remove('copied');
                    if (text) text.textContent = 'Copiar código PIX';
                }, 2000);
            }).catch(function(err) {
                console.error('Erro ao copiar:', err);
            });
        }

        // Countdown timer
        let countdown = 0;
        let isExpired = false;
        let countdownInterval = null;
        
        // Adicionar animação CSS para pulse
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0% { opacity: 1; }
                50% { opacity: 0.5; }
                100% { opacity: 1; }
            }
        `;
        document.head.appendChild(style);
        
        function updateCountdown() {
            if (countdown <= 0 || isExpired) {
                const countdownElement = document.getElementById('countdown');
                if (countdownElement) {
                    countdownElement.classList.add('expired');
                    countdownElement.innerHTML = `
                        <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>PIX expirado!</span>
                        </div>
                    `;
                }
                
                const statusElement = document.getElementById('payment-status');
                if (statusElement) {
                    statusElement.innerHTML = 
                        '❌ PIX expirado. <a href="." style="color: var(--primary-green); font-weight: 600;">Clique aqui para gerar um novo código</a>';
                    statusElement.className = 'status-message status-error';
                }
                
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }
                return;
            }
            
            const minutes = Math.floor(countdown / 60);
            const seconds = countdown % 60;
            const timeString = minutes + ':' + seconds.toString().padStart(2, '0');
            
            const timerElement = document.getElementById('countdown-timer');
            if (timerElement) {
                timerElement.textContent = timeString;
                
                // Mudar cor conforme o tempo restante
                if (countdown <= 300) { // Últimos 5 minutos
                    timerElement.style.color = 'var(--error-red)';
                    timerElement.style.animation = 'pulse 1s infinite';
                } else if (countdown <= 600) { // Últimos 10 minutos
                    timerElement.style.color = 'var(--warning-orange)';
                } else {
                    timerElement.style.color = 'var(--warning-orange)';
                }
            }
            
            countdown--;
        }
        
        function startCountdown(expiresAt) {
            if (!expiresAt) return;
            
            countdown = Math.max(0, Math.floor((new Date(expiresAt) - new Date()) / 1000));
            isExpired = false;
            
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            
            countdownInterval = setInterval(updateCountdown, 1000);
            updateCountdown();
            
            // Verificar se já expirou
            if (countdown <= 0) {
                isExpired = true;
                updateCountdown();
            }
        }
        
        // Inicializar countdown se há dados de PIX
        <?php if ($success && isset($pixData['expiresAt'])): ?>
        startCountdown('<?php echo $pixData['expiresAt']; ?>');
        <?php endif; ?>

        // Verificação de status do pagamento
        <?php if ($success && isset($pixData['id'])): ?>
        let paymentCheckInterval = null;
        
        function checkPaymentStatus() {
            fetch(window.location.pathname, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=check_payment'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'PAID') {
                    document.getElementById('payment-status').innerHTML = 
                        '✅ Pagamento confirmado! Redirecionando...';
                    document.getElementById('payment-status').className = 'status-message status-success';
                    if (paymentCheckInterval) {
                        clearInterval(paymentCheckInterval);
                    }
                    if (countdownInterval) {
                        clearInterval(countdownInterval);
                    }
                    setTimeout(() => {
                        window.location.href = '../receipt.php?id=<?php echo $pixData['id']; ?>';
                    }, 2000);
                } else if (data.status === 'EXPIRED') {
                    document.getElementById('payment-status').innerHTML = 
                        '❌ PIX expirado. <a href="." style="color: var(--primary-green); font-weight: 600;">Clique aqui para gerar um novo código</a>';
                    document.getElementById('payment-status').className = 'status-message status-error';
                    if (paymentCheckInterval) {
                        clearInterval(paymentCheckInterval);
                    }
                    if (countdownInterval) {
                        clearInterval(countdownInterval);
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao verificar status:', error);
            });
        }
        
        // Verificar status a cada <?php echo PAYMENT_CHECK_INTERVAL; ?> segundos
        paymentCheckInterval = setInterval(checkPaymentStatus, <?php echo PAYMENT_CHECK_INTERVAL * 1000; ?>);
        <?php endif; ?>
    </script>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p class="footer-text"><?php echo FOOTER_COPYRIGHT; ?></p>
            <p class="footer-cnpj">CNPJ: <?php echo COMPANY_CNPJ; ?> - <?php echo COMPANY_FULL_NAME; ?></p>
            <p class="footer-dev"><?php echo FOOTER_DEV_MESSAGE; ?></p>
            <p class="footer-disclaimer"><?php echo FOOTER_DISCLAIMER; ?></p>
        </div>
    </footer>
</body>
</html>
