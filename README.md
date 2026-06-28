# 💰 UMKM Financial Report API

> REST API untuk laporan keuangan UMKM — dibangun dengan Laravel 13, PostgreSQL, Redis, dan AI Insights.

[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?logo=postgresql)](https://postgresql.org)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?logo=redis)](https://redis.io)
[![OpenRouter](https://img.shields.io/badge/OpenRouter-AI-412991)](https://openrouter.ai)

---

## 📋 Daftar Isi

- [Tech Stack](#tech-stack)
- [Cara Install](#cara-install)
- [Base URL & Headers](#base-url--headers)

---

## Tech Stack

| Layer | Tech |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.4 |
| Database | PostgreSQL 16 |
| Cache & Queue | Redis 7 + Predis |
| Auth | Laravel Sanctum (Bearer Token) |
| Queue Monitor | Laravel Horizon (`/horizon`) |
| API Docs | dedoc/scramble (`/docs/api`) |
| Error Log | opcodesio/log-viewer (`/log-viewer`) |
| Health Check | laravel-health by spatie |
| AI | OpenRouter x OpenAI |
| Export | PDF (DomPDF) + Excel (Maatwebsite) |

---

## Cara Install

```bash
# 1. Clone repo
git clone https://github.com/bimanyunugroho/financial-umkm-api.git
cd umkm-financial-api

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Isi konfigurasi di .env
# DB_DATABASE, DB_USERNAME, DB_PASSWORD
# REDIS_HOST, REDIS_PORT | LOCAL SUDAH DEFAULT JADI DIBIARIN AJA
# OPENAI_API_KEY, OPENAI_MODEL

# 5. Buat database
createdb db_financial_umkm_apis

# 6. Migrasi + seeder (termasuk data dummy 6 bulan)
php artisan migrate:refresh
php artisan db:seed

# 7. Jalankan Horizon (queue worker) | Buka Terminal 1
php artisan horizon

# 8. Jalankan server | Buka Terminal 2
composer run dev
# http://localhost:8000
```

**Demo account (setelah seeder):**
```
Email    : demo@gmail.com
Password : password
```

**Dokumentasi api (README.md)**
```
Buka di folder /doc/api

atau

Buka http://localhost:8000/docs/api
```

---
