# InventoryPro

A retail inventory management and Point of Sale (POS) system built for medium to large retail stores. Supports multi-branch operations, FIFO stock tracking, automated procurement, and a full sales workflow — all from one web application.

## Features

- **Point of Sale** — fast cashier interface for product search, discounts, cash payment, and receipt printing
- **Inventory Management** — real-time stock levels with FIFO batch costing and inventory adjustments
- **Multi-Branch Support** — manage stock, staff, and sales independently per branch
- **Procurement Cycle** — purchase requests auto-generated from reorder triggers; promote to purchase orders with supplier tracking
- **Product Catalog** — hierarchical categories, supplier linking, and product details
- **Role-Based Access** — Admin, Store Owner, Manager, Staff, and Cashier roles with granular permissions
- **User Management** — create and assign users to roles and branches

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3, Laravel 13 |
| Auth | Laravel Fortify + Spatie Permissions |
| Frontend | React 19, TypeScript 5.7 |
| Bridge | Inertia.js 3 |
| Styling | Tailwind CSS 4 |
| UI Components | Radix UI, shadcn/ui, Lucide React |
| Build | Vite 8 |
| Dev Environment | Laravel Sail (Docker) |
| Testing | Pest 4 |

## Architecture

The backend follows a layered Repository + Service pattern:

```
Controller → Request (validation) → Service → Repository → Model
```

Each domain area has a DTO, Form Request, Service class, Repository interface, and Eloquent implementation.

## Database Schema

Core tables and their purpose:

| Table | Description |
|---|---|
| `users` | System users with branch assignment and role |
| `branches` | Store locations |
| `product_categories` | Self-referential category hierarchy |
| `suppliers` | Supplier contact and address records |
| `products` | Product catalog with reorder thresholds and pricing |
| `inventories` | Per-branch stock levels |
| `inventory_batches` | FIFO cost batches linked to purchase order items |
| `inventory_adjustments` | Manual stock adjustments with approval tracking |
| `purchase_requests` | Auto or manual reorder requests |
| `purchase_orders` | Confirmed orders sent to suppliers |
| `purchase_order_items` | Line items per purchase order |
| `sales_orders` | POS transaction headers |
| `sales_items` | Line items per sale |

## Roles

| Role | Scope |
|---|---|
| Admin | Full system access, user and permission management |
| Store Owner | Cross-branch financial and inventory oversight |
| Store Manager | Branch operations, procurement approval, staff management |
| Store Staff | Inventory receiving, stock counts, purchase requests |
| Store Cashier | POS sales, void, receipts, daily summary |

## Getting Started

### Prerequisites

- Docker Desktop (for Laravel Sail)
- Node.js 20+

### Setup

```bash
# Clone the repository
git clone <repo-url>
cd inventory

# Copy env and install dependencies
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

### Development

```bash
# Start all services (server, queue, vite)
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev
```

The app will be available at `http://localhost`.

### Running Tests

```bash
./vendor/bin/sail artisan test
```

### Code Quality

```bash
# PHP formatting
./vendor/bin/sail composer lint

# TypeScript / ESLint
./vendor/bin/sail npm run lint:check

# Prettier
./vendor/bin/sail npm run format:check

# Type check
./vendor/bin/sail npm run types:check
```

## Project Structure

```
app/
  Controllers/       # HTTP controllers
  DTOs/              # Data Transfer Objects per domain
  Http/Requests/     # Form request validation
  Models/            # Eloquent models
  Repositories/      # Interfaces + Eloquent implementations
  Services/          # Business logic layer
resources/js/
  pages/             # Inertia page components (React)
    auth/            # Login, register, 2FA
    branches/        # Branch CRUD
    inventory/       # Stock management
    products/        # Product catalog
    sales-orders/    # POS sales transactions
    sales-items/     # Sale line items
    suppliers/       # Supplier management
    users/           # User management
  components/        # Shared UI components
database/
  migrations/        # All schema migrations
```

## License

MIT
