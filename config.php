<?php
/**
 * Configurações do sistema PrescrevaMe Premium
 */

// Configurações da API AbacatePay
define('ABACATE_API_KEY', 'abc_dev_xp4Fa35xjKCq1tndyRzEEj3w');
define('ABACATE_API_BASE_URL', 'https://api.abacatepay.com/v1');

// Configurações do produto
define('PRODUCT_NAME', 'PrescrevaMe Premium');
define('PRODUCT_DESCRIPTION', 'Assinatura Anual Premium');
define('PRODUCT_PRICE', 34700); // Em centavos (R$ 347,00)
define('PRODUCT_PRICE_FORMATTED', 'R$ 347,00');

// Configurações de tempo
define('PIX_EXPIRATION_MINUTES', 15); // Tempo de expiração do PIX em minutos
define('PAYMENT_CHECK_INTERVAL', 2); // Intervalo para verificar pagamento em segundos

// URLs
define('SUCCESS_REDIRECT_URL', '/obrigado');
define('LOGO_URL', 'https://i.ibb.co/7JbHrmdT/pm3.jpg');

// Configurações de email (se necessário no futuro)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@prescreva.me');
define('FROM_NAME', 'PrescrevaMe Premium');

// Configurações de banco de dados (se necessário no futuro)
define('DB_HOST', 'localhost');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');

// Configurações de debug
define('DEBUG_MODE', false); // Altere para false em produção
define('LOG_ERRORS', true);

// Função para log de erros
function logError($message, $context = []) {
    if (LOG_ERRORS) {
        $logMessage = date('Y-m-d H:i:s') . ' - ' . $message;
        if (!empty($context)) {
            $logMessage .= ' - Context: ' . json_encode($context);
        }
        error_log($logMessage . PHP_EOL, 3, 'error.log');
    }
}

// Função para debug
function debug($message, $data = null) {
    if (DEBUG_MODE) {
        echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-left: 4px solid #007cba;">';
        echo '<strong>DEBUG:</strong> ' . $message;
        if ($data !== null) {
            echo '<pre>' . print_r($data, true) . '</pre>';
        }
        echo '</div>';
    }
}

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

// Headers de segurança
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
?>
