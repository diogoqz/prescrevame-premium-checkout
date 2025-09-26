<?php
require_once '../settings.php';

session_start();

// Garantir que nenhum referrer seja enviado para domínios externos
header('Referrer-Policy: no-referrer');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao Prescreva.me Premium</title>
    <meta name="description" content="Página de boas-vindas do Prescreva.me Premium">
    <meta name="referrer" content="no-referrer">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .thankyou-wrapper { max-width: 680px; margin: 0 auto; padding: 20px; }
        .thankyou-card { background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
        .thankyou-title { font-size: 22px; font-weight: 800; color: var(--text-dark); margin-bottom: 10px; text-align: center; }
        .thankyou-subtitle { font-size: 16px; color: var(--text-gray); text-align: center; margin-bottom: 18px; }
        .thankyou-content { font-size: 15px; color: var(--text-dark); line-height: 1.7; white-space: pre-line; }
        .actions { display: grid; grid-template-columns: 1fr; gap: 10px; margin-top: 22px; }
        .actions .btn { width: 100%; padding: 14px; border-radius: 12px; font-weight: 700; }
        .btn-primary { background: var(--primary-green); color: var(--text-dark); border: 2px solid var(--dark-green); box-shadow: 0 4px 0 0 var(--dark-green); }
        .btn-primary:hover { box-shadow: 0 2px 0 0 var(--dark-green); transform: translateY(2px); }
        .btn-secondary { background: #fff; color: var(--text-dark); border: 2px solid var(--border-gray); }
        .btn-secondary:hover { border-color: var(--primary-green); }
        @media (min-width: 520px) { .actions { grid-template-columns: 1fr 1fr; } }
    </style>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <meta http-equiv="Cache-Control" content="no-store">
    <meta http-equiv="Pragma" content="no-cache">
</head>
<body>
    <div class="container thankyou-wrapper">
        <!-- Header -->
        <div class="header">
            <img src="<?php echo COMPANY_LOGO_URL; ?>" alt="<?php echo COMPANY_NAME; ?>" class="logo" style="border-radius:50%">
            <h1>Bem-vindo ao Prescreva.me Premium!</h1>
            <h4 style="margin-top:4px;color:var(--text-gray)">Seu acesso foi liberado com sucesso</h4>
        </div>

        <div class="thankyou-card">
            <div class="thankyou-title">Parabéns! 🎉</div>
            <div class="thankyou-subtitle">Agora você tem acesso total ao Prescreva.me Premium</div>
            <div class="thankyou-content">
Bem-vindo ao Prescreva.me Premium!
Parabéns por dar um passo importante na sua jornada médica. A partir de agora, você tem acesso total ao Prescreva.me Premium — uma plataforma feita sob medida para facilitar sua prática clínica e te acompanhar no dia a dia com segurança, agilidade e confiança.

Você está em boas mãos. Seja para relembrar uma conduta urgente, revisar um protocolo ou tirar uma dúvida rápida, estamos aqui para te apoiar — sempre com ética, ciência e clareza.

E o melhor: tudo isso direto no WhatsApp. Sem precisar baixar nada. Sem complicações.

Qualquer dúvida, estamos a um clique de distância. Conte com a gente!
            </div>

            <div class="actions">
                <a class="btn btn-primary" href="https://api.whatsapp.com/send?phone=18567398993&text=/" rel="noreferrer" target="_blank">ACESSAR CONTEÚDO</a>
                <a class="btn btn-secondary" href="https://api.whatsapp.com/send?phone=5563992437559&text=Ol%C3%A1!%20Preciso%20de%SUPORTE%20com%20meu%20acesso%20Premium" rel="noreferrer" target="_blank">FALAR COM SUPORTE</a>
            </div>
        </div>
    </div>

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

