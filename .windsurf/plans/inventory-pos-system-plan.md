# Inventory POS System — Data Engineering Plan

## Project Overview

- **Purpose**: Inventory management and Point of Sale system for retail stores (medium and large), with FIFO stock tracking, multi-branch support, and automated purchase request/order generation based on sales and inventory levels
- **Scope**: No customer records — anonymous sales only
- **Target Users**: Admin, Store Owner, Store Manager, Store Staff, Store Cashier
- **Core Value**: Real-time stock visibility, FIFO costing, and a procurement cycle that reduces manual work by auto-generating purchase requests from reorder triggers

---

## Roles and User Stories

### Admin
Full system access and configuration.

- As an Admin, I can create and manage user accounts with assigned roles and branches
- As an Admin, I can assign and revoke permissions per role
- As an Admin, I can configure system-wide settings (tax rates, currencies, branches)
- As an Admin, I can access audit logs for all system activity
- As an Admin, I can view and export any report across all branches

### Store Owner
Business oversight across all branches.

- As a Store Owner, I can view financial summaries and sales reports for all branches
- As a Store Owner, I can manage product pricing and cost targets
- As a Store Owner, I can approve or reject purchase orders above a threshold
- As a Store Owner, I can view inventory valuation across all branches
- As a Store Owner, I can review supplier performance and purchase history

### Store Manager
Day-to-day branch operations and procurement oversight.

- As a Store Manager, I can view and manage inventory levels for my branch
- As a Store Manager, I can review auto-generated purchase requests and promote them to purchase orders
- As a Store Manager, I can approve manual inventory adjustments
- As a Store Manager, I can manage branch staff accounts
- As a Store Manager, I can view branch-level sales, inventory, and procurement reports
- As a Store Manager, I can add and update supplier records

### Store Staff
Inventory receiving, stocking, and stock monitoring.

- As a Store Staff member, I can receive inventory against a purchase order and create FIFO batches
- As a Store Staff member, I can create manual purchase requests for low-stock items
- As a Store Staff member, I can update product locations and batch notes
- As a Store Staff member, I can perform stock counts and submit adjustment requests
- As a Store Staff member, I can view current stock levels across all product categories

### Store Cashier
Point of Sale operations.

- As a Cashier, I can search and scan products to add them to a sale
- As a Cashier, I can apply discounts within my allowed threshold
- As a Cashier, I can accept multiple payment methods (cash, card, GCash, etc.)
- As a Cashier, I can void or cancel a sale before completion
- As a Cashier, I can print or send a receipt
- As a Cashier, I can view my daily sales summary

---

## Data Entities

| Entity | Purpose |
|---|---|
| branches | Physical store locations |
| product_categories | Hierarchical product classification |
| products | Items for sale with pricing and thresholds |
| inventory | Stock levels per product per branch |
| inventory_batches | FIFO batch records per inventory record |
| suppliers | Vendor records and contact info |
| purchase_requests | Auto or manually triggered replenishment requests |
| purchase_orders | Approved procurement orders sent to suppliers |
| purchase_order_items | Line items within a purchase order |
| sales_orders | POS transaction records (anonymous) |
| sales_items | Line items within a sale (FIFO cost allocated) |
| payments | Payment method records per sale |
| inventory_adjustments | Manual stock corrections with reason codes |

**Removed**: customers, price lists, transfers (future phases)

---

## Relationships

- **branches** → **users**: Users assigned to a branch
- **products** → **product_categories**: Categorization
- **inventory** → **products** + **branches**: One record per product per branch
- **inventory_batches** → **inventory**: FIFO batch tracking
- **purchase_requests** → **products** + **branches**: Triggered by reorder point or manually
- **purchase_orders** → **suppliers** + **branches**: Procurement order to a supplier
- **purchase_order_items** → **purchase_orders** + **products**: Line items
- **purchase_order_items** → **purchase_requests**: Optional link (PR → PO item)
- **inventory_batches** → **purchase_order_items**: Receiving links batch to PO line
- **sales_orders** → **branches** + **users**: Branch and cashier
- **sales_items** → **sales_orders** + **products** + **inventory_batches**: FIFO cost allocation
- **payments** → **sales_orders**: One or many payments per order
- **inventory_adjustments** → **inventory** + **users**: Approved corrections

---

## Schema Design

### users (extended Laravel default)

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| name | varchar(255) | NO | |
| email | varchar(255) | NO | unique |
| password | varchar(255) | NO | |
| phone | varchar(20) | YES | |
| branch_id | bigint unsigned | YES | FK → branches |
| is_active | boolean | NO | default true |
| last_login_at | timestamp | YES | |
| remember_token | varchar(100) | YES | |
| timestamps + softDeletes | | | |

### branches

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| code | varchar(20) | NO | unique |
| name | varchar(255) | NO | |
| address | text | YES | |
| city | varchar(100) | YES | |
| phone | varchar(20) | YES | |
| email | varchar(255) | YES | |
| manager_id | bigint unsigned | YES | FK → users, SET NULL |
| is_active | boolean | NO | default true |
| is_main_branch | boolean | NO | default false |
| timezone | varchar(50) | YES | default UTC |
| currency | varchar(3) | YES | default PHP |
| tax_rate | decimal(5,4) | YES | default 0.0000 |
| timestamps + softDeletes | | | |

### product_categories

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| parent_id | bigint unsigned | YES | FK → self, SET NULL |
| name | varchar(255) | NO | |
| slug | varchar(255) | NO | unique |
| sort_order | int | NO | default 0 |
| is_active | boolean | NO | default true |
| timestamps + softDeletes | | | |

### products

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| sku | varchar(100) | NO | unique |
| barcode | varchar(100) | YES | unique |
| name | varchar(255) | NO | |
| slug | varchar(255) | NO | unique |
| description | text | YES | |
| category_id | bigint unsigned | YES | FK → product_categories, SET NULL |
| brand | varchar(255) | YES | |
| unit | varchar(50) | NO | default 'piece' |
| cost_price | decimal(10,2) | NO | default 0.00 |
| selling_price | decimal(10,2) | NO | default 0.00 |
| min_price | decimal(10,2) | YES | floor for discounts |
| reorder_level | int | NO | default 0 — triggers PR |
| reorder_quantity | int | NO | default 0 — suggested order qty |
| is_active | boolean | NO | default true |
| is_taxable | boolean | NO | default true |
| is_trackable | boolean | NO | default true |
| image_urls | json | YES | |
| timestamps + softDeletes | | | |

### inventory

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| product_id | bigint unsigned | NO | FK → products, RESTRICT |
| branch_id | bigint unsigned | NO | FK → branches, RESTRICT |
| quantity_on_hand | int | NO | default 0 |
| quantity_reserved | int | NO | default 0 |
| quantity_available | int | NO | computed: on_hand - reserved |
| average_cost | decimal(10,4) | NO | default 0.0000 |
| last_count_date | date | YES | |
| last_received_date | date | YES | |
| is_active | boolean | NO | default true |
| timestamps | | | no soft delete — use is_active |

Unique: (product_id, branch_id)

### inventory_batches

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| inventory_id | bigint unsigned | NO | FK → inventory, CASCADE |
| batch_number | varchar(100) | NO | unique |
| purchase_order_item_id | bigint unsigned | YES | FK → purchase_order_items, SET NULL |
| quantity | int | NO | initial qty |
| quantity_remaining | int | NO | decremented on sale |
| unit_cost | decimal(10,4) | NO | |
| manufacture_date | date | YES | |
| expiry_date | date | YES | for perishables |
| received_date | date | NO | FIFO sort key |
| received_by | bigint unsigned | NO | FK → users, RESTRICT |
| location | varchar(100) | YES | shelf/bin |
| notes | text | YES | |
| is_active | boolean | NO | default true |
| timestamps | | | |

### suppliers

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| supplier_code | varchar(50) | NO | unique |
| name | varchar(255) | NO | |
| contact_person | varchar(255) | YES | |
| email | varchar(255) | YES | unique |
| phone | varchar(20) | YES | |
| address | text | YES | |
| city | varchar(100) | YES | |
| payment_terms | varchar(100) | YES | e.g. NET30 |
| is_active | boolean | NO | default true |
| notes | text | YES | |
| timestamps + softDeletes | | | |

### purchase_requests

Auto-generated when `quantity_on_hand ≤ reorder_level`, or created manually by Staff/Manager.

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| request_number | varchar(50) | NO | unique |
| branch_id | bigint unsigned | NO | FK → branches, RESTRICT |
| product_id | bigint unsigned | NO | FK → products, RESTRICT |
| requested_quantity | int | NO | from reorder_quantity or manual |
| status | varchar(20) | NO | pending/approved/rejected/ordered — lookup |
| trigger_type | varchar(20) | NO | auto/manual |
| notes | text | YES | |
| requested_by | bigint unsigned | YES | FK → users, SET NULL |
| reviewed_by | bigint unsigned | YES | FK → users, SET NULL |
| reviewed_at | timestamp | YES | |
| timestamps + softDeletes | | | |

### purchase_orders

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| order_number | varchar(50) | NO | unique |
| supplier_id | bigint unsigned | NO | FK → suppliers, RESTRICT |
| branch_id | bigint unsigned | NO | FK → branches, RESTRICT |
| created_by | bigint unsigned | NO | FK → users, RESTRICT |
| order_date | date | NO | |
| expected_date | date | YES | |
| status | varchar(20) | NO | draft/sent/partial/received/cancelled — lookup |
| subtotal | decimal(10,2) | NO | default 0.00 |
| tax_amount | decimal(10,2) | NO | default 0.00 |
| total_amount | decimal(10,2) | NO | default 0.00 |
| notes | text | YES | |
| cancelled_at | timestamp | YES | |
| cancelled_by | bigint unsigned | YES | FK → users, SET NULL |
| timestamps + softDeletes | | | |

### purchase_order_items

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| purchase_order_id | bigint unsigned | NO | FK → purchase_orders, CASCADE |
| product_id | bigint unsigned | NO | FK → products, RESTRICT |
| purchase_request_id | bigint unsigned | YES | FK → purchase_requests, SET NULL |
| quantity_ordered | int | NO | |
| quantity_received | int | NO | default 0 |
| unit_cost | decimal(10,4) | NO | |
| total_cost | decimal(12,2) | NO | |
| timestamps | | | |

### sales_orders

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| order_number | varchar(50) | NO | unique |
| branch_id | bigint unsigned | NO | FK → branches, RESTRICT |
| cashier_id | bigint unsigned | NO | FK → users, RESTRICT |
| order_date | date | NO | |
| order_time | time | NO | |
| status | varchar(20) | NO | pending/completed/cancelled/refunded — lookup |
| subtotal | decimal(10,2) | NO | default 0.00 |
| tax_amount | decimal(10,2) | NO | default 0.00 |
| discount_amount | decimal(10,2) | NO | default 0.00 |
| total_amount | decimal(10,2) | NO | default 0.00 |
| paid_amount | decimal(10,2) | NO | default 0.00 |
| change_amount | decimal(10,2) | NO | default 0.00 |
| payment_status | varchar(20) | NO | pending/paid/partial/refunded — lookup |
| notes | text | YES | |
| timestamps | | | no soft delete — preserve audit |

### sales_items

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| sales_order_id | bigint unsigned | NO | FK → sales_orders, CASCADE |
| product_id | bigint unsigned | NO | FK → products, RESTRICT |
| inventory_batch_id | bigint unsigned | YES | FK → inventory_batches, RESTRICT |
| quantity | int | NO | default 1 |
| unit_price | decimal(10,2) | NO | |
| unit_cost | decimal(10,4) | NO | FIFO batch cost |
| discount_amount | decimal(10,2) | NO | default 0.00 |
| tax_amount | decimal(10,2) | NO | default 0.00 |
| total_price | decimal(10,2) | NO | |
| total_cost | decimal(10,2) | NO | |
| profit | decimal(10,2) | NO | total_price - total_cost |
| timestamps | | | |

### payments

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| sales_order_id | bigint unsigned | NO | FK → sales_orders, CASCADE |
| payment_method | varchar(50) | NO | cash/card/gcash/etc — lookup |
| amount | decimal(10,2) | NO | |
| reference_number | varchar(100) | YES | card/e-wallet ref |
| processed_at | timestamp | NO | |
| timestamps | | | |

### inventory_adjustments

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| inventory_id | bigint unsigned | NO | FK → inventory, RESTRICT |
| adjusted_by | bigint unsigned | NO | FK → users, RESTRICT |
| approved_by | bigint unsigned | YES | FK → users, SET NULL |
| reason_code | varchar(50) | NO | damaged/expired/count/theft/etc — lookup |
| quantity_before | int | NO | |
| quantity_change | int | NO | positive = add, negative = remove |
| quantity_after | int | NO | |
| notes | text | YES | |
| approved_at | timestamp | YES | |
| timestamps + softDeletes | | | |

---

## PR/PO Auto-Generation Flow

```
[Inventory Check Job — runs on sale completion and on schedule]
    ↓
quantity_on_hand ≤ reorder_level?
    ↓ YES
Does an open purchase_request already exist for this product+branch?
    ↓ NO
Create purchase_request (trigger_type = 'auto', requested_quantity = reorder_quantity)
    ↓
Store Manager reviews dashboard → approves purchase_requests
    ↓
Manager groups approved PRs by supplier → creates purchase_order
    ↓
purchase_order_items linked back to purchase_requests (status → 'ordered')
    ↓
Supplier delivers → Staff receives against PO items → inventory_batch created
    ↓
inventory.quantity_on_hand updated, inventory_batches.quantity_remaining set
```

---

## Role Permissions Matrix

| Permission | Admin | Store Owner | Store Manager | Store Staff | Store Cashier |
|---|:---:|:---:|:---:|:---:|:---:|
| users.manage | ✓ | — | — | — | — |
| branches.manage | ✓ | — | — | — | — |
| products.manage | ✓ | ✓ | — | — | — |
| products.view | ✓ | ✓ | ✓ | ✓ | ✓ |
| inventory.view | ✓ | ✓ | ✓ | ✓ | — |
| inventory.receive | ✓ | — | ✓ | ✓ | — |
| inventory.adjust | ✓ | — | approve | request | — |
| suppliers.manage | ✓ | ✓ | ✓ | — | — |
| purchase_requests.create | ✓ | — | ✓ | ✓ | — |
| purchase_requests.approve | ✓ | ✓ | ✓ | — | — |
| purchase_orders.manage | ✓ | ✓ | ✓ | — | — |
| sales.process | ✓ | — | ✓ | — | ✓ |
| reports.branch | ✓ | ✓ | ✓ | — | own shift |
| reports.global | ✓ | ✓ | — | — | — |
| audit.access | ✓ | — | — | — | — |

---

## Migration Order

1. `branches` — standalone
2. `product_categories` — standalone
3. `suppliers` — standalone
4. `users` (extended) — needs branches
5. spatie permission tables — needs users
6. `products` — needs product_categories
7. `inventory` — needs products, branches
8. `inventory_batches` — needs inventory, users
9. `purchase_requests` — needs products, branches, users
10. `purchase_orders` — needs suppliers, branches, users
11. `purchase_order_items` — needs purchase_orders, products, purchase_requests
12. `inventory_batches` FK update — link purchase_order_items
13. `sales_orders` — needs branches, users
14. `sales_items` — needs sales_orders, products, inventory_batches
15. `payments` — needs sales_orders
16. `inventory_adjustments` — needs inventory, users

---

## Indexing Strategy

| Table | Index |
|---|---|
| inventory | UNIQUE (product_id, branch_id), (branch_id), (is_active) |
| inventory_batches | (inventory_id, received_date) FIFO sort, (expiry_date), (is_active) |
| purchase_requests | (branch_id, status), (product_id, status) |
| purchase_orders | (branch_id, status), (supplier_id), (order_date) |
| sales_orders | (branch_id, order_date), (cashier_id), (status) |
| sales_items | (sales_order_id), (product_id), (inventory_batch_id) |
| products | UNIQUE (sku), UNIQUE (barcode), (category_id), (is_active) |

---

## Validation Checklist

- [ ] No ENUM columns — all status/type fields use lookup tables or `varchar` with app-level validation
- [ ] All tables have `timestamps()` — inventory, sales_orders, sales_items use no `softDeletes` (audit integrity)
- [ ] All other tables have `softDeletes()`
- [ ] Cascade rules prevent orphaned records
- [ ] `quantity_available` stays consistent: enforced at service layer, not DB
- [ ] FIFO: `inventory_batches` always ordered by `received_date ASC` at query time
- [ ] No customer data stored anywhere
- [ ] PR auto-generation is idempotent — checks for existing open request before creating

---

## Out of Scope (Future Phases)

- Inter-branch inventory transfers
- Price lists (branch-specific or role-specific pricing)
- Customer loyalty / accounts receivable
- Barcode label printing
- Multi-currency per transaction
