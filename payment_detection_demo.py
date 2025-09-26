#!/usr/bin/env python3
"""
PrescrevaMe Premium - Demonstração de Detecção de Pagamentos
Mostra como o sistema detecta quando um PIX foi pago
"""

import abacatepay
import time
from datetime import datetime
from abacatepay.customers import CustomerMetadata
from abacatepay.pixQrCode import PixQrCodeIn

def demonstrate_payment_detection():
    """Demonstra como o sistema detecta pagamentos"""
    print("🌵 PrescrevaMe Premium - Demonstração de Detecção de Pagamentos")
    print("=" * 70)
    
    # Inicializar cliente
    client = abacatepay.AbacatePay("abc_dev_xp4Fa35xjKCq1tndyRzEEj3w")
    
    # Criar PIX
    print("1️⃣ Criando PIX...")
    customer = CustomerMetadata(
        name="Cliente Teste",
        email="teste@exemplo.com",
        cellphone="+55 11 99999-9999",
        tax_id="11144477735"
    )
    
    pix_data = PixQrCodeIn(
        amount=34700,
        expires_in=300,  # 5 minutos para demo
        description="PrescrevaMe Premium - Demo Detecção",
        customer=customer
    )
    
    pix_result = client.pixQrCode.create(pix_data)
    pix_id = pix_result.id
    
    print(f"✅ PIX criado!")
    print(f"   ID: {pix_id}")
    print(f"   Valor: R$ {pix_result.amount/100:.2f}")
    print(f"   Status inicial: {pix_result.status}")
    print()
    
    # Simular detecção de pagamento
    print("2️⃣ Simulando detecção de pagamento...")
    print("   O sistema verifica o status a cada 5 segundos")
    print("   Aguarde 10 segundos para simular o pagamento...")
    print()
    
    # Aguardar 10 segundos
    for i in range(10, 0, -1):
        print(f"   ⏳ {i}...", end="\r")
        time.sleep(1)
    
    print("   ✅ Tempo de espera concluído!")
    print()
    
    # Verificar status (ainda PENDING)
    print("3️⃣ Verificando status após 10 segundos...")
    status = client.pixQrCode.check(pix_id)
    print(f"   Status: {status.status}")
    print()
    
    # Simular pagamento
    print("4️⃣ Simulando pagamento...")
    sim_result = client.pixQrCode.simulate(pix_id)
    print(f"   ✅ Pagamento simulado!")
    print(f"   Status após simulação: {sim_result.status}")
    print()
    
    # Verificar novamente
    print("5️⃣ Verificando status após pagamento...")
    final_status = client.pixQrCode.check(pix_id)
    print(f"   Status final: {final_status.status}")
    print()
    
    # Mostrar como o sistema detecta
    print("6️⃣ Como o sistema detecta o pagamento:")
    print("   📡 Endpoint de verificação: GET /v1/pixQrCode/check?id={pix_id}")
    print("   ⏰ Verificação automática: A cada 5 segundos")
    print("   🔄 Polling contínuo: Enquanto status = PENDING")
    print("   ✅ Detecção: Quando status muda para PAID")
    print()
    
    print("🎯 RESUMO DO PROCESSO:")
    print("   1. PIX criado → Status: PENDING")
    print("   2. Sistema verifica a cada 5s via API")
    print("   3. Cliente paga o PIX")
    print("   4. Próxima verificação detecta: PAID")
    print("   5. Sistema redireciona para recibo")
    print()
    
    print("📋 CÓDIGO JAVASCRIPT (Frontend):")
    print("""
    // Verificação automática a cada 5 segundos
    setInterval(async () => {
        const response = await fetch('/checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=check_payment&pix_id=' + pixId
        });
        
        const result = await response.json();
        if (result.status === 'PAID') {
            // Redirecionar para recibo
            window.location.href = '/receipt.php';
        }
    }, 5000); // A cada 5 segundos
    """)
    
    print("🐍 CÓDIGO PYTHON (Backend):")
    print("""
    # Verificação usando SDK
    status = client.pixQrCode.check(pix_id)
    if status.status == 'PAID':
        # Processar pagamento confirmado
        process_payment_confirmation(pix_id)
    """)
    
    print("🎉 Demonstração concluída!")

if __name__ == "__main__":
    demonstrate_payment_detection()
