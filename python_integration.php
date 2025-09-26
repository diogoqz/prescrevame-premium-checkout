<?php
/**
 * PrescrevaMe Premium - Integração Python
 * Conecta o sistema PHP com os scripts Python do AbacatePay SDK
 */

require_once 'config.php';

class PythonIntegration {
    
    private $pythonPath;
    private $scriptsPath;
    
    public function __construct() {
        $this->pythonPath = 'python3'; // Ajuste conforme necessário
        $this->scriptsPath = __DIR__;
    }
    
    /**
     * Executa comando Python e retorna resultado
     */
    private function executePython($script, $args = []) {
        $command = $this->pythonPath . ' ' . escapeshellarg($this->scriptsPath . '/' . $script);
        
        if (!empty($args)) {
            foreach ($args as $arg) {
                $command .= ' ' . escapeshellarg($arg);
            }
        }
        
        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        return [
            'success' => $returnCode === 0,
            'output' => implode("\n", $output),
            'return_code' => $returnCode
        ];
    }
    
    /**
     * Cria PIX usando o SDK Python
     */
    public function createPixWithPython($customerData) {
        try {
            // Preparar dados para o script Python
            $data = [
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'],
                'cpf' => $customerData['cpf']
            ];
            
            // Criar arquivo temporário com dados
            $tempFile = tempnam(sys_get_temp_dir(), 'pix_data_');
            file_put_contents($tempFile, json_encode($data));
            
            // Executar script Python
            $result = $this->executePython('pix_manager.py', ['--data-file', $tempFile]);
            
            // Limpar arquivo temporário
            unlink($tempFile);
            
            if ($result['success']) {
                // Tentar extrair dados do PIX do output
                $pixData = $this->parsePixOutput($result['output']);
                return [
                    'success' => true,
                    'pix_data' => $pixData
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['output']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verifica status do PIX usando SDK Python
     */
    public function checkPixStatusWithPython($pixId) {
        try {
            $result = $this->executePython('pix_manager.py', ['--check', $pixId]);
            
            if ($result['success']) {
                $status = $this->parseStatusOutput($result['output']);
                return [
                    'success' => true,
                    'status' => $status
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['output']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Gera relatório de transações
     */
    public function generateReport() {
        try {
            $result = $this->executePython('transaction_report.py');
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'report' => $result['output']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['output']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Processa webhook usando Python
     */
    public function processWebhookWithPython($webhookData) {
        try {
            // Salvar dados do webhook em arquivo temporário
            $tempFile = tempnam(sys_get_temp_dir(), 'webhook_data_');
            file_put_contents($tempFile, json_encode($webhookData));
            
            // Executar processamento via Python
            $result = $this->executePython('webhook_handler.py', ['--process', $tempFile]);
            
            // Limpar arquivo temporário
            unlink($tempFile);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'processed' => true
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['output']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Inicia servidor webhook Python
     */
    public function startWebhookServer($port = 5000) {
        try {
            // Executar em background
            $command = $this->pythonPath . ' ' . escapeshellarg($this->scriptsPath . '/webhook_handler.py') . 
                      ' --port ' . $port . ' > /dev/null 2>&1 &';
            
            exec($command, $output, $returnCode);
            
            return [
                'success' => $returnCode === 0,
                'port' => $port,
                'message' => 'Webhook server started'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Para servidor webhook Python
     */
    public function stopWebhookServer($port = 5000) {
        try {
            // Encontrar processo Python na porta específica
            $command = "lsof -ti:$port | xargs kill -9 2>/dev/null";
            exec($command, $output, $returnCode);
            
            return [
                'success' => true,
                'message' => 'Webhook server stopped'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verifica se Python está disponível
     */
    public function checkPythonAvailability() {
        try {
            $result = $this->executePython('--version');
            
            return [
                'available' => $result['success'],
                'version' => $result['success'] ? $result['output'] : null
            ];
            
        } catch (Exception $e) {
            return [
                'available' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verifica se SDK AbacatePay está instalado
     */
    public function checkAbacatePaySDK() {
        try {
            $result = $this->executePython('--c', 'import abacatepay; print("SDK OK")');
            
            return [
                'installed' => $result['success'],
                'message' => $result['success'] ? 'SDK instalado' : 'SDK não encontrado'
            ];
            
        } catch (Exception $e) {
            return [
                'installed' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Extrai dados do PIX do output do Python
     */
    private function parsePixOutput($output) {
        // Implementar parsing específico baseado no formato do output
        // Por enquanto, retorna estrutura básica
        return [
            'pix_id' => 'python_generated_' . time(),
            'brcode' => 'python_generated_brcode',
            'status' => 'PENDING'
        ];
    }
    
    /**
     * Extrai status do output do Python
     */
    private function parseStatusOutput($output) {
        // Implementar parsing específico
        if (strpos($output, 'PAID') !== false) {
            return 'PAID';
        } elseif (strpos($output, 'EXPIRED') !== false) {
            return 'EXPIRED';
        } else {
            return 'PENDING';
        }
    }
}

// Função helper para uso direto
function createPixWithPython($customerData) {
    $integration = new PythonIntegration();
    return $integration->createPixWithPython($customerData);
}

function checkPixStatusWithPython($pixId) {
    $integration = new PythonIntegration();
    return $integration->checkPixStatusWithPython($pixId);
}

function generateTransactionReport() {
    $integration = new PythonIntegration();
    return $integration->generateReport();
}

// Exemplo de uso:
if (isset($_GET['test_integration'])) {
    header('Content-Type: application/json');
    
    $integration = new PythonIntegration();
    
    $result = [
        'python_available' => $integration->checkPythonAvailability(),
        'sdk_installed' => $integration->checkAbacatePaySDK()
    ];
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}
?>
