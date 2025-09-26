#!/usr/bin/env python3
"""
PrescrevaMe Premium - Script de Configuração
Configura o ambiente Python com SDK AbacatePay
"""

import subprocess
import sys
import os
from pathlib import Path

def run_command(command, description):
    """Executa comando e retorna resultado"""
    print(f"🔧 {description}...")
    try:
        result = subprocess.run(command, shell=True, capture_output=True, text=True)
        if result.returncode == 0:
            print(f"✅ {description} - Concluído")
            return True
        else:
            print(f"❌ {description} - Erro: {result.stderr}")
            return False
    except Exception as e:
        print(f"❌ {description} - Exceção: {e}")
        return False

def check_python_version():
    """Verifica versão do Python"""
    print("🐍 Verificando versão do Python...")
    version = sys.version_info
    print(f"   Python {version.major}.{version.minor}.{version.micro}")
    
    if version.major < 3 or (version.major == 3 and version.minor < 8):
        print("⚠️ Recomendado Python 3.8+ para melhor compatibilidade")
        return False
    
    print("✅ Versão do Python compatível")
    return True

def install_requirements():
    """Instala dependências do requirements.txt"""
    if not os.path.exists("requirements.txt"):
        print("❌ Arquivo requirements.txt não encontrado")
        return False
    
    return run_command("pip install -r requirements.txt", "Instalando dependências Python")

def install_abacatepay_sdk():
    """Instala SDK AbacatePay"""
    return run_command("pip install abacatepay", "Instalando SDK AbacatePay")

def test_abacatepay_import():
    """Testa importação do SDK"""
    print("🧪 Testando importação do SDK AbacatePay...")
    try:
        import abacatepay
        print("✅ SDK AbacatePay importado com sucesso")
        print(f"   Versão: {getattr(abacatepay, '__version__', 'Desconhecida')}")
        return True
    except ImportError as e:
        print(f"❌ Erro ao importar SDK: {e}")
        return False

def test_flask_import():
    """Testa importação do Flask"""
    print("🧪 Testando importação do Flask...")
    try:
        import flask
        print("✅ Flask importado com sucesso")
        print(f"   Versão: {flask.__version__}")
        return True
    except ImportError as e:
        print(f"❌ Erro ao importar Flask: {e}")
        return False

def create_env_file():
    """Cria arquivo .env de exemplo"""
    print("📝 Criando arquivo .env de exemplo...")
    
    env_content = """# PrescrevaMe Premium - Configurações de Ambiente
# Copie este arquivo para .env e configure suas credenciais

# API AbacatePay
ABACATE_API_KEY=your_api_key_here
ABACATE_WEBHOOK_SECRET=seu_webhook_secret_aqui

# Configurações do Produto
PRODUCT_PRICE=34700
PRODUCT_NAME=PrescrevaMe Premium

# URLs
SUCCESS_REDIRECT_URL=https://prescreva.me/obrigado
WEBHOOK_URL=https://seudominio.com/webhook/abacatepay

# Configurações de Log
LOG_LEVEL=INFO
LOG_FILE=webhook.log

# Configurações de Desenvolvimento
DEBUG_MODE=true
"""
    
    try:
        with open(".env.example", "w", encoding="utf-8") as f:
            f.write(env_content)
        print("✅ Arquivo .env.example criado")
        return True
    except Exception as e:
        print(f"❌ Erro ao criar .env.example: {e}")
        return False

def create_directories():
    """Cria diretórios necessários"""
    print("📁 Criando diretórios...")
    
    directories = [
        "logs",
        "reports", 
        "temp"
    ]
    
    for directory in directories:
        try:
            Path(directory).mkdir(exist_ok=True)
            print(f"   ✅ Diretório {directory}/ criado")
        except Exception as e:
            print(f"   ❌ Erro ao criar {directory}/: {e}")
            return False
    
    return True

def run_tests():
    """Executa testes básicos"""
    print("🧪 Executando testes básicos...")
    
    # Teste 1: Importação do SDK
    if not test_abacatepay_import():
        return False
    
    # Teste 2: Importação do Flask
    if not test_flask_import():
        return False
    
    # Teste 3: Criação de cliente AbacatePay
    try:
        import abacatepay
        client = abacatepay.AbacatePay("test_key")
        print("✅ Cliente AbacatePay criado com sucesso")
    except Exception as e:
        print(f"❌ Erro ao criar cliente: {e}")
        return False
    
    print("✅ Todos os testes passaram!")
    return True

def show_next_steps():
    """Mostra próximos passos"""
    print("\n" + "="*60)
    print("🌵 PRESCREVAME PREMIUM - CONFIGURAÇÃO CONCLUÍDA!")
    print("="*60)
    
    print("\n📋 PRÓXIMOS PASSOS:")
    print("1. Configure sua API Key no arquivo config.php")
    print("2. Configure o webhook no painel AbacatePay:")
    print("   URL: https://seudominio.com/webhook/abacatepay")
    print("3. Teste o sistema:")
    print("   - PHP: http://localhost:8000/checkout.php")
    print("   - Python: python3 pix_manager.py")
    print("4. Inicie o webhook server:")
    print("   python3 webhook_handler.py")
    
    print("\n📚 COMANDOS ÚTEIS:")
    print("• Testar PIX: python3 pix_manager.py")
    print("• Gerar relatório: python3 transaction_report.py")
    print("• Iniciar webhook: python3 webhook_handler.py")
    print("• Testar integração PHP: http://localhost:8000/python_integration.php?test_integration=1")
    
    print("\n🔧 ARQUIVOS CRIADOS:")
    print("• pix_manager.py - Gerenciador de PIX")
    print("• webhook_handler.py - Processador de webhooks")
    print("• transaction_report.py - Gerador de relatórios")
    print("• python_integration.php - Integração PHP-Python")
    print("• requirements.txt - Dependências Python")
    print("• .env.example - Configurações de exemplo")
    
    print("\n📖 DOCUMENTAÇÃO:")
    print("• README.md - Documentação completa")
    print("• https://docs.abacatepay.com/ - SDK AbacatePay")
    
    print("\n🎉 Sistema pronto para uso!")

def main():
    """Função principal"""
    print("🌵 PrescrevaMe Premium - Configuração do Ambiente Python")
    print("="*60)
    
    steps = [
        ("Verificar Python", check_python_version),
        ("Criar diretórios", create_directories),
        ("Instalar SDK AbacatePay", install_abacatepay_sdk),
        ("Instalar dependências", install_requirements),
        ("Criar arquivo .env", create_env_file),
        ("Executar testes", run_tests)
    ]
    
    success_count = 0
    
    for step_name, step_func in steps:
        print(f"\n📋 {step_name}...")
        if step_func():
            success_count += 1
        else:
            print(f"❌ Falha em: {step_name}")
            break
    
    if success_count == len(steps):
        show_next_steps()
    else:
        print(f"\n❌ Configuração incompleta ({success_count}/{len(steps)} passos concluídos)")
        print("Verifique os erros acima e execute novamente.")

if __name__ == "__main__":
    main()
