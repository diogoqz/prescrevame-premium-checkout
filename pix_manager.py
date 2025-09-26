#!/usr/bin/env python3
"""
PrescrevaMe Premium - Gerenciador de PIX com AbacatePay SDK
Script para gerenciar pagamentos PIX usando o SDK oficial do AbacatePay
"""

import abacatepay
from abacatepay.customers import CustomerMetadata
from abacatepay.pixQrCode import PixQrCodeIn
import json
import time
import os
from datetime import datetime
from typing import Optional, Dict, Any
from dotenv import load_dotenv

# Carregar variáveis de ambiente
load_dotenv()

# Configurações
API_KEY = os.getenv('ABACATE_API_KEY', '')  # Chave de desenvolvimento
PRODUCT_PRICE = int(os.getenv('PRODUCT_PRICE', 34700))  # R$ 347,00 em centavos
PRODUCT_NAME = os.getenv('PRODUCT_NAME', 'PrescrevaMe Premium - Assinatura Anual')
PIX_EXPIRATION = int(os.getenv('PIX_EXPIRATION_MINUTES', 15)) * 60  # Converter minutos para segundos

class PrescrevaMePixManager:
    """Gerenciador de pagamentos PIX para PrescrevaMe Premium"""
    
    def __init__(self, api_key: str = API_KEY):
        """Inicializa o cliente AbacatePay"""
        self.client = abacatepay.AbacatePay(api_key)
        self.log_prefix = "🌵 PrescrevaMe PIX Manager"
    
    def create_pix_payment(
        self,
        customer_name: str,
        customer_email: str,
        customer_phone: str,
        customer_cpf: str,
        amount: int = PRODUCT_PRICE,
        description: str = PRODUCT_NAME,
        expires_in: int = PIX_EXPIRATION
    ) -> Dict[str, Any]:
        """
        Cria um novo pagamento PIX
        
        Args:
            customer_name: Nome completo do cliente
            customer_email: Email do cliente
            customer_phone: Telefone do cliente (formato: +55 11 99999-9999)
            customer_cpf: CPF do cliente (apenas números)
            amount: Valor em centavos (padrão: R$ 347,00)
            description: Descrição do pagamento
            expires_in: Tempo de expiração em segundos
        
        Returns:
            Dict com informações do PIX criado
        """
        try:
            print(f"{self.log_prefix} 💳 Criando pagamento PIX...")
            
            # Criar dados do cliente
            customer = CustomerMetadata(
                name=customer_name,
                email=customer_email,
                cellphone=customer_phone,
                tax_id=customer_cpf
            )
            
            # Criar dados do PIX
            pix_data = PixQrCodeIn(
                amount=amount,
                expires_in=expires_in,
                description=description,
                customer=customer
            )
            
            # Criar PIX via API
            pix_result = self.client.pixQrCode.create(pix_data)
            
            print(f"{self.log_prefix} ✅ PIX criado com sucesso!")
            print(f"   ID: {pix_result.id}")
            print(f"   Valor: R$ {amount/100:.2f}")
            print(f"   Expira em: {pix_result.expires_at}")
            print(f"   Status: {pix_result.status}")
            
            return {
                "success": True,
                "pix_id": pix_result.id,
                "amount": pix_result.amount,
                "status": pix_result.status,
                "brcode": pix_result.brcode,
                "brcode_base64": pix_result.brcode_base64,
                "expires_at": pix_result.expires_at,
                "created_at": pix_result.created_at,
                "dev_mode": pix_result.dev_mode
            }
            
        except Exception as e:
            print(f"{self.log_prefix} ❌ Erro ao criar PIX: {str(e)}")
            return {
                "success": False,
                "error": str(e)
            }
    
    def check_payment_status(self, pix_id: str) -> Dict[str, Any]:
        """
        Verifica o status de um pagamento PIX
        
        Args:
            pix_id: ID do PIX a ser verificado
        
        Returns:
            Dict com status do pagamento
        """
        try:
            print(f"{self.log_prefix} 🔍 Verificando status do PIX {pix_id}...")
            
            status_result = self.client.pixQrCode.check(pix_id)
            
            print(f"{self.log_prefix} 📊 Status: {status_result.status}")
            
            return {
                "success": True,
                "status": status_result.status,
                "expires_at": status_result.expires_at,
                "is_paid": status_result.status == "PAID",
                "is_expired": status_result.status == "EXPIRED"
            }
            
        except Exception as e:
            print(f"{self.log_prefix} ❌ Erro ao verificar status: {str(e)}")
            return {
                "success": False,
                "error": str(e)
            }
    
    def simulate_payment(self, pix_id: str, metadata: Optional[Dict] = None) -> Dict[str, Any]:
        """
        Simula um pagamento (apenas para desenvolvimento)
        
        Args:
            pix_id: ID do PIX a ser simulado
            metadata: Metadados adicionais para simulação
        
        Returns:
            Dict com resultado da simulação
        """
        try:
            print(f"{self.log_prefix} 🧪 Simulando pagamento do PIX {pix_id}...")
            
            simulation_result = self.client.pixQrCode.simulate(pix_id, metadata or {})
            
            print(f"{self.log_prefix} ✅ Simulação concluída!")
            print(f"   Status: {simulation_result.status}")
            
            return {
                "success": True,
                "status": simulation_result.status,
                "simulated": True
            }
            
        except Exception as e:
            print(f"{self.log_prefix} ❌ Erro na simulação: {str(e)}")
            return {
                "success": False,
                "error": str(e)
            }
    
    def monitor_payment(self, pix_id: str, max_attempts: int = 100, interval: int = 5) -> Dict[str, Any]:
        """
        Monitora um pagamento até ser confirmado ou expirado
        
        Args:
            pix_id: ID do PIX a ser monitorado
            max_attempts: Número máximo de tentativas
            interval: Intervalo entre verificações em segundos
        
        Returns:
            Dict com resultado final do monitoramento
        """
        print(f"{self.log_prefix} 👀 Iniciando monitoramento do PIX {pix_id}...")
        print(f"   Tentativas máximas: {max_attempts}")
        print(f"   Intervalo: {interval}s")
        
        for attempt in range(1, max_attempts + 1):
            print(f"{self.log_prefix} 🔄 Tentativa {attempt}/{max_attempts}")
            
            status_result = self.check_payment_status(pix_id)
            
            if not status_result["success"]:
                print(f"{self.log_prefix} ❌ Erro na verificação: {status_result['error']}")
                continue
            
            status = status_result["status"]
            
            if status == "PAID":
                print(f"{self.log_prefix} 🎉 Pagamento confirmado!")
                return {
                    "success": True,
                    "status": "PAID",
                    "attempts": attempt,
                    "final_status": status
                }
            elif status == "EXPIRED":
                print(f"{self.log_prefix} ⏰ PIX expirado!")
                return {
                    "success": True,
                    "status": "EXPIRED",
                    "attempts": attempt,
                    "final_status": status
                }
            elif status == "CANCELLED":
                print(f"{self.log_prefix} ❌ PIX cancelado!")
                return {
                    "success": True,
                    "status": "CANCELLED",
                    "attempts": attempt,
                    "final_status": status
                }
            
            print(f"{self.log_prefix} ⏳ Aguardando... Status atual: {status}")
            time.sleep(interval)
        
        print(f"{self.log_prefix} ⏰ Tempo limite atingido após {max_attempts} tentativas")
        return {
            "success": False,
            "error": "Tempo limite atingido",
            "attempts": max_attempts
        }


def main():
    """Função principal para demonstração"""
    print("🌵 PrescrevaMe Premium - Gerenciador de PIX")
    print("=" * 50)
    
    # Inicializar gerenciador
    manager = PrescrevaMePixManager()
    
    # Dados de exemplo (CPF válido para teste)
    customer_data = {
        "customer_name": "João Silva Teste",
        "customer_email": "joao.teste@exemplo.com",
        "customer_phone": "+55 11 99999-9999",
        "customer_cpf": "11144477735"  # CPF válido para teste
    }
    
    print(f"📋 Dados do cliente:")
    for key, value in customer_data.items():
        print(f"   {key}: {value}")
    
    # Criar PIX
    pix_result = manager.create_pix_payment(**customer_data)
    
    if not pix_result["success"]:
        print(f"❌ Falha ao criar PIX: {pix_result['error']}")
        return
    
    pix_id = pix_result["pix_id"]
    
    print(f"\n📱 PIX criado com sucesso!")
    print(f"   ID: {pix_id}")
    print(f"   Código PIX: {pix_result['brcode'][:50]}...")
    
    # Opção de simulação (desenvolvimento)
    if pix_result.get("dev_mode"):
        print(f"\n🧪 Modo desenvolvimento detectado!")
        simulate = input("Deseja simular o pagamento? (s/n): ").lower().strip()
        
        if simulate == 's':
            sim_result = manager.simulate_payment(pix_id)
            if sim_result["success"]:
                print(f"✅ Pagamento simulado com sucesso!")
            else:
                print(f"❌ Erro na simulação: {sim_result['error']}")
    
    # Monitorar pagamento
    monitor = input("\nDeseja monitorar o pagamento? (s/n): ").lower().strip()
    
    if monitor == 's':
        monitor_result = manager.monitor_payment(pix_id, max_attempts=20, interval=5)
        
        if monitor_result["success"]:
            print(f"\n🏁 Monitoramento finalizado!")
            print(f"   Status final: {monitor_result['final_status']}")
            print(f"   Tentativas: {monitor_result['attempts']}")
        else:
            print(f"\n⏰ Monitoramento interrompido: {monitor_result['error']}")


if __name__ == "__main__":
    main()
