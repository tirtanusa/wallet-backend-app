# 💰 Wallet App — REST API

A **RESTful API** for a digital wallet application built with **Laravel 13** and secured with **Laravel Sanctum**. This backend supports user authentication, wallet balance management, and fund transfers between users.

---

## 🚀 Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.3 |
| Authentication | Laravel Sanctum (Token-based) |
| Database | MySQL |
| Testing | PestPHP |
| Code Style | Laravel Pint |

---

## ✨ Features

- **Authentication** — Register, Login, and Logout with Sanctum token
- **Wallet Balance** — View current wallet balance
- **Top Up** — Add funds to own wallet
- **Transfer** — Transfer funds to another user by email
- **Transaction History** — Paginated list of all transactions
- **Transaction Detail** — View a specific transaction by reference code
- **Authorization** — Users can only view their own transactions (via Policy)
- **Race Condition Safe** — Uses database-level locking (`lockForUpdate`) and atomic `increment`/`decrement` to prevent concurrent balance issues
- **Soft Deletes** — Wallet and Transaction records use soft deletes for data integrity

---

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── WalletController.php
│   │   └── TransactionController.php
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── RegisterRequest.php
│   │   │   └── LoginRequest.php
│   │   └── Wallet/
│   │       ├── TopUpRequest.php
│   │       └── TransferRequest.php
│   └── Resources/
│       ├── WalletResources.php
│       └── TransactionResources.php
├── Models/
│   ├── User.php
│   ├── Wallet.php
│   └── Transaction.php
├── Policies/
│   └── TransactionPolicy.php
└── Services/
    └── WalletService.php
```

---

## 🗄️ Database Schema

### `users`
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| name | string | User's full name |
| email | string | Unique email address |
| password | string | Hashed password |
| timestamps | — | created_at, updated_at |

### `wallets`
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| user_id | bigint (FK) | Belongs to a user |
| balance | decimal(15,2) | Current balance, default `0` |
| currency | string(3) | Currency code, default `IDR` |
| timestamps | — | created_at, updated_at, deleted_at |

### `transactions`
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| wallet_id | bigint (FK) | Owner wallet |
| related_wallet_id | bigint (FK, nullable) | Counterpart wallet for transfers |
| type | enum | `topup`, `transfer_out`, `transfer_in` |
| amount | decimal(15,2) | Transaction amount |
| balance_before | decimal(15,2) | Balance before transaction |
| balance_after | decimal(15,2) | Balance after transaction |
| reference_code | string(50) | Unique transaction reference |
| description | text (nullable) | Optional note |
| timestamps | — | created_at, updated_at, deleted_at |

---

## 📡 API Endpoints

### Authentication

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| POST | `/api/auth/register` | Register a new user | ❌ |
| POST | `/api/auth/login` | Login and get token | ❌ |
| POST | `/api/auth/logout` | Logout (revoke token) | ✅ |

### Wallet

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/wallet` | Get current wallet balance | ✅ |
| POST | `/api/wallet/topup` | Top up wallet balance | ✅ |
| POST | `/api/wallet/transfer` | Transfer funds to another user | ✅ |

### Transactions

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/transactions` | Get paginated transaction history | ✅ |
| GET | `/api/transactions/{reference_code}` | Get transaction detail by reference code | ✅ |

> **Auth**: ✅ requires `Authorization: Bearer {token}` header

---

## 📋 Request & Response Examples

### POST `/api/auth/register`
```json
// Request
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}

// Response 201
{
  "message": "Registrasi berhasil.",
  "token": "1|abc123..."
}
```

### POST `/api/auth/login`
```json
// Request
{
  "email": "john@example.com",
  "password": "password"
}

// Response 200
{
  "message": "Login berhasil.",
  "token": "2|xyz789..."
}
```

### GET `/api/wallet`
```json
// Response 200
{
  "message": "Berhasil mengambil saldo.",
  "data": {
    "balance": "150000.00",
    "currency": "IDR"
  }
}
```

### POST `/api/wallet/topup`
```json
// Request
{
  "amount": 100000,
  "description": "Top up via bank transfer"
}

// Response 201
{
  "message": "Top up berhasil.",
  "data": {
    "reference_code": "TOP-20260624-ABCD12",
    "type": "topup",
    "amount": "100000.00",
    "balance_before": "50000.00",
    "balance_after": "150000.00",
    "description": "Top up via bank transfer",
    "created_at": "2026-06-24T09:00:00.000000Z"
  }
}
```

### POST `/api/wallet/transfer`
```json
// Request
{
  "recipient_email": "jane@example.com",
  "amount": 50000,
  "description": "Bayar makan siang"
}

// Response 201
{
  "message": "Transfer berhasil.",
  "data": {
    "reference_code": "TRF-20260624-XYZ789",
    "type": "transfer_out",
    "amount": "50000.00",
    "balance_before": "150000.00",
    "balance_after": "100000.00",
    "description": "Bayar makan siang",
    "created_at": "2026-06-24T09:05:00.000000Z"
  }
}
```

### GET `/api/transactions`
```json
// Response 200
{
  "message": "Berhasil mengambil riwayat transaksi.",
  "data": {
    "current_page": 1,
    "data": [ ... ],
    "per_page": 10,
    "total": 5
  }
}
```

---

## ⚙️ Installation & Setup

### Prerequisites
- PHP >= 8.3
- Composer
- MySQL
- Node.js & NPM

### 1. Clone the repository
```bash
git clone <repository-url>
cd wallet-app
```

### 2. Install dependencies
```bash
composer install
npm install
```

### 3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wallet_app
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Run migrations
```bash
php artisan migrate
```

### 5. Start the development server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

> **Quick setup**: You can also run `composer setup` to install all dependencies, generate the app key, and run migrations in one step.

---

## 🧪 Running Tests

```bash
php artisan test
# or
composer test
```

---

## 🔒 Security

- All protected routes require a **Sanctum Bearer Token**
- Passwords are **hashed** using Laravel's default bcrypt
- Database transactions use **`lockForUpdate`** to prevent race conditions on concurrent balance operations
- **`TransactionPolicy`** ensures users can only access their own transaction records
- Wallet balances use atomic **`increment`/`decrement`** operations for consistency

---

## 📝 Reference Code Format

Transaction reference codes follow this format:

```
{PREFIX}-{YYYYMMDD}-{RANDOM6}
```

| Prefix | Type |
|---|---|
| `TOP` | Top Up |
| `TRF` | Transfer |

**Example**: `TOP-20260624-AB12CD`, `TRF-20260624-XY78ZQ`

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
