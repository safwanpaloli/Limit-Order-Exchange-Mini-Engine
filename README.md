# Limit Order Exchange Mini-Engine

A full-stack, real-time cryptocurrency limit order exchange application built with **Laravel**, **Vue.js 3 (Composition API)**, and **Tailwind CSS**.

## 🚀 Features Implemented

### 1. Robust Matching Engine (Backend)
- **Database Architecture**: Implements secure `users`, `assets`, `orders`, and `trades` tables.
- **Atomic Execution**: Uses database transactions and pessimistic locking (`lockForUpdate()`) to completely prevent race conditions during concurrent order placements and fills.
- **Automated Matching**: Automatically matches crossing limit orders based on strict Price/Time Priority (FIFO) rules.
- **Commission System**: A precise **1.5% commission** is dynamically calculated and correctly deducted from trade proceeds (Buyer pays asset fee, Seller pays USD fee).

### 2. Real-Time Interactions
- **Laravel Reverb (Pusher)**: Fully integrated WebSocket broadcasting for real-time exchange capabilities.
- **Private Channels**: `OrderMatched` events are dispatched on secure private user channels (`private-user.{id}`).
- **Reactive UI**: The frontend seamlessly intercepts live events and patches the UI instantly—updating wallet balances, updating the orderbook, and dynamically switching order statuses to "Completed" without requiring a browser refresh.

### 3. Modern Vue.js Frontend
- **Tech Stack**: Built with Vite, Vue 3, and Tailwind CSS.
- **Beautiful UI**: Features a custom design system with consistent `8px` (`rounded-lg`) border radii, glassmorphism elements, animated custom dropdowns (`CustomSelect`), and micro-interactions.
- **Limit Order Form**: Complete trading terminal with BTC/ETH symbol dropdowns, live volume calculations, and strict validation.
- **Orders & Wallet Overview**: A comprehensive dashboard showing fiat/crypto balances, open/filled/cancelled order history, and advanced real-time status filtering.

---

## 🛠 Setup & Installation Instructions

### 1. Clone & Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Configuration
Copy the `.env.example` file to `.env` and configure your database settings:
```bash
cp .env.example .env
php artisan key:generate
```
*Ensure you have a MySQL/MariaDB server running and update the `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in your `.env`.*

### 3. Database Migration & Seeding
Run the migrations and seed the database with a test user:
```bash
php artisan migrate --seed
```
*This will create a default test user with an initial USD balance.*

### 4. Start the Application Servers
You will need **three** separate terminal windows running simultaneously to power the full stack:

**Terminal 1 (Backend API):**
```bash
php artisan serve
```

**Terminal 2 (Frontend Vite Server):**
```bash
npm run dev
```

**Terminal 3 (Real-Time WebSockets Server):**
```bash
php artisan reverb:start
```

---

## 🧪 Testing the Flow

1. Open your browser and navigate to the frontend URL provided by Vite (usually `http://localhost:5173`).
2. Login using the test user credentials (if seeded) or register a new account.
3. Navigate to the **Trade** page.
4. Place a Limit Buy or Sell order. Note the immediate USD/Asset locking logic.
5. Open an incognito window, create a second account, and place a counter-order to cross the spread.
6. Watch as the WebSocket server instantly matches the trade, deducts the 1.5% fee, triggers a toast notification, and dynamically updates both users' balances and order statuses in real-time!

## 🔐 Security & Architecture Highlights
- **Strict Locking**: Utilizes `$query->lockForUpdate()` in `OrderRepository` during balance deduction and trade settlement to ensure mathematical accuracy under high concurrency.
- **Secure WebSockets**: Employs Laravel's `Broadcast::routes(['middleware' => ['auth:sanctum']])` to ensure trades are only ever broadcasted to the specific authenticated parties involved via `Echo.private()`.
- **Form Hardening**: Frontend validation is backed strictly by backend API validations for price, amount, and sufficient balance requirements.
