#!/usr/bin/env python3
"""
PrescrevaMe Premium - Teste Rápido do PIX
Script não-interativo para testar a funcionalidade PIX
"""

import abacatepay
from abacatepay.customers import CustomerMetadata
from abacatepay.pixQrCode import PixQrCodeIn

def test_pix_creation():
    """Testa criação de PIX"""
    print("🌵 PrescrevaMe Premium - Teste de PIX")
    print("=" * 40)
    
    # Inicializar cliente
    client = abacatepay.AbacatePay("abc_dev_xp4Fa35xjKCq1tndyRzEEj3w")
    
    # Dados do cliente
    customer = CustomerMetadata(
        name="João Silva Teste",
        email="joao.teste@exemplo.com",
        cellphone="+55 11 99999-9999",
        tax_id="11144477735"  # CPF válido para teste
    )
    
    # Dados do PIX
    pix_data = PixQrCodeIn(
        amount=34700,  # R$ 347,00
        expires_in=900,  # 15 minutos
        description="PrescrevaMe Premium - Teste",
        customer=customer
    )
    
    print("💳 Criando PIX...")
    
    try:
        # Criar PIX
        pix_result = client.pixQrCode.create(pix_data)
        
        print("✅ PIX criado com sucesso!")
        print(f"   ID: {pix_result.id}")
        print(f"   Valor: R$ {pix_result.amount/100:.2f}")
        print(f"   Status: {pix_result.status}")
        print(f"   Expira em: {pix_result.expires_at}")
        print(f"   Modo dev: {pix_result.dev_mode}")
        
        # Verificar status
        print("\n🔍 Verificando status...")
        status_result = client.pixQrCode.check(pix_result.id)
        print(f"   Status atual: {status_result.status}")
        
        # Simular pagamento (modo dev)
        if pix_result.dev_mode:
            print("\n🧪 Simulando pagamento...")
            sim_result = client.pixQrCode.simulate(pix_result.id)
            print(f"   Status após simulação: {sim_result.status}")
            
            # Verificar status novamente
            print("\n🔍 Verificando status após simulação...")
            final_status = client.pixQrCode.check(pix_result.id)
            print(f"   Status final: {final_status.status}")
        
        print("\n🎉 Teste concluído com sucesso!")
        return True
        
    except Exception as e:
        print(f"❌ Erro no teste: {e}")
        return False

if __name__ == "__main__":
    test_pix_creation()
