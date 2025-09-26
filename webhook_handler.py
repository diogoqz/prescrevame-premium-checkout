#!/usr/bin/env python3
"""
PrescrevaMe Premium - Webhook Handler para AbacatePay
Processa notificações de pagamento da API AbacatePay
"""

import json
import logging
import hashlib
import hmac
from datetime import datetime
from typing import Dict, Any, Optional
from flask import Flask, request, jsonify
import abacatepay

# Configurações
API_KEY = "abc_dev_xp4Fa35xjKCq1tndyRzEEj3w"
WEBHOOK_SECRET = "seu_webhook_secret_aqui"  # Configure no painel AbacatePay
LOG_FILE = "webhook.log"

# Configurar logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(LOG_FILE),
        logging.StreamHandler()
    ]
)

logger = logging.getLogger(__name__)

app = Flask(__name__)
client = abacatepay.AbacatePay(API_KEY)

class WebhookHandler:
    """Handler para processar webhooks do AbacatePay"""
    
    def __init__(self, secret: str):
        self.secret = secret
        self.log_prefix = "🔔 PrescrevaMe Webhook"
    
    def verify_signature(self, payload: str, signature: str) -> bool:
        """
        Verifica a assinatura do webhook para garantir autenticidade
        
        Args:
            payload: Corpo da requisição
            signature: Assinatura enviada no header
        
        Returns:
            True se a assinatura for válida
        """
        try:
            expected_signature = hmac.new(
                self.secret.encode('utf-8'),
                payload.encode('utf-8'),
                hashlib.sha256
            ).hexdigest()
            
            return hmac.compare_digest(signature, expected_signature)
        except Exception as e:
            logger.error(f"{self.log_prefix} ❌ Erro na verificação de assinatura: {e}")
            return False
    
    def process_payment_notification(self, data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Processa notificação de pagamento
        
        Args:
            data: Dados da notificação
        
        Returns:
            Dict com resultado do processamento
        """
        try:
            event_type = data.get("type", "")
            pix_data = data.get("data", {})
            
            logger.info(f"{self.log_prefix} 📨 Processando notificação: {event_type}")
            
            if event_type == "pix.paid":
                return self._handle_payment_confirmed(pix_data)
            elif event_type == "pix.expired":
                return self._handle_payment_expired(pix_data)
            elif event_type == "pix.cancelled":
                return self._handle_payment_cancelled(pix_data)
            else:
                logger.warning(f"{self.log_prefix} ⚠️ Tipo de evento não reconhecido: {event_type}")
                return {"success": False, "error": "Event type not recognized"}
                
        except Exception as e:
            logger.error(f"{self.log_prefix} ❌ Erro ao processar notificação: {e}")
            return {"success": False, "error": str(e)}
    
    def _handle_payment_confirmed(self, pix_data: Dict[str, Any]) -> Dict[str, Any]:
        """Processa pagamento confirmado"""
        try:
            pix_id = pix_data.get("id", "")
            amount = pix_data.get("amount", 0)
            customer = pix_data.get("customer", {})
            
            logger.info(f"{self.log_prefix} 🎉 Pagamento confirmado!")
            logger.info(f"   PIX ID: {pix_id}")
            logger.info(f"   Valor: R$ {amount/100:.2f}")
            logger.info(f"   Cliente: {customer.get('name', 'N/A')}")
            
            # Aqui você pode adicionar lógica específica:
            # - Ativar acesso ao PrescrevaMe
            # - Enviar email de confirmação
            # - Atualizar banco de dados
            # - Notificar sistemas internos
            
            # Exemplo: Salvar log de pagamento confirmado
            self._save_payment_log(pix_data, "PAID")
            
            # Exemplo: Enviar notificação para WhatsApp (se configurado)
            # self._send_whatsapp_confirmation(customer, pix_data)
            
            return {
                "success": True,
                "action": "payment_confirmed",
                "pix_id": pix_id,
                "amount": amount
            }
            
        except Exception as e:
            logger.error(f"{self.log_prefix} ❌ Erro ao processar pagamento confirmado: {e}")
            return {"success": False, "error": str(e)}
    
    def _handle_payment_expired(self, pix_data: Dict[str, Any]) -> Dict[str, Any]:
        """Processa pagamento expirado"""
        try:
            pix_id = pix_data.get("id", "")
            
            logger.info(f"{self.log_prefix} ⏰ Pagamento expirado: {pix_id}")
            
            # Lógica para pagamento expirado:
            # - Limpar dados temporários
            # - Notificar cliente sobre expiração
            # - Oferecer nova tentativa de pagamento
            
            self._save_payment_log(pix_data, "EXPIRED")
            
            return {
                "success": True,
                "action": "payment_expired",
                "pix_id": pix_id
            }
            
        except Exception as e:
            logger.error(f"{self.log_prefix} ❌ Erro ao processar pagamento expirado: {e}")
            return {"success": False, "error": str(e)}
    
    def _handle_payment_cancelled(self, pix_data: Dict[str, Any]) -> Dict[str, Any]:
        """Processa pagamento cancelado"""
        try:
            pix_id = pix_data.get("id", "")
            
            logger.info(f"{self.log_prefix} ❌ Pagamento cancelado: {pix_id}")
            
            # Lógica para pagamento cancelado:
            # - Limpar dados temporários
            # - Registrar motivo do cancelamento
            
            self._save_payment_log(pix_data, "CANCELLED")
            
            return {
                "success": True,
                "action": "payment_cancelled",
                "pix_id": pix_id
            }
            
        except Exception as e:
            logger.error(f"{self.log_prefix} ❌ Erro ao processar pagamento cancelado: {e}")
            return {"success": False, "error": str(e)}
    
    def _save_payment_log(self, pix_data: Dict[str, Any], status: str) -> None:
        """Salva log do pagamento"""
        try:
            log_entry = {
                "timestamp": datetime.now().isoformat(),
                "pix_id": pix_data.get("id", ""),
                "status": status,
                "amount": pix_data.get("amount", 0),
                "customer": pix_data.get("customer", {}),
                "data": pix_data
            }
            
            with open("payment_logs.json", "a", encoding="utf-8") as f:
                f.write(json.dumps(log_entry, ensure_ascii=False) + "\n")
                
        except Exception as e:
            logger.error(f"{self.log_prefix} ❌ Erro ao salvar log: {e}")
    
    def _send_whatsapp_confirmation(self, customer: Dict[str, Any], pix_data: Dict[str, Any]) -> None:
        """Envia confirmação via WhatsApp (exemplo)"""
        try:
            # Implementar integração com WhatsApp Business API
            # ou usar serviço como Twilio, MessageBird, etc.
            
            phone = customer.get("cellphone", "")
            name = customer.get("name", "")
            amount = pix_data.get("amount", 0)
            
            if phone and name:
                message = f"""
🎉 Olá {name}!

Seu pagamento de R$ {amount/100:.2f} foi confirmado com sucesso!

✅ Acesso ao PrescrevaMe Premium ativado
📱 Você já pode usar o assistente médico via WhatsApp
🔗 Link de acesso: https://prescreva.me/acesso

Obrigado por escolher o PrescrevaMe! 🌵
                """
                
                logger.info(f"{self.log_prefix} 📱 WhatsApp preparado para {phone}")
                # Aqui você implementaria o envio real
                
        except Exception as e:
            logger.error(f"{self.log_prefix} ❌ Erro ao enviar WhatsApp: {e}")


# Inicializar handler
webhook_handler = WebhookHandler(WEBHOOK_SECRET)

@app.route('/webhook/abacatepay', methods=['POST'])
def handle_webhook():
    """Endpoint para receber webhooks do AbacatePay"""
    try:
        # Obter dados da requisição
        payload = request.get_data(as_text=True)
        signature = request.headers.get('X-AbacatePay-Signature', '')
        
        logger.info(f"🔔 Webhook recebido de {request.remote_addr}")
        
        # Verificar assinatura (descomente quando configurar o secret)
        # if not webhook_handler.verify_signature(payload, signature):
        #     logger.warning("❌ Assinatura inválida")
        #     return jsonify({"error": "Invalid signature"}), 401
        
        # Processar dados
        data = json.loads(payload)
        result = webhook_handler.process_payment_notification(data)
        
        if result["success"]:
            logger.info(f"✅ Webhook processado com sucesso: {result.get('action', 'unknown')}")
            return jsonify({"status": "success", "message": "Webhook processed"}), 200
        else:
            logger.error(f"❌ Erro ao processar webhook: {result.get('error', 'unknown')}")
            return jsonify({"status": "error", "message": result.get("error")}), 400
            
    except json.JSONDecodeError:
        logger.error("❌ JSON inválido no webhook")
        return jsonify({"error": "Invalid JSON"}), 400
    except Exception as e:
        logger.error(f"❌ Erro geral no webhook: {e}")
        return jsonify({"error": "Internal server error"}), 500

@app.route('/webhook/status', methods=['GET'])
def webhook_status():
    """Endpoint para verificar status do webhook"""
    return jsonify({
        "status": "active",
        "service": "PrescrevaMe Premium Webhook",
        "timestamp": datetime.now().isoformat()
    })

@app.route('/webhook/test', methods=['POST'])
def test_webhook():
    """Endpoint para testar webhook localmente"""
    try:
        test_data = {
            "type": "pix.paid",
            "data": {
                "id": "test_pix_123",
                "amount": 34700,
                "customer": {
                    "name": "João Silva Teste",
                    "email": "joao@teste.com",
                    "cellphone": "+55 11 99999-9999"
                },
                "created_at": datetime.now().isoformat()
            }
        }
        
        result = webhook_handler.process_payment_notification(test_data)
        
        return jsonify({
            "status": "test_completed",
            "result": result
        }), 200
        
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    print("🌵 PrescrevaMe Premium - Webhook Handler")
    print("=" * 50)
    print("🚀 Iniciando servidor webhook...")
    print("📡 Endpoints disponíveis:")
    print("   POST /webhook/abacatepay - Webhook principal")
    print("   GET  /webhook/status - Status do serviço")
    print("   POST /webhook/test - Teste local")
    print("=" * 50)
    
    # Executar servidor
    app.run(
        host='0.0.0.0',
        port=5000,
        debug=True
    )
