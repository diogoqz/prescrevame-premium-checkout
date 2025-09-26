<?php
session_start();
// Não enviar referrer para domínios externos
header('Referrer-Policy: no-referrer');

// Verificar se há dados de pagamento na sessão
if (!isset($_SESSION['pix_data']) || !isset($_SESSION['customer_data']) || $_SESSION['pix_data']['status'] !== 'PAID') {
    header('Location: checkout.php');
    exit;
}

$pixData = $_SESSION['pix_data'];
$customerData = $_SESSION['customer_data'];

// Função para gerar PDF do comprovante
function generateReceiptPDF($pixData, $customerData) {
    require_once('tcpdf/tcpdf.php'); // Você precisará baixar a biblioteca TCPDF
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Informações do documento
    $pdf->SetCreator('PrescrevaMe');
    $pdf->SetAuthor('PrescrevaMe Premium');
    $pdf->SetTitle('Comprovante de Pagamento - PrescrevaMe Premium');
    $pdf->SetSubject('Comprovante de Pagamento');
    
    // Margens
    $pdf->SetMargins(20, 30, 20);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);
    
    // Adicionar página
    $pdf->AddPage();
    
    // Cabeçalho com logo e título
    $pdf->SetFillColor(199, 233, 80); // Cor verde primária
    $pdf->Rect(0, 0, 210, 40, 'F');
    
    $pdf->SetTextColor(26, 26, 26);
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetXY(20, 15);
    $pdf->Cell(0, 10, 'PrescrevaMe Premium', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetXY(20, 25);
    $pdf->Cell(0, 10, 'Comprovante de Pagamento', 0, 1, 'L');
    
    // Conteúdo
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 12);
    
    $y = 60;
    $lineHeight = 8;
    
    $receiptData = [
        ['Produto:', 'PrescrevaMe Premium - Assinatura Anual'],
        ['Cliente:', $customerData['nome']],
        ['E-mail:', $customerData['email']],
        ['CPF:', $customerData['cpf']],
        ['Valor:', 'R$ 347,00'],
        ['Data:', date('d/m/Y H:i')],
        ['Status:', '✅ Pago via PIX'],
        ['ID da Transação:', $pixData['id']]
    ];
    
    foreach ($receiptData as $row) {
        $pdf->SetFont('helvetica', 'B');
        $pdf->SetXY(20, $y);
        $pdf->Cell(40, $lineHeight, $row[0], 0, 0, 'L');
        
        $pdf->SetFont('helvetica', '');
        $pdf->SetXY(60, $y);
        $pdf->Cell(0, $lineHeight, $row[1], 0, 1, 'L');
        
        $y += $lineHeight + 2;
    }
    
    // Rodapé
    $y += 20;
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(102, 102, 102);
    $pdf->SetXY(20, $y);
    $pdf->Cell(0, $lineHeight, 'Este é um comprovante de pagamento válido.', 0, 1, 'L');
    $pdf->SetXY(20, $y + $lineHeight);
    $pdf->Cell(0, $lineHeight, 'PrescrevaMe - Assistente Médico via WhatsApp', 0, 1, 'L');
    
    return $pdf;
}

// Processar download do PDF
if (isset($_GET['download']) && $_GET['download'] === 'pdf') {
    try {
        $pdf = generateReceiptPDF($pixData, $customerData);
        $filename = 'comprovante-prescreva-me-' . date('Y-m-d-H-i-s') . '.pdf';
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        $pdf->Output($filename, 'D');
        exit;
    } catch (Exception $e) {
        $error = 'Erro ao gerar PDF: ' . $e->getMessage();
    }
}

// Limpar sessão após exibir o recibo (opcional)
// unset($_SESSION['pix_data']);
// unset($_SESSION['customer_data']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante - PrescrevaMe Premium</title>
    <meta name="description" content="Comprovante de pagamento PrescrevaMe Premium">
    <meta name="referrer" content="no-referrer">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #ACCDAC;
            --dark-green: #2B505B;
            --light-green: #9EEA6C;
            --bg-light: #f8f9fa;
            --text-dark: #1a1a1a;
            --text-gray: #666666;
            --border-gray: #e0e0e0;
            --success-green: #4CAF50;
            --error-red: #f44336;
            --warning-orange: #FF9800;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 480px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            margin: 0 auto 15px;
            display: block;
            object-fit: cover;
            border: 2px solid var(--primary-green);
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .header p {
            color: var(--text-gray);
            font-size: 16px;
        }

        .receipt-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .receipt-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 16px;
        }

        .receipt-details {
            text-align: left;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .receipt-row:last-child {
            margin-bottom: 0;
            font-weight: 600;
            padding-top: 8px;
            border-top: 1px solid var(--border-gray);
        }

        .receipt-label {
            color: var(--text-gray);
        }

        .receipt-value {
            color: var(--text-dark);
            font-weight: 500;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            margin-bottom: 10px;
        }

        .btn-primary {
            background: var(--primary-green);
            color: var(--text-dark);
            border: 2px solid var(--dark-green);
            box-shadow: 0 4px 0 0 var(--dark-green);
        }

        .btn-primary:hover {
            box-shadow: 0 2px 0 0 var(--dark-green);
            transform: translateY(2px);
        }

        .btn-secondary {
            background: white;
            color: var(--text-dark);
            border: 2px solid var(--border-gray);
        }

        .btn-secondary:hover {
            border-color: var(--primary-green);
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .redirect-info {
            margin-top: 20px;
            color: var(--text-gray);
            font-size: 14px;
            text-align: center;
        }

        .countdown {
            font-weight: 600;
            color: var(--warning-orange);
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            .receipt-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="https://i.ibb.co/7JbHrmdT/pm3.jpg" alt="PrescrevaMe" class="logo">
            <h1>PrescrevaMe Premium</h1>
            <p>Pagamento confirmado com sucesso!</p>
        </div>

        <!-- Success Message -->
        <div class="success-message">
            🎉 Parabéns! Seu pagamento foi processado com sucesso.
        </div>

        <!-- Receipt -->
        <div class="receipt-section">
            <div class="receipt-title">📄 Comprovante de Pagamento</div>
            
            <div class="receipt-details">
                <div class="receipt-row">
                    <span class="receipt-label">Produto:</span>
                    <span class="receipt-value">PrescrevaMe Premium</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Cliente:</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($customerData['nome']); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">E-mail:</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($customerData['email']); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">CPF:</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($customerData['cpf']); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Valor:</span>
                    <span class="receipt-value">R$ 347,00</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Data:</span>
                    <span class="receipt-value"><?php echo date('d/m/Y H:i'); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Status:</span>
                    <span class="receipt-value">✅ Pago</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">ID da Transação:</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($pixData['id']); ?></span>
                </div>
            </div>

            <a href="?download=pdf" class="btn btn-primary">
                📄 Baixar Comprovante PDF
            </a>

            <a href="/obrigado/" class="btn btn-secondary">
                🚀 Acessar PrescrevaMe
            </a>

            <div class="redirect-info">
                <p>Você será redirecionado automaticamente em <span class="countdown" id="countdown">10</span> segundos...</p>
                <p><a href="/obrigado/" style="color: var(--dark-green);">Clique aqui se não for redirecionado</a></p>
            </div>
        </div>
    </div>

    <script>
        // Countdown para redirecionamento
        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        
        function updateCountdown() {
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                window.location.href = '/obrigado/';
            } else {
                countdown--;
                setTimeout(updateCountdown, 1000);
            }
        }
        
        updateCountdown();
    </script>
</body>
</html>
