# PerfectPay - API de Pagamentos

<div align="center">
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" />
  <img src="https://img.shields.io/badge/Redis-DC382D?style=for-the-badge&logo=redis&logoColor=white" />
</div>

## 🌟 Visão Geral

**PerfectPay** é uma API robusta projetada para evoluir para um microserviço de pagamentos escalável, com capacidade para:

- Integração com múltiplos gateways de pagamento (atualmente com Asaas)
- Processamento assíncrono de alta performance
- Resiliência contra falhas com Circuit Breaker automático
- Dashboard visual para monitoramento (Horizon + Kibana)

## 🚀 Principais Funcionalidades

### ✅ Implementado
- **Processamento em filas** (Redis) para alta carga
- **Circuit Breaker** para alternância automática entre gateways
- **Webhooks** com processamento assíncrono
- **Monitoramento**:
    - Horizon para filas
    - Kibana para análise de logs

### 🔜 Próximas Implementações
- **Métricas em tempo real** com Prometheus + Grafana
- **Sistema de cache** para consultas frequentes
- **Testes completos** (unitários, integração e carga)
- **Benchmark** para 1.000+ requisições simultâneas

## 💻 Frontend Oficial
Disponível em: [PerfectPay Frontend](https://github.com/pedroerico/perfect-pay)

## 📋 Pré-requisitos

- Docker 20.10+
- Docker Compose 2.0+
- Git

## 🚀 Instalação

```bash
# Clone o repositório
git clone https://github.com/pedroerico/perfect-pay-api.git
cd perfect-pay-api

# Copie o arquivo de ambiente
cp .env.example .env

# Inicie os containers
docker-compose up -d
```

## 🚀 Instalação [Perfect-Pay Frontend](https://github.com/pedroerico/perfect-pay)

```bash
# Clone o repositório
git clone https://github.com/pedroerico/perfect-pay.git
cd perfect-pay

# Copie o arquivo de ambiente
cp .env.example .env

# Inicie os containers
docker-compose up -d
```

## 📡 Endpoints

### POST `/api/payments`
Cria um novo pagamento

**Exemplo Cartão de Crédito:**
```json
{
  "amount": 17.90,
  "method": "credit_card",
  "card": {
    "number": "4444444444444444",
    "holder_name": "FULANO DE TAL",
    "expiry_month": "12",
    "expiry_year": "2026",
    "cvv": "123"
  },
  "customer": {
    "name": "Ciclano da Silva",
    "cpf_cnpj": "05794656388",
    "email": "ciclano@empresa.com",
    "postal_code": "60844400",
    "address_number": "123",
    "phone": "1198765-4321"
  }
}
```
**Exemplo para pagamento PIX:**
```json
{
    "amount": 11,
    "method": "pix"
}
```
**Exemplo para pagamento Boleto:**
```json
{
    "amount": 11,
    "method": "boleto"
}
```

- `GET /api/payments` - Listar pagamentos
- `GET /api/payments/{id}` - Detalhes do pagamento

### Webhooks
- `POST /webhooks/asaas` - Receber notificações da Asaas (Necessário configuração no ASAAS)

## 🌐 Acessos Locais

| Serviço  | URL                           | Credenciais     |
|----------|-------------------------------|-----------------|
| API      | http://localhost:8080         | -               |
| Frontend | http://localhost:8000         | -               |
| Horizon  | http://localhost:8080/horizon | -               |
| Kibana   | http://localhost:5601         | elastic/elastic |
| Redis    | http://localhost:6379         | -               |
| MySQL    | http://localhost:3306         | root/root       |

## ⚙️ Variáveis de Ambiente Importantes

| Variável               | Descrição                                         | Exemplo               |
|------------------------|---------------------------------------------------|-----------------------|
| `PAYMENT_USE_QUEUE`    | Ativa/desativa se vai usar filas de processamento | `true`/`false`        |
| `ASAAS_API_KEY`        | Chave de API da Asaas                             | `a1b2c3d4...`         |
| `ASAAS_BASE_URL`       | URL da API da Asaas                               | `https://sandbox.asaas.com/api/v3` |
| `ASAAS_CLIENT_ID`       | Cliente cadastro no asaas para exemplo            | `cus_000006602604`    |

```ini
# Banco de dados
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=perfectpay
DB_USERNAME=root
DB_PASSWORD=root

# Filas
QUEUE_CONNECTION=redis
REDIS_HOST=redis

# Asaas
ASAAS_API_KEY=sua_chave
ASAAS_BASE_URL=https://sandbox.asaas.com/api/v3
ASAAS_CLIENT_ID=seu_client_id

# Configurações
PAYMENT_USE_QUEUE=true

# Gateway
PAYMENT_GATEWAY_DEFAULT=asaas
PAYMENT_GATEWAY_FALLBACK=null
```

## 📊 Monitoramento

### Filas com Horizon
Acesse `http://localhost:8080/horizon` para monitorar:
- Jobs pendentes
- Jobs falhados

Tipos de filas:
- payments: Processamento principal de pagamentos
- webhooks: Processamento de callbacks

### Logs com Kibana
1. Acesse `http://localhost:5601`
2. Crie um index pattern para `laravel-logs-*`
3. Filtre por:
    - `level:ERROR` para erros
    - `service:payment` para logs de pagamento

## 📄 Desenvolvido

Desenvolvido em 31 de março de 2025.

Desenvolvedor: Pedro Érico.
Email: pedroerico.desenvolvedor@gmail.com
