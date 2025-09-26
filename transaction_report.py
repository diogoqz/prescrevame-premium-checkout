#!/usr/bin/env python3
"""
PrescrevaMe Premium - Relatório de Transações
Gera relatórios detalhados de pagamentos e transações
"""

import json
import csv
import os
from datetime import datetime, timedelta
from typing import List, Dict, Any, Optional
import abacatepay
from collections import defaultdict
from dotenv import load_dotenv

# Carregar variáveis de ambiente
load_dotenv()

# Configurações
API_KEY = os.getenv('ABACATE_API_KEY', '')

class TransactionReporter:
    """Gerador de relatórios de transações"""
    
    def __init__(self, api_key: str = API_KEY):
        """Inicializa o cliente AbacatePay"""
        self.client = abacatepay.AbacatePay(api_key)
        self.log_prefix = "📊 PrescrevaMe Reports"
    
    def load_payment_logs(self, log_file: str = "payment_logs.json") -> List[Dict[str, Any]]:
        """Carrega logs de pagamento do arquivo"""
        try:
            logs = []
            with open(log_file, "r", encoding="utf-8") as f:
                for line in f:
                    if line.strip():
                        logs.append(json.loads(line))
            return logs
        except FileNotFoundError:
            print(f"{self.log_prefix} ⚠️ Arquivo de logs não encontrado: {log_file}")
            return []
        except Exception as e:
            print(f"{self.log_prefix} ❌ Erro ao carregar logs: {e}")
            return []
    
    def generate_summary_report(self, logs: List[Dict[str, Any]]) -> Dict[str, Any]:
        """Gera relatório resumido das transações"""
        print(f"{self.log_prefix} 📈 Gerando relatório resumido...")
        
        summary = {
            "total_transactions": len(logs),
            "total_amount": 0,
            "status_breakdown": defaultdict(int),
            "daily_breakdown": defaultdict(int),
            "monthly_breakdown": defaultdict(int),
            "customer_breakdown": defaultdict(int),
            "payment_methods": defaultdict(int)
        }
        
        for log in logs:
            # Contar por status
            status = log.get("status", "UNKNOWN")
            summary["status_breakdown"][status] += 1
            
            # Somar valores (apenas pagamentos confirmados)
            if status == "PAID":
                amount = log.get("amount", 0)
                summary["total_amount"] += amount
            
            # Contar por dia
            timestamp = log.get("timestamp", "")
            if timestamp:
                date = timestamp.split("T")[0]
                summary["daily_breakdown"][date] += 1
                
                # Contar por mês
                month = "-".join(date.split("-")[:2])
                summary["monthly_breakdown"][month] += 1
            
            # Contar por cliente
            customer = log.get("customer", {})
            customer_name = customer.get("name", "Desconhecido")
            summary["customer_breakdown"][customer_name] += 1
            
            # Método de pagamento (PIX para todos no nosso caso)
            summary["payment_methods"]["PIX"] += 1
        
        return dict(summary)
    
    def generate_detailed_report(self, logs: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """Gera relatório detalhado das transações"""
        print(f"{self.log_prefix} 📋 Gerando relatório detalhado...")
        
        detailed = []
        
        for log in logs:
            customer = log.get("customer", {})
            pix_data = log.get("data", {})
            
            record = {
                "timestamp": log.get("timestamp", ""),
                "pix_id": log.get("pix_id", ""),
                "status": log.get("status", ""),
                "amount": log.get("amount", 0),
                "amount_formatted": f"R$ {log.get('amount', 0)/100:.2f}",
                "customer_name": customer.get("name", ""),
                "customer_email": customer.get("email", ""),
                "customer_phone": customer.get("cellphone", ""),
                "customer_cpf": customer.get("tax_id", ""),
                "created_at": pix_data.get("created_at", ""),
                "expires_at": pix_data.get("expires_at", ""),
                "dev_mode": pix_data.get("dev_mode", False)
            }
            
            detailed.append(record)
        
        return detailed
    
    def export_to_csv(self, data: List[Dict[str, Any]], filename: str) -> bool:
        """Exporta dados para arquivo CSV"""
        try:
            if not data:
                print(f"{self.log_prefix} ⚠️ Nenhum dado para exportar")
                return False
            
            with open(filename, "w", newline="", encoding="utf-8") as csvfile:
                fieldnames = data[0].keys()
                writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
                
                writer.writeheader()
                for row in data:
                    writer.writerow(row)
            
            print(f"{self.log_prefix} ✅ Dados exportados para: {filename}")
            return True
            
        except Exception as e:
            print(f"{self.log_prefix} ❌ Erro ao exportar CSV: {e}")
            return False
    
    def print_summary(self, summary: Dict[str, Any]) -> None:
        """Imprime resumo formatado"""
        print(f"\n{self.log_prefix} 📊 RELATÓRIO RESUMIDO")
        print("=" * 60)
        
        print(f"📈 Total de Transações: {summary['total_transactions']}")
        print(f"💰 Valor Total: R$ {summary['total_amount']/100:.2f}")
        
        print(f"\n📊 Status das Transações:")
        for status, count in summary['status_breakdown'].items():
            emoji = {
                "PAID": "✅",
                "PENDING": "⏳", 
                "EXPIRED": "⏰",
                "CANCELLED": "❌"
            }.get(status, "❓")
            print(f"   {emoji} {status}: {count}")
        
        print(f"\n📅 Transações por Mês:")
        for month, count in sorted(summary['monthly_breakdown'].items()):
            print(f"   {month}: {count} transações")
        
        print(f"\n👥 Top 5 Clientes:")
        sorted_customers = sorted(
            summary['customer_breakdown'].items(), 
            key=lambda x: x[1], 
            reverse=True
        )[:5]
        
        for customer, count in sorted_customers:
            print(f"   {customer}: {count} transações")
        
        print("=" * 60)
    
    def generate_prescreva_me_report(self, logs: List[Dict[str, Any]]) -> Dict[str, Any]:
        """Gera relatório específico para PrescrevaMe Premium"""
        print(f"{self.log_prefix} 🌵 Gerando relatório PrescrevaMe Premium...")
        
        # Filtrar apenas transações do PrescrevaMe (valor padrão)
        prescreva_logs = [
            log for log in logs 
            if log.get("amount") == 34700  # R$ 347,00
        ]
        
        if not prescreva_logs:
            print(f"{self.log_prefix} ⚠️ Nenhuma transação do PrescrevaMe encontrada")
            return {}
        
        # Análise específica
        report = {
            "product": "PrescrevaMe Premium",
            "price": 34700,
            "total_subscriptions": len(prescreva_logs),
            "confirmed_subscriptions": len([l for l in prescreva_logs if l.get("status") == "PAID"]),
            "revenue": sum(l.get("amount", 0) for l in prescreva_logs if l.get("status") == "PAID"),
            "conversion_rate": 0,
            "customer_analysis": defaultdict(int),
            "monthly_subscriptions": defaultdict(int)
        }
        
        # Calcular taxa de conversão
        if report["total_subscriptions"] > 0:
            report["conversion_rate"] = (report["confirmed_subscriptions"] / report["total_subscriptions"]) * 100
        
        # Análise de clientes e meses
        for log in prescreva_logs:
            customer = log.get("customer", {})
            customer_name = customer.get("name", "Desconhecido")
            report["customer_analysis"][customer_name] += 1
            
            timestamp = log.get("timestamp", "")
            if timestamp:
                month = "-".join(timestamp.split("T")[0].split("-")[:2])
                report["monthly_subscriptions"][month] += 1
        
        return dict(report)
    
    def print_prescreva_me_summary(self, report: Dict[str, Any]) -> None:
        """Imprime resumo do PrescrevaMe"""
        if not report:
            return
        
        print(f"\n{self.log_prefix} 🌵 RELATÓRIO PRESCREVAME PREMIUM")
        print("=" * 60)
        
        print(f"📦 Produto: {report['product']}")
        print(f"💰 Preço: R$ {report['price']/100:.2f}")
        print(f"📊 Total de Tentativas: {report['total_subscriptions']}")
        print(f"✅ Assinaturas Confirmadas: {report['confirmed_subscriptions']}")
        print(f"💵 Receita Total: R$ {report['revenue']/100:.2f}")
        print(f"📈 Taxa de Conversão: {report['conversion_rate']:.1f}%")
        
        print(f"\n📅 Assinaturas por Mês:")
        for month, count in sorted(report['monthly_subscriptions'].items()):
            print(f"   {month}: {count} assinaturas")
        
        print("=" * 60)


def main():
    """Função principal"""
    print("🌵 PrescrevaMe Premium - Relatório de Transações")
    print("=" * 50)
    
    # Inicializar reporter
    reporter = TransactionReporter()
    
    # Carregar logs
    print(f"📂 Carregando logs de pagamento...")
    logs = reporter.load_payment_logs()
    
    if not logs:
        print(f"⚠️ Nenhum log encontrado. Execute o webhook_handler.py primeiro.")
        return
    
    print(f"✅ {len(logs)} logs carregados")
    
    # Gerar relatórios
    summary = reporter.generate_summary_report(logs)
    detailed = reporter.generate_detailed_report(logs)
    prescreva_report = reporter.generate_prescreva_me_report(logs)
    
    # Exibir resumos
    reporter.print_summary(summary)
    reporter.print_prescreva_me_summary(prescreva_report)
    
    # Exportar dados
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    
    # CSV detalhado
    csv_filename = f"transactions_detailed_{timestamp}.csv"
    reporter.export_to_csv(detailed, csv_filename)
    
    # JSON do resumo
    summary_filename = f"summary_report_{timestamp}.json"
    try:
        with open(summary_filename, "w", encoding="utf-8") as f:
            json.dump(summary, f, ensure_ascii=False, indent=2)
        print(f"{reporter.log_prefix} ✅ Resumo exportado para: {summary_filename}")
    except Exception as e:
        print(f"{reporter.log_prefix} ❌ Erro ao exportar resumo: {e}")
    
    print(f"\n🎉 Relatórios gerados com sucesso!")


if __name__ == "__main__":
    main()
