# Artisan Roastery & Café — POS and Management System

A fully functional, medium-scale **Café Point of Sale (POS) and Enterprise Management System** built with **Laravel 11**, **PHP 8.3**, **MySQL 8.4**, **Blade**, **Tailwind CSS**, **JavaScript**, **Vite**, and **Chart.js**.

Designed and developed for university coursework, this system avoids mere mockups and implements complete end-to-end workflows: authentic MySQL transactions, row-level locking for concurrent inventory recipe deductions, idempotency guards, split payments, shift reconciliation with discrepancy auditing, live kitchen display updates via polling, customer loyalty point mechanics, and financial reporting with Chart.js visualizations.

---

## 1. System Technology Stack

- **Backend Framework:** Laravel 11 (MVC architecture, Service layer, Policies, FormRequests, Enums).
- **Runtime Environment:** PHP 8.3.16 CLI / Local Server.
- **Database Engine:** MySQL 8.4 Community Edition (InnoDB, strict foreign keys, atomic transactions).
- **Authentication:** Laravel Breeze with custom Role-Based Access Control (RBAC).
- **Frontend Architecture:** Laravel Blade Templates styled with Tailwind CSS v3.4.
- **Asset Bundling:** Vite v5 (bundled Chart.js, ES modules, CSS minification).
- **Interactive Visualizations:** Chart.js (daily sales trends, payment method breakdowns).
- **Automated Testing:** PHPUnit test suites (46 tests, 125 assertions passing).

---

## 2. Neat Folder Directory Architecture

The system is cleanly divided into `backend/` and `frontend/` directories:

```
POS_CAFE/
├── backend/
│   ├── app/
│   │   ├── Enums/                 # Backed Enums (OrderStatus, PaymentStatus, RoleType)
│   │   ├── Http/
│   │   │   ├── Controllers/       # Domain-Partitioned Controllers (Admin, Customer, Inventory, Kds, Management, Pos, Report)
│   │   │   ├── Middleware/        # CheckRole, EnsureCashierShiftOpen
│   │   │   └── Requests/          # Organized FormRequests (Admin, Inventory, Management, Pos, Supplier)
│   │   ├── Models/                # 23 Eloquent Models with strict casts, fillables & relations
│   │   ├── Policies/              # OrderPolicy, CashierShiftPolicy, UserPolicy, ExpensePolicy
│   │   └── Services/              # Core Business Logic (Audit, Pos, Payment, Shift, Inventory, Loyalty, Report)
│   ├── bootstrap/                 # Application bootstrap & middleware registration
│   ├── config/                    # Configuration files
│   ├── database/
│   │   ├── migrations/            # 11 normalized migrations (InnoDB, foreign keys, indexes)
│   │   └── seeders/               # 9 modular seeders for authentic demonstration data
│   ├── routes/                    # Web and authentication routes
│   ├── storage/                   # Storage and session logs
│   ├── tests/                     # Comprehensive feature and unit tests
│   ├── .env                       # Environment configuration (MySQL 8.4 connection)
│   └── artisan                    # Laravel CLI tool
│
└── frontend/
    ├── node_modules/              # Frontend dependencies
    ├── public/                    # Compiled assets, build manifest, favicon
    │   └── build/                 # Vite production CSS & JS bundles
    ├── resources/                 # Cleanly partitioned Blade Views, Tailwind CSS, & JavaScript
    │   ├── css/                   # Tailwind CSS stylesheets
    │   ├── js/                    # JavaScript with Chart.js integration
    │   └── views/                 # Blade templates partitioned by module (pos, kds, shifts, menu, inventory, etc.)
    ├── package.json               # Frontend package definition
    ├── tailwind.config.js         # Tailwind CSS configuration
    └── vite.config.js             # Vite asset compiler configuration
```

---

## 3. Demonstration Accounts & Roles

The system is pre-seeded with four dedicated roles. Each role has distinct dashboard layouts, navigation items, and server-side authorization:

| Role | Email | Password | Access Privileges |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@cafe.com` | `password` | Full system access: Staff, Settings, Tax, Audit Trail, Reports, POS, KDS. |
| **Manager** | `manager@cafe.com` | `password` | Menu catalog, Stock, Purchase Orders, Shift reviews, Refunds, Expenses, Reports. |
| **Cashier** | `cashier@cafe.com` | `password` | Shift opening/closing, Cash movements, POS ordering, Customers, Loyalty rewards. |
| **Barista** | `barista@cafe.com` | `password` | Kitchen Display System (KDS) order cards, preparation timers, status updates. |

---

## 4. System Modules & Workflows

### Module 1: POS & Order Management
- **Order Types:** Dine-in (with table numbers), Takeaway, and Delivery.
- **Customizations:** Sizes, milk options, syrups, extra espresso shots, and barista preparation notes.
- **Server-Side Pricing Engine:** Recalculates subtotal, percentage/fixed discounts, and tax (exclusive or inclusive) on the server to prevent client-side tampering.
- **Thermal Receipts:** Printable 80mm thermal receipt format with store details, itemized modifiers, tax breakdown, and customizable footer.
- **Cancellations & Refunds:** Role-restricted refund workflow tracking reasons, responsible managers, and inventory restock options.

### Module 2: Menu & Product Catalog
- **Categories:** Hot & Iced Coffee, Artisan Teas, Blended Beverages, Fresh Pastries.
- **Variants & Add-ons:** Granular modifiers with individual price increments.
- **Availability Toggle:** Instant out-of-stock toggle preventing unavailable items from being rung up at POS.

### Module 3: Kitchen Display System (KDS)
- **Live Polling:** Automatic order synchronization every 5 seconds (practical and robust, without external Redis/Node dependencies).
- **Progress Tracking:** Clear card indicators for `Pending` (amber), `Preparing` (indigo), and `Ready` (emerald).
- **Timers:** Real-time elapsed waiting time display highlighting priority tickets.
- **Security:** Baristas are strictly restricted from accessing financial data, refunds, or settings.

### Module 4: Cashier Shifts & Cash Reconciliation
- **Opening Float:** Cashiers must open a shift with initial cash before taking orders.
- **Cash Movements:** Drawer Cash-In and Cash-Out tracking with mandatory reasons.
- **Shift Reconciliation:** Automatic expected cash calculation (`Opening + Cash Sales + Cash In - Cash Out - Cash Refunds`).
- **Discrepancy Audit:** Calculates exact over/short discrepancies upon entering counted cash. Managers review and sign off on shifts.

### Module 5: Recipe-Based Inventory & Stock Ledger
- **Recipe Deductions:** Order completion triggers automated ingredient deductions.
- **Unit Conversions:** Converts grams (`g`) to kilograms (`kg`) and milliliters (`ml`) to liters (`l`).
- **Concurrency & Idempotency:** Row-level pessimistic locking (`lockForUpdate`) and unique idempotency keys (`order_deduct_{order_id}_...`) prevent race conditions and duplicate deductions.
- **Stock Ledger:** Complete audit trail tracking every movement (order deduction, supplier receipt, wastage, manual adjustment).

### Module 6 & 7: Customer Directory & Loyalty Program
- **Customer Profiles:** Tracks contact information, lifetime spending, and points balance.
- **Automated Points:** Configurable points-per-dollar reward rate awarded on paid purchases.
- **Redemption:** Redeem points for fixed discounts, percentage vouchers, or free drinks with balance validation.
- **Refund Reversals:** Order refunds automatically deduct previously awarded points.

### Module 8 & 9: Suppliers, Purchase Orders & Expenses
- **Suppliers:** Manage vendor catalogs and primary contact information.
- **Purchase Orders:** Track PO lifecycle (`draft` -> `ordered` -> `received`). Receiving stock automatically updates inventory quantities and unit costs.
- **Operating Expenses:** Track rent, utilities, maintenance, and supplies with manager approval workflows.

### Module 10: Promotions & Discount Engine
- **Promo Codes & Auto Deals:** Supports percentage discounts, fixed-amount coupons, and combo deals.
- **Constraints:** Minimum order spend thresholds, date validity windows, and usage counters.

### Module 11: Executive Dashboard & Reports
- **Financial Metrics:** Gross Sales, Net Sales, Total Refunds, Cost of Goods Sold (COGS), Operating Expenses, Gross Profit, and Operating Profit.
- **Visual Trends:** Chart.js daily revenue charts (last 30 days) and sales by payment method doughnut charts.
- **Data Export:** Streaming CSV export for orders and sales transactions.

### Module 12 & 13: Staff Administration, Security & System Settings
- **Role Security:** Prevents users from modifying their own roles or deactivating their own accounts.
- **Audit Trail:** Immutable activity log tracking user IDs, actions, entity references, and IP addresses.
- **Store Configuration:** Dynamic settings for café branding, currency symbol, tax percentage, tax mode, order prefixes, and receipt messages.

---

## 5. Installation & Local Setup

### Prerequisites
- PHP 8.3+ with `pdo_mysql`, `bcmath`, `ctype`, `curl`, `mbstring`, `openssl`, `tokenizer`, `xml` extensions.
- Composer 2.x.
- Node.js 18+ and npm.
- MySQL 8.0+ running locally on port 3306.

### Setup Instructions

1. **Backend Dependencies & Setup:**
   ```bash
   cd D:\pos_cafe\POS_CAFE\backend
   composer install
   ```

2. **Configure Environment (`backend/.env`):**
   ```ini
   APP_NAME="Artisan Café POS"
   APP_ENV=local
   APP_KEY=base64:...
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pos_cafe
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Initialize Database and Seed Sample Data:**
   ```bash
   cd D:\pos_cafe\POS_CAFE\backend
   php artisan migrate:fresh --seed
   ```

4. **Frontend Asset Compilation:**
   ```bash
   cd D:\pos_cafe\POS_CAFE\frontend
   npm install
   npm run build
   ```

5. **Start the Laravel Development Server:**
   ```bash
   cd D:\pos_cafe\POS_CAFE\backend
   php artisan serve
   ```
   Open your browser at `http://localhost:8000` to access the application.

---

## 6. Automated Testing Verification

The project includes thorough PHPUnit feature tests verifying core business logic:

```bash
cd D:\pos_cafe\POS_CAFE\backend
php artisan test
```

### Test Results
```
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\AuthAndRoleTest
  ✓ admin can access administrative routes
  ✓ cashier cannot access administrative routes
  ✓ barista cannot access pos or financial reports
  ✓ barista can access kitchen display system
  ✓ admin cannot change own role
  ✓ admin cannot deactivate own account

   PASS  Tests\Feature\Auth\AuthenticationTest
  ✓ login screen can be rendered
  ✓ users can authenticate using the login screen
  ✓ users can not authenticate with invalid password
  ✓ users can logout

   PASS  Tests\Feature\Auth\EmailVerificationTest
  ✓ email verification screen can be rendered
  ✓ email can be verified
  ✓ email is not verified with invalid hash

   PASS  Tests\Feature\Auth\PasswordConfirmationTest
  ✓ confirm password screen can be rendered
  ✓ password can be confirmed
  ✓ password is not confirmed with invalid password

   PASS  Tests\Feature\Auth\PasswordResetTest
  ✓ reset password link screen can be rendered
  ✓ reset password link can be requested
  ✓ reset password screen can be rendered
  ✓ password can be reset with valid token

   PASS  Tests\Feature\Auth\PasswordUpdateTest
  ✓ password can be updated
  ✓ correct password must be provided to update password

   PASS  Tests\Feature\Auth\RegistrationTest
  ✓ registration screen can be rendered
  ✓ new users can register

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response

   PASS  Tests\Feature\InventoryTest
  ✓ order deducts inventory with unit conversion
  ✓ inventory deduction is idempotent
  ✓ negative inventory is blocked when override is disabled
  ✓ manager can adjust stock for spoilage or waste

   PASS  Tests\Feature\PosAndPaymentTest
  ✓ pos service calculates server side pricing accurately
  ✓ cashier can checkout order with cash
  ✓ split payment succeeds when full amount is covered
  ✓ manager can refund order partially or fully
  ✓ refund cannot exceed total paid order amount

   PASS  Tests\Feature\ProfileTest
  ✓ profile page is displayed
  ✓ profile information can be updated
  ✓ email verification status is unchanged when the email address is unchanged
  ✓ user can delete their account
  ✓ correct password must be provided to delete account

   PASS  Tests\Feature\ShiftAndLoyaltyTest
  ✓ cashier can open shift and prevent duplicate open shifts
  ✓ cash movements update expected cash
  ✓ cashier can close shift and record discrepancy
  ✓ customer earns loyalty points on completed order
  ✓ customer can redeem loyalty points for reward
  ✓ order refund reverses awarded loyalty points

  Tests:    46 passed (125 assertions)
  Duration: 17.39s
```

---

## 7. License & Credits

Developed for University Computer Science / Software Engineering coursework.
Built with [Laravel](https://laravel.com), [Tailwind CSS](https://tailwindcss.com), and [Chart.js](https://www.chartjs.org).
