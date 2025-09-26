<?php
/**
 * Configurações do sistema PrescrevaMe Premium
 */

// Função para carregar variáveis de ambiente do arquivo .env
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

// Carregar arquivo .env
loadEnv(__DIR__ . '/.env');

// Função auxiliar para obter variáveis de ambiente com valor padrão
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    
    // Converter strings booleanas
    if (in_array(strtolower($value), ['true', 'false'])) {
        return strtolower($value) === 'true';
    }
    
    // Converter números
    if (is_numeric($value)) {
        return strpos($value, '.') !== false ? (float) $value : (int) $value;
    }
    
    return $value;
}

// Configurações da API AbacatePay
define('ABACATE_API_KEY', env('ABACATE_API_KEY', ''));
define('ABACATE_API_BASE_URL', env('ABACATE_API_BASE_URL', 'https://api.abacatepay.com/v1'));

// Configurações do produto
define('PRODUCT_NAME', env('PRODUCT_NAME', 'PrescrevaMe Premium'));
define('PRODUCT_DESCRIPTION', env('PRODUCT_DESCRIPTION', 'Assinatura Anual Premium'));
define('PRODUCT_PRICE', env('PRODUCT_PRICE', 34700)); // Em centavos (R$ 347,00)
define('PRODUCT_PRICE_FORMATTED', env('PRODUCT_PRICE_FORMATTED', 'R$ 347,00'));

// Configurações de tempo
define('PIX_EXPIRATION_MINUTES', env('PIX_EXPIRATION_MINUTES', 15)); // Tempo de expiração do PIX em minutos
define('PAYMENT_CHECK_INTERVAL', env('PAYMENT_CHECK_INTERVAL', 2)); // Intervalo para verificar pagamento em segundos

// URLs
define('SUCCESS_REDIRECT_URL', env('SUCCESS_REDIRECT_URL', '/obrigado'));
define('LOGO_URL', env('LOGO_URL', 'https://i.ibb.co/7JbHrmdT/pm3.jpg'));

// Configurações de email (se necessário no futuro)
define('SMTP_HOST', env('SMTP_HOST', ''));
define('SMTP_PORT', env('SMTP_PORT', 587));
define('SMTP_USERNAME', env('SMTP_USERNAME', ''));
define('SMTP_PASSWORD', env('SMTP_PASSWORD', ''));
define('FROM_EMAIL', env('FROM_EMAIL', 'noreply@prescreva.me'));
define('FROM_NAME', env('FROM_NAME', 'PrescrevaMe Premium'));

// Configurações de banco de dados (se necessário no futuro)
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', ''));
define('DB_USER', env('DB_USER', ''));
define('DB_PASS', env('DB_PASS', ''));

// Configurações de debug
define('DEBUG_MODE', env('DEBUG_MODE', false)); // Altere para false em produção
define('LOG_ERRORS', env('LOG_ERRORS', true));

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

// Configurações de sessão (apenas se a sessão não foi iniciada)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
}

// Headers de segurança
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
?>
