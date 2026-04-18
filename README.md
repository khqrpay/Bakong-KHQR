# KHQR Payment Gateway - PHP Integration

A simple, production-ready PHP integration for [KHQR.cc](https://khqr.cc) payment gateway — accept payments via Bakong KHQR, ABA, and other Cambodian bank apps.

---

## 🚀 Getting Started

### Step 1 — Create a KHQR.cc Account

1. Go to [https://khqr.cc](https://khqr.cc) and click **Sign Up**
2. Register with your email or login with an existing account
3. Complete your merchant profile setup

### Step 2 — Choose a Plan

1. Go to [https://khqr.cc/pricing](https://khqr.cc/pricing)
2. Select a plan that fits your needs (Free or paid)
3. Activate the plan from your dashboard

### Step 3 — Get Your API Credentials

1. Go to **[https://khqr.cc/settings](https://khqr.cc/settings)**
2. Find and copy your:
   - **Profile ID** — your unique merchant identifier
   - **Secret Key** — used for hash verification (keep this private!)

### Step 4 — Configure Your Webhook

1. In **[https://khqr.cc/settings](https://khqr.cc/settings)**, find the **Webhook** section
2. Set your **Webhook URL** to:
   ```
   https://yourdomain.com/webhook_handler.php
   ```
3. Save the settings
4. KHQR.cc will send a `POST` request to this URL every time a payment is completed

> **Note:** For local development, use [ngrok](https://ngrok.com) to expose your local server:
> ```bash
> ngrok http 8000
> ```
> Then use the ngrok URL as your webhook (e.g. `https://xxxx.ngrok-free.app/webhook_handler.php`)

---

## 📦 Installation

### 1. Clone the repository

```bash
git clone https://github.com/yourusername/khqr-payment.git
cd khqr-payment
```

### 2. Create your `.env` file

```bash
cp .env.example .env
```

### 3. Fill in your credentials

Open `.env` and set your values:

```env
KHQR_PROFILE_ID=your_profile_id_here
KHQR_SECRET_KEY=your_secret_key_here
KHQR_GATEWAY_URL=https://khqr.cc/api/payment/request
APP_BASE_URL=https://yourdomain.com
```

| Variable | Description |
|---|---|
| `KHQR_PROFILE_ID` | Your Profile ID from [khqr.cc/settings](https://khqr.cc/settings) |
| `KHQR_SECRET_KEY` | Your Secret Key from [khqr.cc/settings](https://khqr.cc/settings) |
| `KHQR_GATEWAY_URL` | Payment API endpoint (default: `https://khqr.cc/api/payment/request`) |
| `APP_BASE_URL` | Your app's public URL (used for callback redirects) |

### 4. Start the server

```bash
php -S localhost:8000
```

Open [http://localhost:8000](http://localhost:8000) in your browser.

---

## 📁 Project Structure

```
├── .env.example          # Environment template (commit this)
├── .env                  # Your credentials (DO NOT commit)
├── config.php            # Loads .env and provides env() helper
├── logger.php            # Logging utility
├── index.php             # Checkout page — generates payment URL
├── callback.php          # User redirect after payment + webhook handler
├── webhook_handler.php   # Background webhook receiver (server-to-server)
├── verify.php            # Transaction verification API helper
└── logs/                 # Log files (auto-generated)
```

---

## 🔄 Payment Flow

```
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│  User    │────▶│ index.php│────▶│ KHQR.cc  │────▶│ Bank App │
│ (Browser)│     │ Checkout │     │ Payment  │     │ (Pay)    │
└──────────┘     └──────────┘     └──────────┘     └────┬─────┘
                                                        │
                      ┌─────────────────────────────────┘
                      ▼
              ┌───────────────┐
              │   KHQR.cc     │
              │   Server      │
              └───┬───────┬───┘
                  │       │
         (POST)   │       │  (Redirect)
                  ▼       ▼
     ┌─────────────┐  ┌──────────┐
     │webhook_     │  │callback  │
     │handler.php  │  │.php      │
     │(Background) │  │(Browser) │
     └─────────────┘  └──────────┘
```

1. **User** visits `index.php` and clicks "Pay"
2. **User** is redirected to **KHQR.cc** payment page
3. **User** pays via bank app (KHQR scan / ABA / etc.)
4. **KHQR.cc** sends a background `POST` to `webhook_handler.php` (server-to-server)
5. **User's browser** is redirected to `callback.php` (success page)

---

## 🔐 Security

- All webhook payloads are verified using **SHA-256 HMAC** hash
- Hash formula: `sha256(secret + req_time + transaction_id + amount + status)`
- Payment initiation uses **SHA-1** hash for URL signing
- Credentials are stored in `.env` — never hardcoded

---

## 📝 Logs

All actions are automatically logged to `logs/` directory:

| Log File | Contains |
|---|---|
| `logs/payment_YYYY-MM-DD.log` | Checkout page events |
| `logs/webhook_YYYY-MM-DD.log` | Incoming webhook data |
| `logs/callback_YYYY-MM-DD.log` | Callback/redirect events |
| `logs/verify_YYYY-MM-DD.log` | Transaction verification results |

---

## ⚠️ Important Notes

- **Always return HTTP 200** to webhook requests, or KHQR.cc will retry 3–5 times
- Add `.env` to your `.gitignore` to keep credentials safe
- For production, use HTTPS and a proper domain (not ngrok)
- Test with small amounts (e.g. `$0.01`) before going live

---

## 📄 .gitignore

Make sure to add these to your `.gitignore`:

```gitignore
.env
logs/
```

---

## 📜 License

MIT License — use freely for your projects.
