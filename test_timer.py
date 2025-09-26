#!/usr/bin/env python3
"""
PrescrevaMe Premium - Teste do Timer de PIX
Cria um PIX com tempo de expiração curto para testar o timer
"""

import abacatepay
from abacatepay.customers import CustomerMetadata
from abacatepay.pixQrCode import PixQrCodeIn
import time

def test_pix_timer():
    """Testa o timer de expiração do PIX"""
    print("🌵 PrescrevaMe Premium - Teste do Timer PIX")
    print("=" * 50)
    
    # Cliente
    client = abacatepay.AbacatePay("abc_dev_xp4Fa35xjKCq1tndyRzEEj3w")
    
    # Cliente
    customer = CustomerMetadata(
        name="Teste Timer",
        email="timer@teste.com",
        cellphone="+55 11 99999-9999",
        tax_id="11144477735"
    )
    
    # PIX com 2 minutos de expiração para teste
    pix_data = PixQrCodeIn(
        amount=34700,
        expires_in=120,  # 2 minutos para teste
        description="PrescrevaMe Premium - Teste Timer",
        customer=customer
    )
    
    print("⏰ Criando PIX com 2 minutos de expiração...")
    
    try:
        pix_result = client.pixQrCode.create(pix_data)
        
        print("✅ PIX criado com sucesso!")
        print(f"   ID: {pix_result.id}")
        print(f"   Valor: R$ {pix_result.amount/100:.2f}")
        print(f"   Expira em: {pix_result.expires_at}")
        print(f"   Tempo restante: 2 minutos")
        print()
        
        print("🔗 Acesse o checkout para ver o timer em ação:")
        print("   http://localhost:8000/checkout.php")
        print()
        
        print("📱 O timer mostrará:")
        print("   • ⏰ Contador visual em tempo real")
        print("   • 🟡 Cor laranja (normal)")
        print("   • 🔴 Cor vermelha + piscar (últimos 5 min)")
        print("   • ❌ PIX expirado (quando expirar)")
        print()
        
        print("🧪 Para testar rapidamente:")
        print("   1. Acesse o checkout")
        print("   2. Preencha o formulário")
        print("   3. Observe o timer funcionando")
        print("   4. Aguarde a expiração em 2 minutos")
        print()
        
        # Mostrar informações do PIX
        print("📋 Informações do PIX para teste:")
        print(f"   PIX ID: {pix_result.id}")
        print(f"   Código PIX: {pix_result.brcode[:50]}...")
        print(f"   QR Code: Disponível no checkout")
        
        return pix_result
        
    except Exception as e:
        print(f"❌ Erro ao criar PIX: {e}")
        return None

if __name__ == "__main__":
    test_pix_timer()
