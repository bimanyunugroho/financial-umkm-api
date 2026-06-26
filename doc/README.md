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
- [Authentication](#authentication)
- [Categories](#categories)
- [Transactions](#transactions)
- [Reports](#reports)
- [AI Insights](#ai-insights)
- [Activity Log](#activity-log)
- [Health Check](#health-check)
- [Error Response Format](#error-response-format)

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
# → http://localhost:8000
```

**Demo account (setelah seeder):**
```
Email    : demo@gmail.com
Password : password
```

---

## Base URL & Headers

```
Base URL : http://localhost:8000/api/v1
```

### Headers yang digunakan

| Header | Value | Keterangan |
|---|---|---|
| `Content-Type` | `application/json` | Wajib untuk semua request |
| `Accept` | `application/json` | Wajib untuk semua request |
| `Authorization` | `Bearer {token}` | Wajib untuk endpoint yang membutuhkan auth |
| `Idempotency-Key` | `{uuid-v4}` | Opsional — mencegah duplikasi pada POST/PUT |

### Contoh header lengkap (request dengan auth):

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

### Format response sukses:

```json
{
    "success": true,
    "message": "Pesan sukses.",
    "data": { }
}
```

### Format response paginasi:

```json
{
    "success": true,
    "message": "Daftar data.",
    "data": [ ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7,
        "from": 1,
        "to": 15,
        "has_more": true,
        "next_page_url": "http://localhost:8000/api/v1/transactions?page=2",
        "prev_page_url": null
    }
}
```

---

## Authentication

### 1. Register

```http
POST /api/v1/auth/register
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "business_name": "Warung Makan Bu Sari",
    "business_type": "Warung Makan",
    "phone": "081234567890",
    "address": "Jl. Sudirman No. 12, Jakarta Selatan"
}
```

> `phone` dan `address` bersifat opsional.

**Response `201 Created`:**
```json
{
    "success": true,
    "message": "Akun berhasil dibuat.",
    "data": {
        "user": {
            "id": "550e8400-e29b-41d4-a716-446655440000",
            "name": "Budi Santoso",
            "email": "budi@example.com",
            "business_name": "Warung Makan Bu Sari",
            "business_type": "Warung Makan",
            "phone": "081234567890",
            "address": "Jl. Sudirman No. 12, Jakarta Selatan",
            "created_at": "2024-01-15T08:00:00+07:00",
            "updated_at": "2024-01-15T08:00:00+07:00"
        },
        "token": "1|abc123xyz...",
        "token_type": "Bearer",
        "expires_at": "2024-01-22T08:00:00+07:00"
    }
}
```

---

### 2. Login

```http
POST /api/v1/auth/login
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "email": "demo@umkm-api.com",
    "password": "password"
}
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Login berhasil.",
    "data": {
        "user": {
            "id": "550e8400-e29b-41d4-a716-446655440000",
            "name": "Budi Santoso",
            "email": "demo@umkm-api.com",
            "business_name": "Warung Makan Bu Sari",
            "business_type": "Warung Makan",
            "phone": "081234567890",
            "address": "Jl. Sudirman No. 12, Jakarta Selatan",
            "created_at": "2024-01-15T08:00:00+07:00",
            "updated_at": "2024-01-15T08:00:00+07:00"
        },
        "token": "1|abc123xyz...",
        "token_type": "Bearer",
        "expires_at": "2024-01-22T08:00:00+07:00"
    }
}
```

> Simpan nilai `token` — dipakai sebagai `Authorization: Bearer {token}` untuk semua request selanjutnya.

---

### 3. Get Profile (Me)

```http
GET /api/v1/auth/me
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Data profil.",
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Budi Santoso",
        "email": "demo@umkm-api.com",
        "business_name": "Warung Makan Bu Sari",
        "business_type": "Warung Makan",
        "phone": "081234567890",
        "address": "Jl. Sudirman No. 12, Jakarta Selatan",
        "created_at": "2024-01-15T08:00:00+07:00",
        "updated_at": "2024-01-15T08:00:00+07:00"
    }
}
```

---

### 4. Update Profile

```http
PUT /api/v1/auth/me
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Body** (semua field opsional, kirim hanya yang ingin diubah):
```json
{
    "name": "Budi Santoso Updated",
    "business_name": "Warung Makan Bu Sari 2",
    "phone": "089999999999",
    "password": "NewPassword123",
    "password_confirmation": "NewPassword123"
}
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Profil berhasil diperbarui.",
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Budi Santoso Updated",
        "email": "demo@umkm-api.com",
        "business_name": "Warung Makan Bu Sari 2",
        "business_type": "Warung Makan",
        "phone": "089999999999",
        "address": "Jl. Sudirman No. 12, Jakarta Selatan",
        "created_at": "2024-01-15T08:00:00+07:00",
        "updated_at": "2024-01-15T09:30:00+07:00"
    }
}
```

---

### 5. Logout

```http
POST /api/v1/auth/logout
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Body:** *(tidak perlu)*

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Logout berhasil."
}
```

---

## Categories

### 1. List Categories

```http
GET /api/v1/categories
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Daftar kategori.",
    "data": [
        {
            "id": "aaa-111",
            "name": "Penjualan Produk",
            "type": "income",
            "type_label": "Pemasukan",
            "icon": "shopping-bag",
            "color": "#10b981",
            "is_default": true,
            "is_custom": false,
            "created_at": "2024-01-01T00:00:00+07:00"
        },
        {
            "id": "bbb-222",
            "name": "Bahan Baku",
            "type": "expense",
            "type_label": "Pengeluaran",
            "icon": "package",
            "color": "#ef4444",
            "is_default": true,
            "is_custom": false,
            "created_at": "2024-01-01T00:00:00+07:00"
        }
    ],
    "meta": {
        "total": 18,
        "income": 6,
        "expense": 12
    }
}
```

---

### 2. Create Category

```http
POST /api/v1/categories
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Body:**
```json
{
    "name": "Bonus Akhir Tahun",
    "type": "income",
    "icon": "gift",
    "color": "#f59e0b"
}
```

> `icon` dan `color` bersifat opsional. `type` harus `income` atau `expense`.

**Response `201 Created`:**
```json
{
    "success": true,
    "message": "Kategori berhasil dibuat.",
    "data": {
        "id": "ccc-333",
        "name": "Bonus Akhir Tahun",
        "type": "income",
        "type_label": "Pemasukan",
        "icon": "gift",
        "color": "#f59e0b",
        "is_default": false,
        "is_custom": true,
        "created_at": "2024-01-15T10:00:00+07:00"
    }
}
```

---

### 3. Show Category

```http
GET /api/v1/categories/{id}
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Response `200 OK`:** *(format sama dengan item di list)*

---

### 4. Update Category

```http
PUT /api/v1/categories/{id}
```

> Hanya kategori custom milik user yang bisa diupdate. Kategori default tidak bisa diedit.

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Body** (semua opsional):
```json
{
    "name": "Bonus Akhir Tahun (Updated)",
    "color": "#6366f1"
}
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Kategori berhasil diperbarui.",
    "data": {
        "id": "ccc-333",
        "name": "Bonus Akhir Tahun (Updated)",
        "type": "income",
        "type_label": "Pemasukan",
        "icon": "gift",
        "color": "#6366f1",
        "is_default": false,
        "is_custom": true,
        "created_at": "2024-01-15T10:00:00+07:00"
    }
}
```

---

### 5. Delete Category

```http
DELETE /api/v1/categories/{id}
```

> Kategori yang masih memiliki transaksi tidak dapat dihapus.

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Kategori berhasil dihapus."
}
```

---

## Transactions

### 1. List Transactions

```http
GET /api/v1/transactions
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Query Parameters (semua opsional):**

| Parameter | Tipe | Contoh | Keterangan |
|---|---|---|---|
| `filter[type]` | string | `income` | Filter tipe: `income` atau `expense` |
| `filter[category_id]` | uuid | `aaa-111` | Filter by kategori |
| `filter[payment_method]` | string | `qris` | `cash`, `transfer`, `qris`, `ewallet`, `credit` |
| `filter[date_from]` | date | `2024-01-01` | Dari tanggal (format `Y-m-d`) |
| `filter[date_to]` | date | `2024-01-31` | Sampai tanggal (format `Y-m-d`) |
| `filter[min_amount]` | number | `100000` | Jumlah minimum |
| `filter[max_amount]` | number | `5000000` | Jumlah maksimum |
| `filter[description]` | string | `gaji` | Cari di deskripsi |
| `sort` | string | `-date` | Sort field. Prefix `-` untuk descending. Pilihan: `date`, `amount`, `created_at` |
| `per_page` | integer | `25` | Item per halaman (max 50, default 15) |

**Contoh dengan filter:**
```
GET /api/v1/transactions?filter[type]=income&filter[date_from]=2024-01-01&filter[date_to]=2024-01-31&sort=-amount&per_page=25
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Daftar transaksi.",
    "data": [
        {
            "id": "tx-uuid-001",
            "type": "income",
            "type_label": "Pemasukan",
            "amount": 2500000.00,
            "amount_formatted": "Rp 2.500.000",
            "description": "Penjualan produk harian",
            "date": "2024-01-15",
            "payment_method": "qris",
            "payment_label": "QRIS",
            "notes": "Penjualan sabtu-minggu",
            "reference_number": "INV-2024-001",
            "is_deleted": false,
            "category": {
                "id": "aaa-111",
                "name": "Penjualan Produk",
                "type": "income",
                "icon": "shopping-bag",
                "color": "#10b981"
            },
            "attachments": [],
            "deleted_at": null,
            "created_at": "2024-01-15T08:30:00+07:00",
            "updated_at": "2024-01-15T08:30:00+07:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 87,
        "last_page": 6,
        "from": 1,
        "to": 15,
        "has_more": true,
        "next_page_url": "http://localhost:8000/api/v1/transactions?page=2",
        "prev_page_url": null
    }
}
```

---

### 2. Create Transaction

```http
POST /api/v1/transactions
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer 1|abc123xyz...
Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000
```

> `Idempotency-Key` bersifat opsional tapi **sangat direkomendasikan** untuk mencegah duplikasi jika request dikirim ulang akibat network error. Gunakan UUID v4 yang unik per transaksi.

**Body:**
```json
{
    "type": "income",
    "amount": 1500000,
    "category_id": "aaa-111-uuid-kategori",
    "description": "Penjualan produk harian",
    "date": "2024-01-15",
    "payment_method": "qris",
    "notes": "Termasuk penjualan weekend",
    "reference_number": "INV-2024-001"
}
```

> `notes` dan `reference_number` bersifat opsional.
> `payment_method`: `cash` | `transfer` | `qris` | `ewallet` | `credit`
> `date` tidak boleh di masa depan.

**Response `201 Created`:**
```json
{
    "success": true,
    "message": "Transaksi berhasil dibuat.",
    "data": {
        "id": "tx-uuid-001",
        "type": "income",
        "type_label": "Pemasukan",
        "amount": 1500000.00,
        "amount_formatted": "Rp 1.500.000",
        "description": "Penjualan produk harian",
        "date": "2024-01-15",
        "payment_method": "qris",
        "payment_label": "QRIS",
        "notes": "Termasuk penjualan weekend",
        "reference_number": "INV-2024-001",
        "is_deleted": false,
        "category": {
            "id": "aaa-111",
            "name": "Penjualan Produk",
            "type": "income",
            "icon": "shopping-bag",
            "color": "#10b981"
        },
        "attachments": [],
        "deleted_at": null,
        "created_at": "2024-01-15T08:30:00+07:00",
        "updated_at": "2024-01-15T08:30:00+07:00"
    }
}
```

---

### 3. Show Transaction

```http
GET /api/v1/transactions/{id}
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Response `200 OK`:** *(format sama dengan item di list)*

---

### 4. Update Transaction

```http
PUT /api/v1/transactions/{id}
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer 1|abc123xyz...
Idempotency-Key: 550e8400-e29b-41d4-a716-446655440001
```

**Body** (semua field opsional, kirim hanya yang ingin diubah):
```json
{
    "amount": 2000000,
    "description": "Penjualan produk harian (dikoreksi)",
    "payment_method": "transfer",
    "notes": "Koreksi jumlah setelah dicek ulang"
}
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Transaksi berhasil diperbarui.",
    "data": {
        "id": "tx-uuid-001",
        "type": "income",
        "type_label": "Pemasukan",
        "amount": 2000000.00,
        "amount_formatted": "Rp 2.000.000",
        "description": "Penjualan produk harian (dikoreksi)",
        "date": "2024-01-15",
        "payment_method": "transfer",
        "payment_label": "Transfer Bank",
        "notes": "Koreksi jumlah setelah dicek ulang",
        "reference_number": "INV-2024-001",
        "is_deleted": false,
        "category": {
            "id": "aaa-111",
            "name": "Penjualan Produk",
            "type": "income",
            "icon": "shopping-bag",
            "color": "#10b981"
        },
        "attachments": [],
        "deleted_at": null,
        "created_at": "2024-01-15T08:30:00+07:00",
        "updated_at": "2024-01-15T09:00:00+07:00"
    }
}
```

---

### 5. Delete Transaction (Soft Delete)

```http
DELETE /api/v1/transactions/{id}
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Transaksi berhasil dihapus."
}
```

> Data tidak benar-benar terhapus dari database (soft delete). Bisa dipulihkan.

---

### 6. Restore Transaction

```http
POST /api/v1/transactions/{id}/restore
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Transaksi berhasil dipulihkan."
}
```

---

## Reports

### 1. Dashboard Summary

```http
GET /api/v1/reports/summary
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Ringkasan bulan ini.",
    "data": {
        "this_month": {
            "income": 15000000.00,
            "expense": 8500000.00,
            "profit": 6500000.00,
            "profit_margin": 43.33
        },
        "comparison_prev_month": {
            "income_diff": 2000000.00,
            "expense_diff": 500000.00,
            "profit_diff": 1500000.00,
            "income_pct": 15.38,
            "expense_pct": 6.25,
            "profit_pct": 30.00
        },
        "by_category": [
            {
                "category_id": "aaa-111",
                "type": "income",
                "transaction_count": 12,
                "total_amount": "9000000.00",
                "avg_amount": "750000.00",
                "category": {
                    "id": "aaa-111",
                    "name": "Penjualan Produk",
                    "icon": "shopping-bag",
                    "color": "#10b981",
                    "type": "income"
                }
            }
        ],
        "trend_6_months": [
            { "month": "2023-08", "income": 12000000, "expense": 7000000, "profit": 5000000 },
            { "month": "2023-09", "income": 13000000, "expense": 8000000, "profit": 5000000 },
            { "month": "2023-10", "income": 11000000, "expense": 7500000, "profit": 3500000 },
            { "month": "2023-11", "income": 14000000, "expense": 8200000, "profit": 5800000 },
            { "month": "2023-12", "income": 13000000, "expense": 8000000, "profit": 5000000 },
            { "month": "2024-01", "income": 15000000, "expense": 8500000, "profit": 6500000 }
        ],
        "generated_at": "2024-01-15T10:00:00+07:00"
    }
}
```

---

### 2. Profit & Loss

```http
GET /api/v1/reports/profit-loss
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Query Parameters:**

| Parameter | Tipe | Contoh | Keterangan |
|---|---|---|---|
| `period` | string | `monthly` | `daily` `weekly` `monthly` `yearly` `custom` (default: `monthly`) |
| `year` | integer | `2024` | Tahun (default: tahun ini) |
| `month` | integer | `1` | Bulan 1–12 (default: bulan ini) |
| `date_from` | date | `2024-01-01` | Wajib jika `period=custom` |
| `date_to` | date | `2024-03-31` | Wajib jika `period=custom` |

**Contoh request:**
```
GET /api/v1/reports/profit-loss?period=monthly&year=2024&month=1
GET /api/v1/reports/profit-loss?period=yearly&year=2024
GET /api/v1/reports/profit-loss?period=custom&date_from=2024-01-01&date_to=2024-03-31
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Laporan laba rugi.",
    "data": {
        "period": "Januari 2024",
        "date_from": "2024-01-01",
        "date_to": "2024-01-31",
        "gross_income": 15000000.00,
        "total_expense": 8500000.00,
        "net_profit": 6500000.00,
        "profit_margin": 43.33,
        "profit_status": "profit",
        "by_category": [ ],
        "comparison": {
            "prev_date_from": "2023-12-01",
            "prev_date_to": "2023-12-31",
            "prev_income": 13000000.00,
            "prev_expense": 8000000.00,
            "prev_profit": 5000000.00,
            "income_change": 15.38,
            "expense_change": 6.25,
            "profit_change": 30.00
        },
        "generated_at": "2024-01-15T10:00:00+07:00"
    }
}
```

> `profit_status` bernilai: `profit` | `loss` | `break_even`

---

### 3. Cash Flow

```http
GET /api/v1/reports/cash-flow
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Query Parameters:** *(sama dengan profit-loss)*

**Contoh request:**
```
GET /api/v1/reports/cash-flow?period=monthly&year=2024&month=1
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Laporan arus kas.",
    "data": {
        "period": "Januari 2024",
        "date_from": "2024-01-01",
        "date_to": "2024-01-31",
        "total_inflow": 15000000.00,
        "total_outflow": 8500000.00,
        "net_flow": 6500000.00,
        "daily_breakdown": [
            {
                "date": "2024-01-01",
                "inflow": 500000.00,
                "outflow": 200000.00,
                "net": 300000.00,
                "running_balance": 300000.00
            },
            {
                "date": "2024-01-02",
                "inflow": 750000.00,
                "outflow": 450000.00,
                "net": 300000.00,
                "running_balance": 600000.00
            }
        ],
        "generated_at": "2024-01-15T10:00:00+07:00"
    }
}
```

---

### 4. By Category

```http
GET /api/v1/reports/by-category
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Contoh request:**
```
GET /api/v1/reports/by-category?period=monthly&year=2024&month=1
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Laporan per kategori.",
    "data": {
        "period": "Januari 2024",
        "date_from": "2024-01-01",
        "date_to": "2024-01-31",
        "by_category": [
            {
                "category_id": "aaa-111",
                "type": "income",
                "transaction_count": 12,
                "total_amount": "9000000.00",
                "avg_amount": "750000.00",
                "category": {
                    "id": "aaa-111",
                    "name": "Penjualan Produk",
                    "icon": "shopping-bag",
                    "color": "#10b981",
                    "type": "income"
                }
            },
            {
                "category_id": "bbb-222",
                "type": "expense",
                "transaction_count": 8,
                "total_amount": "4200000.00",
                "avg_amount": "525000.00",
                "category": {
                    "id": "bbb-222",
                    "name": "Bahan Baku",
                    "icon": "package",
                    "color": "#ef4444",
                    "type": "expense"
                }
            }
        ],
        "generated_at": "2024-01-15T10:00:00+07:00"
    }
}
```

---

### 5. Monthly Trend

```http
GET /api/v1/reports/trend
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Query Parameters:**

| Parameter | Tipe | Contoh | Keterangan |
|---|---|---|---|
| `months` | integer | `6` | Jumlah bulan ke belakang, max 12 (default: 6) |

**Contoh request:**
```
GET /api/v1/reports/trend?months=6
GET /api/v1/reports/trend?months=12
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Tren bulanan.",
    "data": {
        "months": 6,
        "data": [
            { "month": "2023-08", "income": 12000000, "expense": 7000000, "profit": 5000000 },
            { "month": "2023-09", "income": 13000000, "expense": 8000000, "profit": 5000000 },
            { "month": "2023-10", "income": 11000000, "expense": 7500000, "profit": 3500000 },
            { "month": "2023-11", "income": 14000000, "expense": 8200000, "profit": 5800000 },
            { "month": "2023-12", "income": 13000000, "expense": 8000000, "profit": 5000000 },
            { "month": "2024-01", "income": 15000000, "expense": 8500000, "profit": 6500000 }
        ],
        "generated_at": "2024-01-15T10:00:00+07:00"
    }
}
```

---

### 6. Export (PDF / Excel)

```http
GET /api/v1/reports/export
```

> Export diproses secara **async** melalui Redis queue. Request ini akan mengembalikan `job_id` yang bisa digunakan untuk mengecek status dan mengunduh file.

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Query Parameters:**

| Parameter | Tipe | Contoh | Keterangan |
|---|---|---|---|
| `format` | string | `pdf` | **Wajib.** `pdf` atau `xlsx` |
| `period` | string | `monthly` | Sama seperti profit-loss |
| `year` | integer | `2024` | Tahun |
| `month` | integer | `1` | Bulan |

**Contoh request:**
```
GET /api/v1/reports/export?format=pdf&period=monthly&year=2024&month=1
GET /api/v1/reports/export?format=xlsx&period=yearly&year=2024
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Export dijadwalkan.",
    "data": {
        "job_id": "9d3f9a2e-1234-4abc-8def-000000000001",
        "status": "queued",
        "message": "Export sedang diproses.",
        "status_url": "http://localhost:8000/api/v1/reports/export/status/9d3f9a2e-...",
        "queued_at": "2024-01-15T10:05:00+07:00"
    }
}
```

---

### 7. Export Status (Polling)

```http
GET /api/v1/reports/export/status/{jobId}
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Response jika masih diproses:**
```json
{
    "success": true,
    "message": "Status export.",
    "data": {
        "status": "processing",
        "job_id": "9d3f9a2e-...",
        "format": "pdf",
        "queued_at": "2024-01-15T10:05:00+07:00",
        "started_at": "2024-01-15T10:05:02+07:00"
    }
}
```

**Response jika selesai:**
```json
{
    "success": true,
    "message": "Status export.",
    "data": {
        "status": "done",
        "job_id": "9d3f9a2e-...",
        "format": "pdf",
        "filename": "laporan-keuangan-warung-makan-bu-sari-monthly-20240115-100530.pdf",
        "download_url": "http://localhost:8000/storage/exports/uuid/filename.pdf?expires=...",
        "completed_at": "2024-01-15T10:05:08+07:00"
    }
}
```

> `download_url` hanya valid selama **30 menit** setelah dihasilkan.

---

## AI Insights

> Endpoint AI di-rate-limit **20 request/jam** per user karena menggunakan OpenAI API berbayar.
> Response di-cache **1 jam** per user untuk menekan biaya.

### 1. Get AI Insights

```http
GET /api/v1/ai/insights
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "AI insights berhasil dianalisis.",
    "data": {
        "period": "January 2024",
        "summary": "Bisnis Warung Makan Bu Sari menunjukkan performa yang sangat baik di bulan Januari 2024 dengan pendapatan Rp 15.000.000 dan margin laba 43.33%, meningkat 30% dibanding bulan lalu.",
        "trend_analysis": "Tren pemasukan terus naik selama 3 bulan terakhir dengan rata-rata pertumbuhan 8% per bulan. Pengeluaran bahan baku masih terkendali di kisaran 28% dari total pendapatan.",
        "health_score": 78,
        "recommendations": [
            {
                "type": "opportunity",
                "title": "Tingkatkan Penjualan Online",
                "message": "Penjualan via marketplace baru menyumbang 20% pendapatan. Pertimbangkan untuk meningkatkan anggaran promosi online karena ROI-nya 3x lebih tinggi dari promosi offline."
            },
            {
                "type": "warning",
                "title": "Biaya Listrik Meningkat",
                "message": "Pengeluaran listrik naik 25% bulan ini. Periksa apakah ada peralatan yang boros daya atau pertimbangkan penggunaan timer otomatis."
            },
            {
                "type": "tip",
                "title": "Kelola Arus Kas Lebih Baik",
                "message": "Sebaiknya sisihkan minimal 20% dari laba bersih (Rp 1.300.000) sebagai dana darurat operasional untuk mengantisipasi penurunan penjualan di bulan berikutnya."
            }
        ],
        "predicted_next_month": {
            "income": 16000000,
            "expense": 9000000,
            "profit": 7000000,
            "confidence": "medium"
        },
        "generated_at": "2024-01-15T10:10:00+07:00",
        "cached_until": "2024-01-15T11:10:00+07:00"
    }
}
```

---

### 2. Ask AI

```http
POST /api/v1/ai/ask
```

> Rate limit: **10 pertanyaan/hari** per user.

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Body:**
```json
{
    "question": "Kenapa pengeluaran saya naik drastis bulan ini?"
}
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Pertanyaan berhasil dijawab.",
    "data": {
        "question": "Kenapa pengeluaran saya naik drastis bulan ini?",
        "answer": "Berdasarkan data keuangan Anda, pengeluaran naik 6.25% (Rp 500.000) dibanding bulan lalu. Berdasarkan rincian per kategori, kenaikan terbesar berasal dari kategori Bahan Baku yang naik 18% dan Listrik & Air yang naik 25%. Kemungkinan penyebabnya adalah kenaikan harga bahan baku dari supplier atau peningkatan volume produksi. Saran: bandingkan harga dengan supplier lain dan pertimbangkan pembelian bahan baku dalam jumlah lebih besar untuk mendapat harga lebih murah.",
        "context": {
            "business_name": "Warung Makan Bu Sari",
            "current_month": "January 2024",
            "income": 15000000,
            "expense": 8500000,
            "profit": 6500000,
            "profit_margin": 43.33
        },
        "asked_at": "2024-01-15T10:15:00+07:00",
        "from_cache": false
    }
}
```

---

## Activity Log

### 1. List Activity Log

```http
GET /api/v1/activity
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Query Parameters:**

| Parameter | Tipe | Contoh | Keterangan |
|---|---|---|---|
| `event` | string | `created` | Filter by event: `created`, `updated`, `deleted` |
| `subject_type` | string | `Transaction` | Filter by model: `Transaction`, `Category`, `User` |
| `per_page` | integer | `20` | Item per halaman (max 50, default 20) |

**Contoh request:**
```
GET /api/v1/activity?event=created&subject_type=Transaction&per_page=20
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Riwayat aktivitas.",
    "data": [
        {
            "id": 1,
            "event": "created",
            "description": "Transaction created",
            "subject_type": "Transaction",
            "subject_id": "tx-uuid-001",
            "changes": {
                "attributes": {
                    "type": "income",
                    "amount": 1500000,
                    "description": "Penjualan produk harian"
                }
            },
            "ip_address": "127.0.0.1",
            "user_agent": "PostmanRuntime/7.36.0",
            "created_at": "2024-01-15T08:30:00+07:00"
        },
        {
            "id": 2,
            "event": "updated",
            "description": "Transaction updated",
            "subject_type": "Transaction",
            "subject_id": "tx-uuid-001",
            "changes": {
                "old": { "amount": 1500000 },
                "attributes": { "amount": 2000000 }
            },
            "ip_address": "127.0.0.1",
            "user_agent": "PostmanRuntime/7.36.0",
            "created_at": "2024-01-15T09:00:00+07:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 45,
        "last_page": 3,
        "has_more": true,
        "next_page_url": "http://localhost:8000/api/v1/activity?page=2",
        "prev_page_url": null
    }
}
```

---

### 2. Activity Summary

```http
GET /api/v1/activity/summary
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Query Parameters:**

| Parameter | Tipe | Contoh | Keterangan |
|---|---|---|---|
| `days` | integer | `30` | Lihat N hari ke belakang (max 90, default 30) |

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Ringkasan aktivitas.",
    "data": {
        "period": "last_30_days",
        "activities": [
            { "event": "created", "subject_type": "Transaction", "count": 25, "last_at": "2024-01-15T08:30:00+07:00" },
            { "event": "updated", "subject_type": "Transaction", "count": 8,  "last_at": "2024-01-14T15:00:00+07:00" },
            { "event": "deleted", "subject_type": "Transaction", "count": 2,  "last_at": "2024-01-10T11:00:00+07:00" },
            { "event": "created", "subject_type": "Category",    "count": 3,  "last_at": "2024-01-05T09:00:00+07:00" }
        ],
        "total": 38
    }
}
```

---

### 3. Activity Log per Resource

```http
GET /api/v1/activity/subject
```

**Headers:**
```
Accept: application/json
Authorization: Bearer 1|abc123xyz...
```

**Query Parameters:**

| Parameter | Tipe | Contoh | Keterangan |
|---|---|---|---|
| `subject_id` | uuid | `tx-uuid-001` | **Wajib.** UUID resource |
| `subject_type` | string | `Transaction` | **Wajib.** `Transaction`, `Category`, `User` |

**Contoh request:**
```
GET /api/v1/activity/subject?subject_type=Transaction&subject_id=tx-uuid-001
```

**Response `200 OK`:**
```json
{
    "success": true,
    "message": "Riwayat aktivitas resource.",
    "data": [
        {
            "id": 2,
            "event": "updated",
            "description": "Transaction updated",
            "subject_type": "Transaction",
            "subject_id": "tx-uuid-001",
            "changes": {
                "old": { "amount": 1500000 },
                "attributes": { "amount": 2000000 }
            },
            "ip_address": "127.0.0.1",
            "user_agent": "PostmanRuntime/7.36.0",
            "created_at": "2024-01-15T09:00:00+07:00"
        },
        {
            "id": 1,
            "event": "created",
            "description": "Transaction created",
            "subject_type": "Transaction",
            "subject_id": "tx-uuid-001",
            "changes": {
                "attributes": {
                    "type": "income",
                    "amount": 1500000
                }
            },
            "ip_address": "127.0.0.1",
            "user_agent": "PostmanRuntime/7.36.0",
            "created_at": "2024-01-15T08:30:00+07:00"
        }
    ]
}
```

---

## Health Check

```http
GET /api/health
```

> Tidak memerlukan autentikasi.

**Headers:**
```
Accept: application/json
```

**Response `200 OK` (semua sistem normal):**
```json
{
    "success": true,
    "message": "Semua sistem berjalan normal.",
    "data": {
        "status": "healthy",
        "service": "UMKM Financial API",
        "version": "v1",
        "timestamp": "2024-01-15T10:00:00+07:00",
        "checks": {
            "database": { "status": "ok", "latency": "2.45ms" },
            "redis":    { "status": "ok", "latency": "0.87ms" },
            "queue":    { "status": "ok", "pending_jobs": 0 },
            "disk":     { "status": "ok", "used_pct": "23.5%", "free_mb": "7812.5MB" }
        }
    }
}
```

**Response `503 Service Unavailable` (ada masalah):**
```json
{
    "success": false,
    "message": "Terdapat masalah pada sistem.",
    "data": {
        "status": "unhealthy",
        "checks": {
            "database": { "status": "fail", "error": "Connection refused" },
            "redis":    { "status": "ok",   "latency": "0.87ms" },
            "queue":    { "status": "ok",   "pending_jobs": 0 },
            "disk":     { "status": "ok",   "used_pct": "23.5%", "free_mb": "7812.5MB" }
        }
    }
}
```

---

## Error Response Format

Semua error mengembalikan format yang **konsisten**:

### 400 Bad Request
```json
{
    "success": false,
    "message": "Bad request."
}
```

### 401 Unauthorized
```json
{
    "success": false,
    "message": "Unauthenticated. Silakan login terlebih dahulu."
}
```

### 403 Forbidden
```json
{
    "success": false,
    "message": "Anda tidak memiliki akses."
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Data tidak ditemukan."
}
```

### 409 Conflict
```json
{
    "success": false,
    "message": "Kategori tidak dapat dihapus karena masih memiliki transaksi."
}
```

### 422 Unprocessable Entity (Validation Error)
```json
{
    "success": false,
    "message": "Data yang dikirim tidak valid.",
    "errors": {
        "email": ["Email sudah terdaftar."],
        "amount": ["Jumlah minimal Rp 1."],
        "date": ["Tanggal tidak boleh di masa depan."]
    }
}
```

### 429 Too Many Requests
```json
{
    "success": false,
    "message": "Terlalu banyak request. Coba lagi dalam 1 menit."
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Terjadi kesalahan pada server."
}
```

### 503 Service Unavailable
```json
{
    "success": false,
    "message": "Layanan AI sedang tidak tersedia."
}
```

---

## Rate Limiting

| Endpoint Group | Limit |
|---|---|
| `POST /auth/register`, `POST /auth/login` | 5 request/menit per IP |
| Semua endpoint authenticated | 60 request/menit per user |
| `GET /reports/export` | 5 request/jam per user |
| `GET /ai/insights`, `POST /ai/ask` | 20 request/jam per user |
| `POST /ai/ask` (daily) | 10 pertanyaan/hari per user |

---

## Developer Tools

| URL | Keterangan |
|---|---|
| `http://localhost:8000/docs/api` | Swagger UI (auto-generated) |
| `http://localhost:8000/horizon` | Queue monitoring dashboard |
| `http://localhost:8000/api/health` | System health check |

---