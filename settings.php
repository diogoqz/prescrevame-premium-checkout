<?php
/**
 * Configurações do Sistema PrescrevaMe
 * Centralize todas as configurações aqui
 */

// ========================================
// CONFIGURAÇÕES DA API ABACATEPAY
// ========================================
define('ABACATE_API_KEY', 'abc_dev_xp4Fa35xjKCq1tndyRzEEj3w');
define('ABACATE_API_BASE_URL', 'https://api.abacatepay.com/v1');

// ========================================
// CONFIGURAÇÕES DO PRODUTO
// ========================================
define('PRODUCT_NAME', 'PrescrevaMe Premium - Assinatura Anual');
define('PRODUCT_DESCRIPTION_TITLE', 'Acesso completo ao PrescrevaMe via WhatsApp');
define('PRODUCT_DESCRIPTION_SUBTITLE', 'IA médica avançada para diagnóstico e prescrição');
define('PRODUCT_PRICE', 34700); // R$ 347,00 em centavos
define('PRODUCT_PRICE_DISPLAY', 'R$ 347,00');
define('PRODUCT_ORIGINAL_PRICE', 42700); // Preço de tabela para exibir economia
define('PRODUCT_ORIGINAL_PRICE_DISPLAY', 'R$ 427,00');
define('PRODUCT_IMAGE_URL', 'https://i.ibb.co/GVFByJw/09d6707b-23b3-4e92-9ca2-d18d30f28f00-min.jpg');
define('PRODUCT_SUMMARY_NOTE', 'Plano anual');

// ========================================
// CONFIGURAÇÕES DO HEADER - PRODUTO PRINCIPAL
// ========================================
define('HEADER_TITLE', 'PrescrevaMe Premium');
define('HEADER_DESCRIPTION', 'Assinatura Anual');
define('HEADER_SUBDESCRIPTION', 'Acesso completo via WhatsApp com IA médica avançada');

// ========================================
// CONFIGURAÇÕES DO DESCONTO PIX
// ========================================
define('DESCONTO_PIX_NAME', 'PrescrevaMe Premium Assinatura Anual');
define('DESCONTO_PIX_DESCRIPTION_TITLE', 'Acesso completo ao PrescrevaMe via WhatsApp');
define('DESCONTO_PIX_DESCRIPTION_SUBTITLE', 'IA médica avançada - Desconto especial para pagamento via PIX');
define('DESCONTO_PIX_PRICE', 34700); // R$ 347,00 em centavos
define('DESCONTO_PIX_PRICE_DISPLAY', 'R$ 347,00');
define('DESCONTO_PIX_ORIGINAL_PRICE', 42700);
define('DESCONTO_PIX_ORIGINAL_PRICE_DISPLAY', 'R$ 427,00');
define('DESCONTO_PIX_EXPIRES_IN', 2700); // 45 minutos em segundos
define('DESCONTO_PIX_EXPIRES_DISPLAY', '45 minutos');
define('DESCONTO_PIX_IMAGE_URL', PRODUCT_IMAGE_URL);
define('DESCONTO_PIX_SUMMARY_NOTE', 'Plano anual');

// ========================================
// CONFIGURAÇÕES DO HEADER - DESCONTO PIX
// ========================================
define('DESCONTO_PIX_HEADER_TITLE', 'PrescrevaMe Premium - Assinatura Anual');
define('DESCONTO_PIX_HEADER_DESCRIPTION', 'Desconto PIX');
define('DESCONTO_PIX_HEADER_SUBDESCRIPTION', 'Oferta válida por tempo limitado');

// ========================================
// CONFIGURAÇÕES DO DESCONTO RENOVAÇÃO
// ========================================
define('DESCONTO_RENOVACAO_NAME', 'PrescrevaMe Premium - Assinatura Anual');
define('DESCONTO_RENOVACAO_DESCRIPTION_TITLE', 'Acesso completo ao PrescrevaMe via WhatsApp');
define('DESCONTO_RENOVACAO_DESCRIPTION_SUBTITLE', 'IA médica avançada - Desconto especial para renovação');
define('DESCONTO_RENOVACAO_PRICE', 22700); // R$ 227,00 em centavos
define('DESCONTO_RENOVACAO_PRICE_DISPLAY', 'R$ 227,00');
define('DESCONTO_RENOVACAO_ORIGINAL_PRICE', 42700); // R$ 427,00 em centavos
define('DESCONTO_RENOVACAO_ORIGINAL_PRICE_DISPLAY', 'R$ 427,00');
define('DESCONTO_RENOVACAO_EXPIRES_IN', 900); // 15 minutos em segundos
define('DESCONTO_RENOVACAO_EXPIRES_DISPLAY', '15 minutos');
define('DESCONTO_RENOVACAO_IMAGE_URL', PRODUCT_IMAGE_URL);
define('DESCONTO_RENOVACAO_SUMMARY_NOTE', 'Plano anual');

// ========================================
// CONFIGURAÇÕES DO HEADER - DESCONTO RENOVAÇÃO
// ========================================
define('DESCONTO_RENOVACAO_HEADER_TITLE', 'PrescrevaMe Premium');
define('DESCONTO_RENOVACAO_HEADER_DESCRIPTION', 'Desconto Renovação');
define('DESCONTO_RENOVACAO_HEADER_SUBDESCRIPTION', 'Desconto especial para renovação da sua assinatura');

// ========================================
// CONFIGURAÇÕES DO PIX
// ========================================
define('PIX_EXPIRES_IN', 900); // 15 minutos em segundos
define('PIX_EXPIRES_DISPLAY', '15 minutos');

// ========================================
// CONFIGURAÇÕES DO WEBHOOK
// ========================================
define('WEBHOOK_URL', 'https://app-n8n.3gbyjx.easypanel.host/webhook/abacate-pme'); // Substitua pela sua URL
define('WEBHOOK_ENABLED', true); // true/false para habilitar/desabilitar

// ========================================
// CONFIGURAÇÕES DA EMPRESA
// ========================================
define('COMPANY_NAME', 'Prescreva.me');
define('COMPANY_CNPJ', '36.342.929/0001-46');
define('COMPANY_FULL_NAME', 'Prescreva.Me Inovação e Tecnologia em Saúde');
define('COMPANY_LOGO_URL', 'https://i.ibb.co/7JbHrmdT/pm3.jpg');

// ========================================
// CONFIGURAÇÕES DO SITE
// ========================================
define('SITE_TITLE', 'PrescrevaMe Premium');
define('SITE_DESCRIPTION', 'Finalize seu pagamento de forma segura');
define('SITE_URL', 'http://localhost:8000');

// ========================================
// CONFIGURAÇÕES DE PAGAMENTO
// ========================================
define('PAYMENT_CHECK_INTERVAL', 5); // Verificar status a cada 5 segundos
define('PAYMENT_REDIRECT_DELAY', 2000); // Delay para redirecionamento (ms)

// ========================================
// CONFIGURAÇÕES DE SEGURANÇA
// ========================================
define('SECURITY_BADGES_ENABLED', true);
define('SECURITY_BADGES_IMAGE', 'site-seguro.png');

// ========================================
// CONFIGURAÇÕES DE FOOTER
// ========================================
define('FOOTER_COPYRIGHT', '© 2025 - Prescreva.me - Todos os direitos reservados.');
define('FOOTER_DEV_MESSAGE', 'Desenvolvido com 💚 de médico para médico');
define('FOOTER_DISCLAIMER', 'O Prescreva.me é uma ferramenta de apoio e não substitui a avaliação médica profissional.');

// ========================================
// CONFIGURAÇÕES DE TERMOS
// ========================================
define('TERMS_TEXT', 'Ao clicar em \'Comprar agora\', eu declaro que li e concordo (i) que a MedPlataforma está processando este pedido em nome de Prescreva.me - Inovação e Tecnologia em Saúde e não possui responsabilidade pelo conteúdo e/ou faz controle prévio deste; (ii) com os Termos de Uso, Política de Privacidade e demais Políticas de pagamento e (iii) que sou maior de idade ou autorizado e acompanhado por um responsável legal.');

// ========================================
// CONFIGURAÇÕES DE EMAIL (OPCIONAL)
// ========================================
define('EMAIL_FROM', 'noreply@prescreva.me');
define('EMAIL_FROM_NAME', 'PrescrevaMe');
define('EMAIL_SMTP_HOST', '');
define('EMAIL_SMTP_PORT', 587);
define('EMAIL_SMTP_USER', '');
define('EMAIL_SMTP_PASS', '');

// ========================================
// CONFIGURAÇÕES DE DEBUG
// ========================================
define('DEBUG_MODE', false); // true para ativar logs detalhados
define('LOG_WEBHOOKS', true); // true para logar webhooks

// ========================================
// FUNÇÕES AUXILIARES
// ========================================

/**
 * Obter configuração do produto
 */
function getProductConfig() {
    return [
        'name' => PRODUCT_NAME,
        'description_title' => PRODUCT_DESCRIPTION_TITLE,
        'description_subtitle' => PRODUCT_DESCRIPTION_SUBTITLE,
        'price' => PRODUCT_PRICE,
        'price_display' => PRODUCT_PRICE_DISPLAY,
        'original_price' => PRODUCT_ORIGINAL_PRICE,
        'original_price_display' => PRODUCT_ORIGINAL_PRICE_DISPLAY,
        'image_url' => PRODUCT_IMAGE_URL,
        'summary_note' => PRODUCT_SUMMARY_NOTE
    ];
}

/**
 * Obter configuração do PIX
 */
function getPixConfig() {
    return [
        'expires_in' => PIX_EXPIRES_IN,
        'expires_display' => PIX_EXPIRES_DISPLAY
    ];
}

/**
 * Obter configuração da empresa
 */
function getCompanyConfig() {
    return [
        'name' => COMPANY_NAME,
        'cnpj' => COMPANY_CNPJ,
        'full_name' => COMPANY_FULL_NAME,
        'logo_url' => COMPANY_LOGO_URL
    ];
}

/**
 * Verificar se webhook está habilitado
 */
function isWebhookEnabled() {
    return WEBHOOK_ENABLED && !empty(WEBHOOK_URL);
}

/**
 * Obter URL do webhook
 */
function getWebhookUrl() {
    return isWebhookEnabled() ? WEBHOOK_URL : null;
}

/**
 * Obter configuração do desconto PIX
 */
function getDescontoPixConfig() {
    return [
        'name' => DESCONTO_PIX_NAME,
        'description_title' => DESCONTO_PIX_DESCRIPTION_TITLE,
        'description_subtitle' => DESCONTO_PIX_DESCRIPTION_SUBTITLE,
        'price' => DESCONTO_PIX_PRICE,
        'price_display' => DESCONTO_PIX_PRICE_DISPLAY,
        'original_price' => DESCONTO_PIX_ORIGINAL_PRICE,
        'original_price_display' => DESCONTO_PIX_ORIGINAL_PRICE_DISPLAY,
        'expires_in' => DESCONTO_PIX_EXPIRES_IN,
        'expires_display' => DESCONTO_PIX_EXPIRES_DISPLAY,
        'header_title' => DESCONTO_PIX_HEADER_TITLE,
        'header_description' => DESCONTO_PIX_HEADER_DESCRIPTION,
        'header_subdescription' => DESCONTO_PIX_HEADER_SUBDESCRIPTION,
        'image_url' => DESCONTO_PIX_IMAGE_URL,
        'summary_note' => DESCONTO_PIX_SUMMARY_NOTE
    ];
}

/**
 * Obter configuração do desconto renovação
 */
function getDescontoRenovacaoConfig() {
    return [
        'name' => DESCONTO_RENOVACAO_NAME,
        'description_title' => DESCONTO_RENOVACAO_DESCRIPTION_TITLE,
        'description_subtitle' => DESCONTO_RENOVACAO_DESCRIPTION_SUBTITLE,
        'price' => DESCONTO_RENOVACAO_PRICE,
        'price_display' => DESCONTO_RENOVACAO_PRICE_DISPLAY,
        'original_price' => DESCONTO_RENOVACAO_ORIGINAL_PRICE,
        'original_price_display' => DESCONTO_RENOVACAO_ORIGINAL_PRICE_DISPLAY,
        'expires_in' => DESCONTO_RENOVACAO_EXPIRES_IN,
        'expires_display' => DESCONTO_RENOVACAO_EXPIRES_DISPLAY,
        'header_title' => DESCONTO_RENOVACAO_HEADER_TITLE,
        'header_description' => DESCONTO_RENOVACAO_HEADER_DESCRIPTION,
        'header_subdescription' => DESCONTO_RENOVACAO_HEADER_SUBDESCRIPTION,
        'image_url' => DESCONTO_RENOVACAO_IMAGE_URL,
        'summary_note' => DESCONTO_RENOVACAO_SUMMARY_NOTE
    ];
}
?>
