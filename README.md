# PrescrevaMe Premium - Sistema de Checkout Completo

Sistema completo de checkout integrado com a API AbacatePay para processamento de pagamentos PIX, incluindo integração PHP + Python SDK.

## 🚀 Características

### Sistema PHP (Frontend + Backend)
- ✅ Integração completa com API AbacatePay
- ✅ Validação de formulários no backend (PHP)
- ✅ Geração automática de QR Code PIX
- ✅ Verificação de status de pagamento em tempo real
- ✅ Comprovante de pagamento em PDF
- ✅ Design responsivo e moderno
- ✅ Validação de CPF e email
- ✅ Formatação automática de campos
- ✅ Sistema de countdown para expiração do PIX

### Sistema Python (SDK Oficial)
- ✅ SDK oficial AbacatePay integrado
- ✅ Gerenciador de PIX avançado
- ✅ Webhook handler para notificações
- ✅ Sistema de relatórios detalhados
- ✅ Integração PHP-Python
- ✅ Simulação de pagamentos (desenvolvimento)
- ✅ Monitoramento automático de status

## 📋 Pré-requisitos

### Para Sistema PHP
- PHP 7.4 ou superior
- Servidor web (Apache/Nginx)
- Extensão cURL habilitada
- Extensão GD habilitada (para QR Code)

### Para Sistema Python
- Python 3.8 ou superior
- pip (gerenciador de pacotes Python)
- SDK AbacatePay (instalado automaticamente)

## 🛠️ Instalação

### 1. Sistema PHP
1. **Clone ou baixe os arquivos para seu servidor web**
   ```bash
   # Exemplo com Apache
   cp -r abacate-frontend/* /var/www/html/
   ```

2. **Configure as permissões**
   ```bash
   chmod 755 /var/www/html/
   chmod 644 /var/www/html/*.php
   chmod 644 /var/www/html/*.html
   ```

3. **Configure a API Key do AbacatePay**
   - Copie o arquivo `.env.example` para `.env`
   - Edite o arquivo `.env` e configure sua chave de API
   - Substitua `your_api_key_here` pela sua chave de produção

4. **Para geração de PDF (opcional)**
   - Baixe a biblioteca TCPDF: https://tcpdf.org/
   - Extraia para a pasta `tcpdf/` no mesmo diretório dos arquivos PHP

### 2. Sistema Python
1. **Execute o script de configuração automática**
   ```bash
   python3 setup.py
   ```
   
2. **Ou instale manualmente**
   ```bash
   pip install -r requirements.txt
   pip install abacatepay
   ```

3. **Teste a instalação**
   ```bash
   python3 test_pix.py
   ```

## 📁 Estrutura de Arquivos

```
abacate-frontend/
├── 📄 Sistema PHP
│   ├── checkout.php          # Página principal de checkout
│   ├── receipt.php           # Página de comprovante
│   ├── config.php            # Configurações do sistema
│   ├── python_integration.php # Integração PHP-Python
│   ├── payment-system.html   # Versão HTML original (referência)
│   └── receipt.html          # Versão HTML do recibo (referência)
│
├── 🐍 Sistema Python
│   ├── pix_manager.py        # Gerenciador de PIX
│   ├── webhook_handler.py    # Processador de webhooks
│   ├── transaction_report.py # Gerador de relatórios
│   ├── test_pix.py          # Teste rápido de PIX
│   ├── setup.py             # Configuração automática
│   └── requirements.txt     # Dependências Python
│
├── 📦 SDK AbacatePay
│   └── abacatepay-python-sdk/ # SDK oficial (integrado)
│
├── 📂 Diretórios
│   ├── logs/                # Logs do sistema
│   ├── reports/             # Relatórios gerados
│   └── temp/                # Arquivos temporários
│
└── 📚 Documentação
    ├── README.md            # Este arquivo
    └── .env.example         # Configurações de exemplo
```

## 🔧 Configuração

### API AbacatePay

Edite o arquivo `config.php` para configurar:

```php
// Sua chave da API AbacatePay
define('ABACATE_API_KEY', 'sua_chave_aqui');

// URL base da API (não altere se estiver usando a API oficial)
define('ABACATE_API_BASE_URL', 'https://api.abacatepay.com/v1');
```

### Produto

Configure as informações do produto no `config.php`:

```php
define('PRODUCT_NAME', 'PrescrevaMe Premium');
define('PRODUCT_DESCRIPTION', 'Assinatura Anual Premium');
define('PRODUCT_PRICE', 34700); // Em centavos (R$ 347,00)
```

### URLs de Redirecionamento

```php
define('SUCCESS_REDIRECT_URL', 'https://prescreva.me/obrigado');
```

## 🎯 Como Usar

### Sistema PHP (Frontend)
1. **Acesse o checkout**
   - Navegue até `https://seudominio.com/checkout.php`

2. **Preencha o formulário**
   - Nome completo
   - E-mail válido
   - WhatsApp com código do país
   - CPF válido

3. **Processo de pagamento**
   - Clique em "Gerar PIX para Pagamento"
   - Escaneie o QR Code ou copie o código PIX
   - O sistema verifica automaticamente o pagamento

4. **Confirmação**
   - Após o pagamento, o usuário é redirecionado para o recibo
   - Pode baixar o comprovante em PDF

### Sistema Python (Backend Avançado)
1. **Gerenciar PIX**
   ```bash
   python3 pix_manager.py
   ```

2. **Iniciar webhook server**
   ```bash
   python3 webhook_handler.py
   ```

3. **Gerar relatórios**
   ```bash
   python3 transaction_report.py
   ```

4. **Teste rápido**
   ```bash
   python3 test_pix.py
   ```

## 🔒 Segurança

- ✅ Validação de dados no servidor
- ✅ Sanitização de inputs
- ✅ Headers de segurança
- ✅ Sessões seguras
- ✅ Validação de CPF
- ✅ Validação de email

## 🐛 Debug

Para ativar o modo debug, edite o `config.php`:

```php
define('DEBUG_MODE', true);
```

Os logs de erro serão salvos em `error.log`.

## 📱 Responsividade

O sistema é totalmente responsivo e funciona em:
- 📱 Dispositivos móveis
- 💻 Tablets
- 🖥️ Desktops

## 🎨 Personalização

### Cores
As cores podem ser alteradas no CSS dentro dos arquivos PHP:

```css
:root {
    --primary-green: #C7E950;
    --dark-green: #2B505B;
    --light-green: #9EEA6C;
    /* ... */
}
```

### Logo
Altere a URL do logo no `config.php`:

```php
define('LOGO_URL', 'https://seudominio.com/logo.jpg');
```

## 🔄 Fluxo de Pagamento

1. **Formulário** → Validação de dados
2. **API Call** → Criação do PIX via AbacatePay
3. **QR Code** → Exibição do código para pagamento
4. **Verificação** → Polling automático do status
5. **Confirmação** → Redirecionamento para recibo
6. **PDF** → Download do comprovante

## 📞 Suporte

Para suporte técnico ou dúvidas sobre a API AbacatePay:
- 📧 Email: suporte@abacatepay.com
- 🌐 Site: https://abacatepay.com

## 📄 Licença

Este projeto é propriedade do PrescrevaMe e não deve ser distribuído sem autorização.

---

**Desenvolvido para PrescrevaMe Premium** 🌵
