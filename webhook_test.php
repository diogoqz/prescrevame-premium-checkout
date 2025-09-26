<?php
// Teste de webhook - simula o disparo quando PIX é criado

// Dados de exemplo
$webhookData = [
    'pix_id' => 'pix_char_exemplo123',
    'amount' => 34700,
    'customer' => [
        'name' => 'João Silva',
        'email' => 'joao@exemplo.com',
        'phone' => '+5511999999999',
        'cpf' => '123.456.789-00'
    ],
    'pix_data' => [
        'id' => 'pix_char_exemplo123',
        'brCode' => '00020126580014br.gov.bcb.pix...',
        'expiresAt' => '2025-09-26T05:00:00.000Z',
        'status' => 'PENDING'
    ]
];

// Função para enviar webhook (copiada do checkout.php)
function sendWebhook($event, $data) {
    $webhookUrl = 'https://webhook.site/your-webhook-url'; // Substitua pela sua URL
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
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'response' => $response
    ];
}

// Testar webhook
echo "<h1>Teste de Webhook - PIX Criado</h1>";
echo "<h2>Dados enviados:</h2>";
echo "<pre>" . json_encode($webhookData, JSON_PRETTY_PRINT) . "</pre>";

$result = sendWebhook('pix.created', $webhookData);

echo "<h2>Resultado:</h2>";
echo "<p><strong>Sucesso:</strong> " . ($result['success'] ? 'Sim' : 'Não') . "</p>";
echo "<p><strong>HTTP Code:</strong> " . $result['http_code'] . "</p>";
echo "<p><strong>Resposta:</strong> " . $result['response'] . "</p>";

if ($result['success']) {
    echo "<p style='color: green;'>✅ Webhook enviado com sucesso!</p>";
} else {
    echo "<p style='color: red;'>❌ Erro ao enviar webhook</p>";
}
?>
