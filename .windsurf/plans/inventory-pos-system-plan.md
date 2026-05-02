# Inventory POS System - Data Engineering Plan

## Project Overview
- **Purpose**: Comprehensive inventory management system with Point of Sale (POS) capabilities supporting FIFO inventory tracking and multi-branch operations
- **Business Goal**: Streamline inventory control, sales transactions, and branch management for retail businesses
- **Target Users**: System administrators, business owners, branch managers, branch staff, cashiers, store staff
- **Success Criteria**: 
  - Real-time inventory tracking across branches
  - FIFO inventory costing and rotation
  - Complete audit trail for all transactions
  - Role-based access control with granular permissions
  - Scalable multi-branch architecture

## Functional Requirements
1. **User Management**: Role-based authentication and authorization system
2. **Branch Management**: Single or multi-branch support with inter-branch transfers
3. **Product Management**: Product catalog with categories, variants, and pricing
4. **Inventory Management**: Stock tracking with FIFO cost flow assumption
5. **Supplier Management**: Vendor relationships and purchase orders
6. **POS Operations**: Sales transactions, payments, and receipts
7. **Reporting**: Sales reports, inventory reports, and financial analytics
8. **Audit Trail**: Complete logging of all system activities

## Data Entities Identified
- **Users**: System users with role-based permissions
- **Branches**: Physical store locations
- **Products**: Items available for sale
- **Product Categories**: Hierarchical product classification
- **Inventory**: Stock levels and tracking per branch
- **Inventory Batches**: FIFO batch tracking with costs
- **Suppliers**: Vendor information and relationships
- **Purchase Orders**: Procurement transactions
- **Sales Orders**: POS sales transactions
- **Sales Items**: Line items within sales orders
- **Payments**: Payment methods and transactions
- **Customers**: Customer information for sales tracking
- **Price Lists**: Branch-specific or role-specific pricing
- **Inventory Adjustments**: Manual stock corrections
- **Transfers**: Inter-branch inventory movements

## Relationships
- **Users** → **Branches**: Users assigned to specific branches
- **Branches** → **Inventory**: Each branch maintains its own inventory
- **Products** → **Inventory**: Products have inventory records per branch
- **Products** → **Product Categories**: Hierarchical categorization
- **Inventory** → **Inventory Batches**: FIFO batch tracking
- **Suppliers** → **Purchase Orders**: Vendor procurement relationships
- **Purchase Orders** → **Inventory Batches**: Stock acquisition
- **Sales Orders** → **Sales Items**: Transaction line items
- **Sales Items** → **Products**: Product references
- **Sales Items** → **Inventory Batches**: FIFO cost allocation
- **Sales Orders** → **Payments**: Transaction settlement
- **Customers** → **Sales Orders**: Customer purchase history

## Existing Laravel Schema

### Standard Laravel Tables (to be retained)
- **users**: User authentication and management (will be extended)
- **cache**: Application caching
- **sessions**: User session management
- **jobs**: Queue job management
- **failed_jobs**: Failed job tracking
- **migrations**: Database migration tracking
- **password_reset_tokens**: Password reset functionality

### Custom Tables (to be migrated/replaced)
- Current educational system tables will be dropped and replaced with inventory POS schema

### users Table JSON Structure (Extended)
```json
{
  "table": "users",
  "columns": {
    "id": {
      "type": "bigint unsigned",
      "nullable": false,
      "auto_increment": true,
      "description": "Primary key"
    },
    "name": {
      "type": "varchar(255)",
      "nullable": false,
      "description": "User full name"
    },
    "email": {
      "type": "varchar(255)",
      "nullable": false,
      "unique": true,
      "description": "User email address"
    },
    "email_verified_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Email verification timestamp"
    },
    "password": {
      "type": "varchar(255)",
      "nullable": false,
      "description": "Hashed password"
    },
    "phone": {
      "type": "varchar(20)",
      "nullable": true,
      "description": "Phone number"
    },
    "branch_id": {
      "type": "bigint unsigned",
      "nullable": true,
      "description": "Assigned branch ID"
    },
    "is_active": {
      "type": "boolean",
      "nullable": false,
      "default": true,
      "description": "Account status"
    },
    "last_login_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Last login timestamp"
    },
    "two_factor_secret": {
      "type": "text",
      "nullable": true,
      "description": "Two-factor authentication secret"
    },
    "two_factor_recovery_codes": {
      "type": "text",
      "nullable": true,
      "description": "Two-factor recovery codes"
    },
    "two_factor_confirmed_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Two-factor confirmation timestamp"
    },
    "remember_token": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "Remember me token"
    },
    "created_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record creation timestamp"
    },
    "updated_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record update timestamp"
    },
    "deleted_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Soft delete timestamp"
    }
  },
  "indexes": {
    "primary": ["id"],
    "users_email_unique": ["email"],
    "users_branch_id_index": ["branch_id"],
    "users_is_active_index": ["is_active"]
  }
}
```

## Entity Relationships

### Users to Branches
- **Relationship Type**: Many-to-One
- **Foreign Key**: users.branch_id → branches.id
- **Cascade Rules**: ON DELETE SET NULL
- **Business Rule**: Users can be assigned to one branch or be system-wide (null branch_id)
- **Query Pattern**: Find all users in a branch, find user's assigned branch

### Products to Inventory
- **Relationship Type**: One-to-Many
- **Foreign Key**: inventory.product_id → products.id
- **Cascade Rules**: ON DELETE RESTRICT
- **Business Rule**: Products cannot be deleted if they have inventory records
- **Query Pattern**: Get inventory levels for a product across all branches

### Inventory to Inventory Batches
- **Relationship Type**: One-to-Many
- **Foreign Key**: inventory_batches.inventory_id → inventory.id
- **Cascade Rules**: ON DELETE CASCADE
- **Business Rule**: Deleting inventory removes all batch tracking
- **Query Pattern**: Get FIFO batches for inventory valuation

### Sales Items to Inventory Batches
- **Relationship Type**: Many-to-One
- **Foreign Key**: sales_items.inventory_batch_id → inventory_batches.id
- **Cascade Rules**: ON DELETE RESTRICT
- **Business Rule**: Cannot delete batches used in sales
- **Query Pattern**: Track FIFO cost allocation for sales

## Role-Based Access Control System

### Permission Package Integration
This system uses **spatie/laravel-permission** package for role-based access control. The existing migration `2026_04_03_144237_create_permission_tables.php` provides the foundation with the following tables:

- **permissions**: Granular permission definitions
- **roles**: Role definitions with team support
- **model_has_permissions**: User-permission assignments
- **model_has_roles**: User-role assignments  
- **role_has_permissions**: Role-permission assignments

### Role Definitions and Permissions

#### System Administrator
**Purpose**: Full system access and configuration
**Permissions**:
- users.* (User management - create, read, update, delete)
- roles.* (Role assignment and permission management)
- permissions.* (Permission management)
- branches.* (Branch management - create, update, delete branches)
- reports.global (Global reporting and analytics)
- audit.access (Audit log access)
- system.backup (System backup and restore)

#### Business Owner
**Purpose**: Business oversight and strategic management
**Permissions**:
- branches.view (View all branches and operations)
- reports.financial (Financial reporting and analytics)
- users.branch (User management within assigned branches)
- products.pricing (Product pricing management)
- suppliers.* (Supplier relationship management)
- transfers.approve (Inventory transfer approval)
- reports.sales (Sales performance monitoring)

#### Branch Manager
**Purpose**: Daily branch operations and staff management
**Permissions**:
- inventory.branch (Branch inventory management)
- users.branch (Staff scheduling and management within branch)
- purchase_orders.* (Purchase order creation and approval)
- reports.branch (Sales reporting and analytics - branch level)
- customers.* (Customer management)
- prices.branch (Price list management - branch-specific)
- adjustments.branch (Inventory adjustments and transfers)

#### Branch Staff
**Purpose**: Inventory handling and customer service
**Permissions**:
- products.view (Product viewing and searching)
- inventory.receive (Inventory receiving and stocking)
- customers.basic (Basic customer service)
- inventory.monitor (Stock level monitoring)
- adjustments.request (Inventory adjustments with approval)
- purchase_orders.create (Purchase order creation - draft)

#### Cashier
**Purpose**: Point of Sale operations
**Permissions**:
- sales.process (Sales transaction processing)
- payments.* (Payment handling)
- receipts.* (Receipt generation)
- customers.view (Customer information access)
- reports.daily (Daily sales reporting)
- cash.drawer (Cash drawer management)
- products.lookup (Basic inventory lookup)

#### Store Staff
**Purpose**: Store operations and customer assistance
**Permissions**:
- products.view (Product information access)
- inventory.view (Inventory level viewing)
- customers.assist (Customer assistance)
- reports.basic (Basic reporting - sales, inventory)
- prices.view (Price lookup)
- inventory.locate (Stock location finding)

### Permission Structure

#### Permission Naming Convention
Permissions follow the pattern: `{module}.{action}` where:
- **module**: System area (users, products, inventory, sales, etc.)
- **action**: Specific operation (view, create, update, delete, *, etc.)

#### Module-Based Permissions
- **users**: User management operations
- **roles**: Role management operations  
- **permissions**: Permission management operations
- **branches**: Branch management operations
- **products**: Product catalog operations
- **inventory**: Stock management operations
- **suppliers**: Vendor management operations
- **purchase_orders**: Procurement operations
- **sales**: Sales transaction operations
- **payments**: Payment processing operations
- **customers**: Customer management operations
- **reports**: Reporting and analytics operations
- **adjustments**: Inventory adjustments
- **transfers**: Inter-branch transfers
- **audit**: Audit trail access
- **system**: System configuration operations

#### Permission Actions
- **view**: Read access to module data
- **create**: Create new records
- **update**: Modify existing records
- **delete**: Remove records (soft delete)
- **manage**: Full CRUD operations
- **\***: All operations within module
- **branch**: Operations limited to assigned branch
- **global**: System-wide operations
- **approve**: Approval operations
- **process**: Transaction processing
- **basic**: Limited access
- **access**: Access to specific features

### Existing Permission Tables Structure

The system uses the standard spatie/laravel-permission schema:

#### permissions Table
```php
// Standard spatie/laravel-permission structure
Schema::create('permissions', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('guard_name');
    $table->timestamps();
    $table->unique(['name', 'guard_name']);
});
```

#### roles Table  
```php
// Standard spatie/laravel-permission structure with team support
Schema::create('roles', function (Blueprint $table) use ($teams, $columnNames) {
    $table->id();
    if ($teams || config('permission.testing')) {
        $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
        $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
    }
    $table->string('name');
    $table->string('guard_name');
    $table->timestamps();
    // Unique constraints based on team support
});
```

#### Pivot Tables
- **model_has_permissions**: Links users to permissions
- **model_has_roles**: Links users to roles  
- **role_has_permissions**: Links roles to permissions

### Implementation Notes

#### Guard Configuration
- Default guard: `web`
- API guard: `api` (if applicable)
- All permissions and roles use the same guard system

#### Team Support
- Migration includes team support for multi-tenant scenarios
- Can be enabled/disabled via `config('permission.teams')`

#### Caching
- Package includes built-in caching for permission lookups
- Cache invalidation handled automatically on permission changes

#### Integration Points
- User model must use `HasRoles` trait
- Permissions checked via `$user->can('permission.name')`
- Roles checked via `$user->hasRole('role.name')`
- Blade directives: `@can`, `@role`, `@hasrole`

### branches Table

#### Purpose
Manage physical store locations for single or multi-branch operations

#### Columns
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| code | varchar(20) | NO | | Branch code |
| name | varchar(255) | NO | | Branch name |
| address | text | YES | | Physical address |
| city | varchar(100) | YES | | City |
| state | varchar(100) | YES | | State/Province |
| postal_code | varchar(20) | YES | | Postal code |
| country | varchar(100) | YES | | Country |
| phone | varchar(20) | YES | | Phone number |
| email | varchar(255) | YES | | Email address |
| manager_id | bigint unsigned | YES | | Branch manager user ID |
| is_active | boolean | NO | true | Branch status |
| is_main_branch | boolean | NO | false | Main branch flag |
| timezone | varchar(50) | YES | UTC | Timezone |
| currency | varchar(3) | YES | USD | Currency code |
| tax_rate | decimal(5,4) | YES | 0.0000 | Default tax rate |
| operating_hours | json | YES | | Operating hours |
| created_at | timestamp | YES | | Creation timestamp |
| updated_at | timestamp | YES | | Update timestamp |
| deleted_at | timestamp | YES | | Soft delete timestamp |

#### JSON Structure
```json
{
  "table": "branches",
  "engine": "InnoDB",
  "charset": "utf8mb4",
  "collation": "utf8mb4_unicode_ci",
  "columns": {
    "id": {
      "type": "bigint unsigned",
      "nullable": false,
      "auto_increment": true,
      "description": "Primary key"
    },
    "code": {
      "type": "varchar(20)",
      "nullable": false,
      "description": "Unique branch code"
    },
    "name": {
      "type": "varchar(255)",
      "nullable": false,
      "description": "Branch name"
    },
    "address": {
      "type": "text",
      "nullable": true,
      "description": "Physical address"
    },
    "city": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "City"
    },
    "state": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "State/Province"
    },
    "postal_code": {
      "type": "varchar(20)",
      "nullable": true,
      "description": "Postal code"
    },
    "country": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "Country"
    },
    "phone": {
      "type": "varchar(20)",
      "nullable": true,
      "description": "Phone number"
    },
    "email": {
      "type": "varchar(255)",
      "nullable": true,
      "description": "Email address"
    },
    "manager_id": {
      "type": "bigint unsigned",
      "nullable": true,
      "description": "Branch manager user ID"
    },
    "is_active": {
      "type": "boolean",
      "nullable": false,
      "default": true,
      "description": "Branch operational status"
    },
    "is_main_branch": {
      "type": "boolean",
      "nullable": false,
      "default": false,
      "description": "Main branch designation"
    },
    "timezone": {
      "type": "varchar(50)",
      "nullable": true,
      "default": "UTC",
      "description": "Branch timezone"
    },
    "currency": {
      "type": "varchar(3)",
      "nullable": true,
      "default": "USD",
      "description": "Currency code"
    },
    "tax_rate": {
      "type": "decimal(5,4)",
      "nullable": true,
      "default": "0.0000",
      "description": "Default tax rate"
    },
    "operating_hours": {
      "type": "json",
      "nullable": true,
      "description": "Operating hours schedule"
    },
    "created_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record creation timestamp"
    },
    "updated_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record update timestamp"
    },
    "deleted_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Soft delete timestamp"
    }
  },
  "indexes": {
    "primary": ["id"],
    "branches_code_unique": ["code"],
    "branches_manager_id_index": ["manager_id"],
    "branches_is_active_index": ["is_active"],
    "branches_is_main_branch_index": ["is_main_branch"]
  },
  "foreign_keys": {
    "branches_manager_id_foreign": {
      "column": "manager_id",
      "references": "users.id",
      "on_delete": "SET_NULL",
      "on_update": "CASCADE"
    }
  }
}
```

#### Indexes
- **Primary**: id
- **Unique**: code
- **Regular**: manager_id, is_active, is_main_branch

#### Foreign Keys
- branches_manager_id_foreign: manager_id → users.id

#### Business Rules
- Branch codes must be unique
- Only one branch can be marked as main branch
- Deactivating a branch prevents operations but preserves data

#### Access Patterns
- **Read**: Frequent branch lookups for user assignments and operations
- **Write**: Rare branch creation/modification

## Inventory Management Schema with FIFO Support

### product_categories Table

#### Purpose
Hierarchical product categorization for organization and reporting

#### Columns
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| parent_id | bigint unsigned | YES | | Parent category ID |
| name | varchar(255) | NO | | Category name |
| slug | varchar(255) | NO | | URL-friendly slug |
| description | text | YES | | Category description |
| image_url | varchar(500) | YES | | Category image |
| sort_order | int | NO | 0 | Display order |
| is_active | boolean | NO | true | Category status |
| created_at | timestamp | YES | | Creation timestamp |
| updated_at | timestamp | YES | | Update timestamp |
| deleted_at | timestamp | YES | | Soft delete timestamp |

#### JSON Structure
```json
{
  "table": "product_categories",
  "engine": "InnoDB",
  "charset": "utf8mb4",
  "collation": "utf8mb4_unicode_ci",
  "columns": {
    "id": {
      "type": "bigint unsigned",
      "nullable": false,
      "auto_increment": true,
      "description": "Primary key"
    },
    "parent_id": {
      "type": "bigint unsigned",
      "nullable": true,
      "description": "Parent category ID for hierarchy"
    },
    "name": {
      "type": "varchar(255)",
      "nullable": false,
      "description": "Category name"
    },
    "slug": {
      "type": "varchar(255)",
      "nullable": false,
      "description": "URL-friendly slug"
    },
    "description": {
      "type": "text",
      "nullable": true,
      "description": "Category description"
    },
    "image_url": {
      "type": "varchar(500)",
      "nullable": true,
      "description": "Category image URL"
    },
    "sort_order": {
      "type": "int",
      "nullable": false,
      "default": 0,
      "description": "Display order"
    },
    "is_active": {
      "type": "boolean",
      "nullable": false,
      "default": true,
      "description": "Category status"
    },
    "created_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record creation timestamp"
    },
    "updated_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record update timestamp"
    },
    "deleted_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Soft delete timestamp"
    }
  },
  "indexes": {
    "primary": ["id"],
    "product_categories_slug_unique": ["slug"],
    "product_categories_parent_id_index": ["parent_id"],
    "product_categories_is_active_index": ["is_active"],
    "product_categories_sort_order_index": ["sort_order"]
  },
  "foreign_keys": {
    "product_categories_parent_id_foreign": {
      "column": "parent_id",
      "references": "product_categories.id",
      "on_delete": "SET_NULL",
      "on_update": "CASCADE"
    }
  }
}
```

#### Indexes
- **Primary**: id
- **Unique**: slug
- **Regular**: parent_id, is_active, sort_order

#### Foreign Keys
- product_categories_parent_id_foreign: parent_id → product_categories.id

#### Business Rules
- Category slugs must be unique
- Categories can have unlimited nesting levels
- Cannot delete category with assigned products

#### Access Patterns
- **Read**: Frequent category browsing and product filtering
- **Write**: Rare category creation/modification

### products Table

#### Purpose
Core product information and attributes

#### Columns
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| sku | varchar(100) | NO | | Product SKU |
| barcode | varchar(100) | YES | | Barcode/EAN |
| name | varchar(255) | NO | | Product name |
| slug | varchar(255) | NO | | URL-friendly slug |
| description | text | YES | | Product description |
| short_description | text | YES | | Short description |
| category_id | bigint unsigned | YES | | Primary category |
| brand | varchar(255) | YES | | Brand name |
| model | varchar(255) | YES | | Model number |
| unit | varchar(50) | NO | piece | Unit of measure |
| weight | decimal(8,3) | YES | | Weight in kg |
| dimensions | varchar(100) | YES | | Dimensions (LxWxH) |
| cost_price | decimal(10,2) | NO | 0.00 | Standard cost |
| selling_price | decimal(10,2) | NO | 0.00 | Standard selling price |
| min_price | decimal(10,2) | YES | | Minimum selling price |
| max_price | decimal(10,2) | YES | | Maximum selling price |
| reorder_level | int | NO | 0 | Reorder point |
| reorder_quantity | int | NO | 0 | Reorder quantity |
| is_active | boolean | NO | true | Product status |
| is_taxable | boolean | NO | true | Tax applicability |
| is_trackable | boolean | NO | true | Inventory tracking |
| is_sellable | boolean | NO | true | Can be sold |
| image_urls | json | YES | | Product images |
| attributes | json | YES | | Product attributes |
| created_at | timestamp | YES | | Creation timestamp |
| updated_at | timestamp | YES | | Update timestamp |
| deleted_at | timestamp | YES | | Soft delete timestamp |

#### JSON Structure
```json
{
  "table": "products",
  "engine": "InnoDB",
  "charset": "utf8mb4",
  "collation": "utf8mb4_unicode_ci",
  "columns": {
    "id": {
      "type": "bigint unsigned",
      "nullable": false,
      "auto_increment": true,
      "description": "Primary key"
    },
    "sku": {
      "type": "varchar(100)",
      "nullable": false,
      "description": "Product SKU"
    },
    "barcode": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "Barcode/EAN"
    },
    "name": {
      "type": "varchar(255)",
      "nullable": false,
      "description": "Product name"
    },
    "slug": {
      "type": "varchar(255)",
      "nullable": false,
      "description": "URL-friendly slug"
    },
    "description": {
      "type": "text",
      "nullable": true,
      "description": "Product description"
    },
    "short_description": {
      "type": "text",
      "nullable": true,
      "description": "Short description"
    },
    "category_id": {
      "type": "bigint unsigned",
      "nullable": true,
      "description": "Primary category ID"
    },
    "brand": {
      "type": "varchar(255)",
      "nullable": true,
      "description": "Brand name"
    },
    "model": {
      "type": "varchar(255)",
      "nullable": true,
      "description": "Model number"
    },
    "unit": {
      "type": "varchar(50)",
      "nullable": false,
      "default": "piece",
      "description": "Unit of measure"
    },
    "weight": {
      "type": "decimal(8,3)",
      "nullable": true,
      "description": "Weight in kg"
    },
    "dimensions": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "Dimensions (LxWxH)"
    },
    "cost_price": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Standard cost price"
    },
    "selling_price": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Standard selling price"
    },
    "min_price": {
      "type": "decimal(10,2)",
      "nullable": true,
      "description": "Minimum selling price"
    },
    "max_price": {
      "type": "decimal(10,2)",
      "nullable": true,
      "description": "Maximum selling price"
    },
    "reorder_level": {
      "type": "int",
      "nullable": false,
      "default": 0,
      "description": "Reorder point"
    },
    "reorder_quantity": {
      "type": "int",
      "nullable": false,
      "default": 0,
      "description": "Reorder quantity"
    },
    "is_active": {
      "type": "boolean",
      "nullable": false,
      "default": true,
      "description": "Product status"
    },
    "is_taxable": {
      "type": "boolean",
      "nullable": false,
      "default": true,
      "description": "Tax applicability"
    },
    "is_trackable": {
      "type": "boolean",
      "nullable": false,
      "default": true,
      "description": "Inventory tracking"
    },
    "is_sellable": {
      "type": "boolean",
      "nullable": false,
      "default": true,
      "description": "Can be sold"
    },
    "image_urls": {
      "type": "json",
      "nullable": true,
      "description": "Product image URLs"
    },
    "attributes": {
      "type": "json",
      "nullable": true,
      "description": "Product attributes"
    },
    "created_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record creation timestamp"
    },
    "updated_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record update timestamp"
    },
    "deleted_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Soft delete timestamp"
    }
  },
  "indexes": {
    "primary": ["id"],
    "products_sku_unique": ["sku"],
    "products_barcode_unique": ["barcode"],
    "products_slug_unique": ["slug"],
    "products_category_id_index": ["category_id"],
    "products_is_active_index": ["is_active"],
    "products_is_sellable_index": ["is_sellable"]
  },
  "foreign_keys": {
    "products_category_id_foreign": {
      "column": "category_id",
      "references": "product_categories.id",
      "on_delete": "SET_NULL",
      "on_update": "CASCADE"
    }
  }
}
```

#### Indexes
- **Primary**: id
- **Unique**: sku, barcode, slug
- **Regular**: category_id, is_active, is_sellable

#### Foreign Keys
- products_category_id_foreign: category_id → product_categories.id

#### Business Rules
- SKUs must be unique
- Barcodes must be unique if provided
- Cannot delete products with inventory records
- Selling price cannot be less than min_price if set

#### Access Patterns
- **Read**: Very frequent product lookups, searches, and catalog browsing
- **Write**: Moderate product creation and updates

### inventory Table

#### Purpose
Track stock levels and inventory status per branch

#### Columns
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| product_id | bigint unsigned | NO | | Product ID |
| branch_id | bigint unsigned | NO | | Branch ID |
| quantity_on_hand | int | NO | 0 | Current stock quantity |
| quantity_reserved | int | NO | 0 | Reserved quantity |
| quantity_available | int | NO | 0 | Available for sale |
| reorder_point | int | NO | 0 | Reorder trigger level |
| max_stock | int | YES | | Maximum stock level |
| min_stock | int | YES | | Minimum stock level |
| average_cost | decimal(10,4) | NO | 0.0000 | Weighted average cost |
| total_cost | decimal(12,2) | NO | 0.00 | Total inventory value |
| last_count_date | date | YES | | Last physical count |
| last_received_date | date | YES | | Last inventory receipt |
| is_active | boolean | NO | true | Inventory tracking status |
| created_at | timestamp | YES | | Creation timestamp |
| updated_at | timestamp | YES | | Update timestamp |

#### JSON Structure
```json
{
  "table": "inventory",
  "engine": "InnoDB",
  "charset": "utf8mb4",
  "collation": "utf8mb4_unicode_ci",
  "columns": {
    "id": {
      "type": "bigint unsigned",
      "nullable": false,
      "auto_increment": true,
      "description": "Primary key"
    },
    "product_id": {
      "type": "bigint unsigned",
      "nullable": false,
      "description": "Product ID"
    },
    "branch_id": {
      "type": "bigint unsigned",
      "nullable": false,
      "description": "Branch ID"
    },
    "quantity_on_hand": {
      "type": "int",
      "nullable": false,
      "default": 0,
      "description": "Current stock quantity"
    },
    "quantity_reserved": {
      "type": "int",
      "nullable": false,
      "default": 0,
      "description": "Reserved quantity"
    },
    "quantity_available": {
      "type": "int",
      "nullable": false,
      "default": 0,
      "description": "Available for sale"
    },
    "reorder_point": {
      "type": "int",
      "nullable": false,
      "default": 0,
      "description": "Reorder trigger level"
    },
    "max_stock": {
      "type": "int",
      "nullable": true,
      "description": "Maximum stock level"
    },
    "min_stock": {
      "type": "int",
      "nullable": true,
      "description": "Minimum stock level"
    },
    "average_cost": {
      "type": "decimal(10,4)",
      "nullable": false,
      "default": "0.0000",
      "description": "Weighted average cost"
    },
    "total_cost": {
      "type": "decimal(12,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Total inventory value"
    },
    "last_count_date": {
      "type": "date",
      "nullable": true,
      "description": "Last physical count date"
    },
    "last_received_date": {
      "type": "date",
      "nullable": true,
      "description": "Last inventory receipt date"
    },
    "is_active": {
      "type": "boolean",
      "nullable": false,
      "default": true,
      "description": "Inventory tracking status"
    },
    "created_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record creation timestamp"
    },
    "updated_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record update timestamp"
    }
  },
  "indexes": {
    "primary": ["id"],
    "inventory_product_branch_unique": ["product_id", "branch_id"],
    "inventory_branch_id_index": ["branch_id"],
    "inventory_reorder_point_index": ["reorder_point"],
    "inventory_is_active_index": ["is_active"]
  },
  "foreign_keys": {
    "inventory_product_id_foreign": {
      "column": "product_id",
      "references": "products.id",
      "on_delete": "CASCADE",
      "on_update": "CASCADE"
    },
    "inventory_branch_id_foreign": {
      "column": "branch_id",
      "references": "branches.id",
      "on_delete": "CASCADE",
      "on_update": "CASCADE"
    }
  }
}
```

#### Indexes
- **Primary**: id
- **Unique**: product_id, branch_id
- **Regular**: branch_id, reorder_point, is_active

#### Foreign Keys
- inventory_product_id_foreign: product_id → products.id
- inventory_branch_id_foreign: branch_id → branches.id

#### Business Rules
- Each product can have only one inventory record per branch
- quantity_available = quantity_on_hand - quantity_reserved
- Cannot have negative quantities
- Average cost recalculated on each batch receipt

#### Access Patterns
- **Read**: Very frequent stock level checks and availability queries
- **Write**: Frequent updates on sales, receipts, and adjustments

### inventory_batches Table

#### Purpose
FIFO batch tracking for accurate cost allocation and inventory rotation

#### Columns
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| inventory_id | bigint unsigned | NO | | Parent inventory record |
| batch_number | varchar(100) | NO | | Batch/lot number |
| quantity | int | NO | 0 | Initial batch quantity |
| quantity_remaining | int | NO | 0 | Remaining quantity |
| unit_cost | decimal(10,4) | NO | 0.0000 | Unit cost |
| total_cost | decimal(12,2) | NO | 0.00 | Total batch cost |
| manufacture_date | date | YES | | Manufacture date |
| expiry_date | date | YES | | Expiry date |
| supplier_id | bigint unsigned | YES | | Supplier reference |
| purchase_order_id | bigint unsigned | YES | | Origin purchase order |
| received_date | date | NO | | Date received |
| received_by | bigint unsigned | NO | | Received by user |
| location | varchar(100) | YES | | Storage location |
| notes | text | YES | | Batch notes |
| is_active | boolean | NO | true | Batch status |
| created_at | timestamp | YES | | Creation timestamp |
| updated_at | timestamp | YES | | Update timestamp |

#### JSON Structure
```json
{
  "table": "inventory_batches",
  "engine": "InnoDB",
  "charset": "utf8mb4",
  "collation": "utf8mb4_unicode_ci",
  "columns": {
    "id": {
      "type": "bigint unsigned",
      "nullable": false,
      "auto_increment": true,
      "description": "Primary key"
    },
    "inventory_id": {
      "type": "bigint unsigned",
      "nullable": false,
      "description": "Parent inventory record"
    },
    "batch_number": {
      "type": "varchar(100)",
      "nullable": false,
      "description": "Batch/lot number"
    },
    "quantity": {
      "type": "int",
      "nullable": false,
      "default": 0,
      "description": "Initial batch quantity"
    },
    "quantity_remaining": {
      "type": "int",
      "nullable": false,
      "default": 0,
      "description": "Remaining quantity"
    },
    "unit_cost": {
      "type": "decimal(10,4)",
      "nullable": false,
      "default": "0.0000",
      "description": "Unit cost"
    },
    "total_cost": {
      "type": "decimal(12,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Total batch cost"
    },
    "manufacture_date": {
      "type": "date",
      "nullable": true,
      "description": "Manufacture date"
    },
    "expiry_date": {
      "type": "date",
      "nullable": true,
      "description": "Expiry date"
    },
    "supplier_id": {
      "type": "bigint unsigned",
      "nullable": true,
      "description": "Supplier reference"
    },
    "purchase_order_id": {
      "type": "bigint unsigned",
      "nullable": true,
      "description": "Origin purchase order"
    },
    "received_date": {
      "type": "date",
      "nullable": false,
      "description": "Date received"
    },
    "received_by": {
      "type": "bigint unsigned",
      "nullable": false,
      "description": "Received by user"
    },
    "location": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "Storage location"
    },
    "notes": {
      "type": "text",
      "nullable": true,
      "description": "Batch notes"
    },
    "is_active": {
      "type": "boolean",
      "nullable": false,
      "default": true,
      "description": "Batch status"
    },
    "created_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record creation timestamp"
    },
    "updated_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record update timestamp"
    }
  },
  "indexes": {
    "primary": ["id"],
    "inventory_batches_batch_number_unique": ["batch_number"],
    "inventory_batches_inventory_id_index": ["inventory_id"],
    "inventory_batches_received_date_index": ["received_date"],
    "inventory_batches_expiry_date_index": ["expiry_date"],
    "inventory_batches_supplier_id_index": ["supplier_id"],
    "inventory_batches_is_active_index": ["is_active"]
  },
  "foreign_keys": {
    "inventory_batches_inventory_id_foreign": {
      "column": "inventory_id",
      "references": "inventory.id",
      "on_delete": "CASCADE",
      "on_update": "CASCADE"
    },
    "inventory_batches_supplier_id_foreign": {
      "column": "supplier_id",
      "references": "suppliers.id",
      "on_delete": "SET_NULL",
      "on_update": "CASCADE"
    },
    "inventory_batches_purchase_order_id_foreign": {
      "column": "purchase_order_id",
      "references": "purchase_orders.id",
      "on_delete": "SET_NULL",
      "on_update": "CASCADE"
    },
    "inventory_batches_received_by_foreign": {
      "column": "received_by",
      "references": "users.id",
      "on_delete": "RESTRICT",
      "on_update": "CASCADE"
    }
  }
}
```

#### Indexes
- **Primary**: id
- **Unique**: batch_number
- **Regular**: inventory_id, received_date, expiry_date, supplier_id, is_active

#### Foreign Keys
- inventory_batches_inventory_id_foreign: inventory_id → inventory.id
- inventory_batches_supplier_id_foreign: supplier_id → suppliers.id
- inventory_batches_purchase_order_id_foreign: purchase_order_id → purchase_orders.id
- inventory_batches_received_by_foreign: received_by → users.id

#### Business Rules
- Batch numbers must be unique
- FIFO: Always consume oldest batches first
- Cannot delete batches with remaining quantity
- Expiry tracking for perishable goods

#### Access Patterns
- **Read**: Frequent FIFO selection for sales and cost calculations
- **Write**: Batch creation on receipt, updates on sales

## POS Transaction Schema

### customers Table

#### Purpose
Customer information for sales tracking and loyalty programs

#### Columns
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| customer_code | varchar(50) | NO | | Customer code |
| name | varchar(255) | NO | | Customer name |
| email | varchar(255) | YES | | Email address |
| phone | varchar(20) | YES | | Phone number |
| address | text | YES | | Address |
| city | varchar(100) | YES | | City |
| state | varchar(100) | YES | | State/Province |
| postal_code | varchar(20) | YES | | Postal code |
| country | varchar(100) | YES | | Country |
| tax_id | varchar(50) | YES | | Tax ID number |
| credit_limit | decimal(10,2) | YES | 0.00 | Credit limit |
| current_balance | decimal(10,2) | NO | 0.00 | Current balance |
| loyalty_points | int | NO | 0 | Loyalty points |
| is_active | boolean | NO | true | Customer status |
| is_tax_exempt | boolean | NO | false | Tax exemption status |
| notes | text | YES | | Customer notes |
| created_at | timestamp | YES | | Creation timestamp |
| updated_at | timestamp | YES | | Update timestamp |
| deleted_at | timestamp | YES | | Soft delete timestamp |

#### JSON Structure
```json
{
  "table": "customers",
  "engine": "InnoDB",
  "charset": "utf8mb4",
  "collation": "utf8mb4_unicode_ci",
  "columns": {
    "id": {
      "type": "bigint unsigned",
      "nullable": false,
      "auto_increment": true,
      "description": "Primary key"
    },
    "customer_code": {
      "type": "varchar(50)",
      "nullable": false,
      "description": "Unique customer code"
    },
    "name": {
      "type": "varchar(255)",
      "nullable": false,
      "description": "Customer name"
    },
    "email": {
      "type": "varchar(255)",
      "nullable": true,
      "description": "Email address"
    },
    "phone": {
      "type": "varchar(20)",
      "nullable": true,
      "description": "Phone number"
    },
    "address": {
      "type": "text",
      "nullable": true,
      "description": "Address"
    },
    "city": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "City"
    },
    "state": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "State/Province"
    },
    "postal_code": {
      "type": "varchar(20)",
      "nullable": true,
      "description": "Postal code"
    },
    "country": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "Country"
    },
    "tax_id": {
      "type": "varchar(50)",
      "nullable": true,
      "description": "Tax ID number"
    },
    "credit_limit": {
      "type": "decimal(10,2)",
      "nullable": true,
      "default": "0.00",
      "description": "Credit limit"
    },
    "current_balance": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Current balance"
    },
    "loyalty_points": {
      "type": "int",
      "nullable": false,
      "default": 0,
      "description": "Loyalty points"
    },
    "is_active": {
      "type": "boolean",
      "nullable": false,
      "default": true,
      "description": "Customer status"
    },
    "is_tax_exempt": {
      "type": "boolean",
      "nullable": false,
      "default": false,
      "description": "Tax exemption status"
    },
    "notes": {
      "type": "text",
      "nullable": true,
      "description": "Customer notes"
    },
    "created_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record creation timestamp"
    },
    "updated_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record update timestamp"
    },
    "deleted_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Soft delete timestamp"
    }
  },
  "indexes": {
    "primary": ["id"],
    "customers_customer_code_unique": ["customer_code"],
    "customers_email_unique": ["email"],
    "customers_phone_unique": ["phone"],
    "customers_is_active_index": ["is_active"]
  }
}
```

#### Indexes
- **Primary**: id
- **Unique**: customer_code, email, phone
- **Regular**: is_active

#### Business Rules
- Customer codes must be unique
- Email and phone must be unique if provided
- Credit limit cannot be exceeded for credit sales

#### Access Patterns
- **Read**: Frequent customer lookups during sales
- **Write**: Moderate customer creation and updates

### sales_orders Table

#### Purpose
Point of Sale transaction records

#### Columns
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| order_number | varchar(50) | NO | | Order number |
| branch_id | bigint unsigned | NO | | Branch ID |
| customer_id | bigint unsigned | YES | | Customer ID |
| user_id | bigint unsigned | NO | | Salesperson ID |
| order_date | date | NO | | Order date |
| order_time | time | NO | | Order time |
| status | enum('pending','completed','cancelled','refunded') | NO | pending | Order status |
| subtotal | decimal(10,2) | NO | 0.00 | Subtotal |
| tax_amount | decimal(10,2) | NO | 0.00 | Tax amount |
| discount_amount | decimal(10,2) | NO | 0.00 | Discount amount |
| total_amount | decimal(10,2) | NO | 0.00 | Total amount |
| paid_amount | decimal(10,2) | NO | 0.00 | Amount paid |
| change_amount | decimal(10,2) | NO | 0.00 | Change amount |
| payment_method | varchar(50) | YES | | Payment method |
| payment_status | enum('pending','paid','partial','refunded') | NO | pending | Payment status |
| notes | text | YES | | Order notes |
| cancelled_at | timestamp | YES | | Cancellation timestamp |
| cancelled_by | bigint unsigned | YES | | Cancelled by user |
| refunded_at | timestamp | YES | | Refund timestamp |
| refunded_by | bigint unsigned | YES | | Refunded by user |
| created_at | timestamp | YES | | Creation timestamp |
| updated_at | timestamp | YES | | Update timestamp |

#### JSON Structure
```json
{
  "table": "sales_orders",
  "engine": "InnoDB",
  "charset": "utf8mb4",
  "collation": "utf8mb4_unicode_ci",
  "columns": {
    "id": {
      "type": "bigint unsigned",
      "nullable": false,
      "auto_increment": true,
      "description": "Primary key"
    },
    "order_number": {
      "type": "varchar(50)",
      "nullable": false,
      "description": "Unique order number"
    },
    "branch_id": {
      "type": "bigint unsigned",
      "nullable": false,
      "description": "Branch ID"
    },
    "customer_id": {
      "type": "bigint unsigned",
      "nullable": true,
      "description": "Customer ID"
    },
    "user_id": {
      "type": "bigint unsigned",
      "nullable": false,
      "description": "Salesperson ID"
    },
    "order_date": {
      "type": "date",
      "nullable": false,
      "description": "Order date"
    },
    "order_time": {
      "type": "time",
      "nullable": false,
      "description": "Order time"
    },
    "status": {
      "type": "enum('pending','completed','cancelled','refunded')",
      "nullable": false,
      "default": "pending",
      "description": "Order status"
    },
    "subtotal": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Subtotal"
    },
    "tax_amount": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Tax amount"
    },
    "discount_amount": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Discount amount"
    },
    "total_amount": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Total amount"
    },
    "paid_amount": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Amount paid"
    },
    "change_amount": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Change amount"
    },
    "payment_method": {
      "type": "varchar(50)",
      "nullable": true,
      "description": "Payment method"
    },
    "payment_status": {
      "type": "enum('pending','paid','partial','refunded')",
      "nullable": false,
      "default": "pending",
      "description": "Payment status"
    },
    "notes": {
      "type": "text",
      "nullable": true,
      "description": "Order notes"
    },
    "cancelled_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Cancellation timestamp"
    },
    "cancelled_by": {
      "type": "bigint unsigned",
      "nullable": true,
      "description": "Cancelled by user"
    },
    "refunded_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Refund timestamp"
    },
    "refunded_by": {
      "type": "bigint unsigned",
      "nullable": true,
      "description": "Refunded by user"
    },
    "created_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record creation timestamp"
    },
    "updated_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record update timestamp"
    }
  },
  "indexes": {
    "primary": ["id"],
    "sales_orders_order_number_unique": ["order_number"],
    "sales_orders_branch_id_index": ["branch_id"],
    "sales_orders_customer_id_index": ["customer_id"],
    "sales_orders_user_id_index": ["user_id"],
    "sales_orders_order_date_index": ["order_date"],
    "sales_orders_status_index": ["status"],
    "sales_orders_payment_status_index": ["payment_status"]
  },
  "foreign_keys": {
    "sales_orders_branch_id_foreign": {
      "column": "branch_id",
      "references": "branches.id",
      "on_delete": "RESTRICT",
      "on_update": "CASCADE"
    },
    "sales_orders_customer_id_foreign": {
      "column": "customer_id",
      "references": "customers.id",
      "on_delete": "SET_NULL",
      "on_update": "CASCADE"
    },
    "sales_orders_user_id_foreign": {
      "column": "user_id",
      "references": "users.id",
      "on_delete": "RESTRICT",
      "on_update": "CASCADE"
    },
    "sales_orders_cancelled_by_foreign": {
      "column": "cancelled_by",
      "references": "users.id",
      "on_delete": "SET_NULL",
      "on_update": "CASCADE"
    },
    "sales_orders_refunded_by_foreign": {
      "column": "refunded_by",
      "references": "users.id",
      "on_delete": "SET_NULL",
      "on_update": "CASCADE"
    }
  }
}
```

#### Indexes
- **Primary**: id
- **Unique**: order_number
- **Regular**: branch_id, customer_id, user_id, order_date, status, payment_status

#### Foreign Keys
- sales_orders_branch_id_foreign: branch_id → branches.id
- sales_orders_customer_id_foreign: customer_id → customers.id
- sales_orders_user_id_foreign: user_id → users.id
- sales_orders_cancelled_by_foreign: cancelled_by → users.id
- sales_orders_refunded_by_foreign: refunded_by → users.id

#### Business Rules
- Order numbers must be unique
- Cannot delete completed orders
- Financial totals must balance
- Payment status must align with paid amounts

#### Access Patterns
- **Read**: Very frequent order lookups and reporting
- **Write**: High volume order creation and updates

### sales_items Table

#### Purpose
Line items within sales orders with FIFO cost tracking

#### Columns
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| sales_order_id | bigint unsigned | NO | | Sales order ID |
| product_id | bigint unsigned | NO | | Product ID |
| inventory_batch_id | bigint unsigned | YES | | FIFO batch reference |
| quantity | int | NO | 1 | Quantity sold |
| unit_price | decimal(10,2) | NO | 0.00 | Selling price |
| unit_cost | decimal(10,4) | NO | 0.0000 | Cost price |
| total_price | decimal(10,2) | NO | 0.00 | Total price |
| total_cost | decimal(10,2) | NO | 0.00 | Total cost |
| discount_percent | decimal(5,2) | NO | 0.00 | Discount percentage |
| discount_amount | decimal(10,2) | NO | 0.00 | Discount amount |
| tax_rate | decimal(5,4) | NO | 0.0000 | Tax rate |
| tax_amount | decimal(10,2) | NO | 0.00 | Tax amount |
| profit | decimal(10,2) | NO | 0.00 | Profit amount |
| created_at | timestamp | YES | | Creation timestamp |
| updated_at | timestamp | YES | | Update timestamp |

#### JSON Structure
```json
{
  "table": "sales_items",
  "engine": "InnoDB",
  "charset": "utf8mb4",
  "collation": "utf8mb4_unicode_ci",
  "columns": {
    "id": {
      "type": "bigint unsigned",
      "nullable": false,
      "auto_increment": true,
      "description": "Primary key"
    },
    "sales_order_id": {
      "type": "bigint unsigned",
      "nullable": false,
      "description": "Sales order ID"
    },
    "product_id": {
      "type": "bigint unsigned",
      "nullable": false,
      "description": "Product ID"
    },
    "inventory_batch_id": {
      "type": "bigint unsigned",
      "nullable": true,
      "description": "FIFO batch reference"
    },
    "quantity": {
      "type": "int",
      "nullable": false,
      "default": 1,
      "description": "Quantity sold"
    },
    "unit_price": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Selling price"
    },
    "unit_cost": {
      "type": "decimal(10,4)",
      "nullable": false,
      "default": "0.0000",
      "description": "Cost price"
    },
    "total_price": {
      "type": "decimal(10,2)",
      "nullable": false",
      "default": "0.00",
      "description": "Total price"
    },
    "total_cost": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Total cost"
    },
    "discount_percent": {
      "type": "decimal(5,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Discount percentage"
    },
    "discount_amount": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Discount amount"
    },
    "tax_rate": {
      "type": "decimal(5,4)",
      "nullable": false,
      "default": "0.0000",
      "description": "Tax rate"
    },
    "tax_amount": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Tax amount"
    },
    "profit": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Profit amount"
    },
    "created_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record creation timestamp"
    },
    "updated_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record update timestamp"
    }
  },
  "indexes": {
    "primary": ["id"],
    "sales_items_sales_order_id_index": ["sales_order_id"],
    "sales_items_product_id_index": ["product_id"],
    "sales_items_inventory_batch_id_index": ["inventory_batch_id"]
  },
  "foreign_keys": {
    "sales_items_sales_order_id_foreign": {
      "column": "sales_order_id",
      "references": "sales_orders.id",
      "on_delete": "CASCADE",
      "on_update": "CASCADE"
    },
    "sales_items_product_id_foreign": {
      "column": "product_id",
      "references": "products.id",
      "on_delete": "RESTRICT",
      "on_update": "CASCADE"
    },
    "sales_items_inventory_batch_id_foreign": {
      "column": "inventory_batch_id",
      "references": "inventory_batches.id",
      "on_delete": "RESTRICT",
      "on_update": "CASCADE"
    }
  }
}
```

#### Indexes
- **Primary**: id
- **Regular**: sales_order_id, product_id, inventory_batch_id

#### Foreign Keys
- sales_items_sales_order_id_foreign: sales_order_id → sales_orders.id
- sales_items_product_id_foreign: product_id → products.id
- sales_items_inventory_batch_id_foreign: inventory_batch_id → inventory_batches.id

#### Business Rules
- FIFO cost allocation from inventory batches
- Profit calculation: total_price - total_cost
- Cannot delete items from completed orders

#### Access Patterns
- **Read**: Frequent item lookups for reporting and analysis
- **Write**: High volume item creation during sales

### suppliers Table

#### Purpose
Vendor and supplier management for procurement

#### Columns
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| supplier_code | varchar(50) | NO | | Supplier code |
| name | varchar(255) | NO | | Supplier name |
| contact_person | varchar(255) | YES | | Contact person |
| email | varchar(255) | YES | | Email address |
| phone | varchar(20) | YES | | Phone number |
| address | text | YES | | Address |
| city | varchar(100) | YES | | City |
| state | varchar(100) | YES | | State/Province |
| postal_code | varchar(20) | YES | | Postal code |
| country | varchar(100) | YES | | Country |
| tax_id | varchar(50) | YES | | Tax ID number |
| payment_terms | varchar(100) | YES | | Payment terms |
| credit_limit | decimal(10,2) | YES | 0.00 | Credit limit |
| is_active | boolean | NO | true | Supplier status |
| notes | text | YES | | Supplier notes |
| created_at | timestamp | YES | | Creation timestamp |
| updated_at | timestamp | YES | | Update timestamp |
| deleted_at | timestamp | YES | | Soft delete timestamp |

#### JSON Structure
```json
{
  "table": "suppliers",
  "engine": "InnoDB",
  "charset": "utf8mb4",
  "collation": "utf8mb4_unicode_ci",
  "columns": {
    "id": {
      "type": "bigint unsigned",
      "nullable": false,
      "auto_increment": true,
      "description": "Primary key"
    },
    "supplier_code": {
      "type": "varchar(50)",
      "nullable": false,
      "description": "Unique supplier code"
    },
    "name": {
      "type": "varchar(255)",
      "nullable": false,
      "description": "Supplier name"
    },
    "contact_person": {
      "type": "varchar(255)",
      "nullable": true,
      "description": "Contact person"
    },
    "email": {
      "type": "varchar(255)",
      "nullable": true,
      "description": "Email address"
    },
    "phone": {
      "type": "varchar(20)",
      "nullable": true,
      "description": "Phone number"
    },
    "address": {
      "type": "text",
      "nullable": true,
      "description": "Address"
    },
    "city": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "City"
    },
    "state": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "State/Province"
    },
    "postal_code": {
      "type": "varchar(20)",
      "nullable": true,
      "description": "Postal code"
    },
    "country": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "Country"
    },
    "tax_id": {
      "type": "varchar(50)",
      "nullable": true,
      "description": "Tax ID number"
    },
    "payment_terms": {
      "type": "varchar(100)",
      "nullable": true,
      "description": "Payment terms"
    },
    "credit_limit": {
      "type": "decimal(10,2)",
      "nullable": true,
      "default": "0.00",
      "description": "Credit limit"
    },
    "is_active": {
      "type": "boolean",
      "nullable": false,
      "default": true,
      "description": "Supplier status"
    },
    "notes": {
      "type": "text",
      "nullable": true,
      "description": "Supplier notes"
    },
    "created_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record creation timestamp"
    },
    "updated_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record update timestamp"
    },
    "deleted_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Soft delete timestamp"
    }
  },
  "indexes": {
    "primary": ["id"],
    "suppliers_supplier_code_unique": ["supplier_code"],
    "suppliers_email_unique": ["email"],
    "suppliers_is_active_index": ["is_active"]
  }
}
```

#### Indexes
- **Primary**: id
- **Unique**: supplier_code, email
- **Regular**: is_active

#### Business Rules
- Supplier codes must be unique
- Email must be unique if provided
- Cannot delete suppliers with purchase orders

#### Access Patterns
- **Read**: Frequent supplier lookups for procurement
- **Write**: Moderate supplier creation and updates

### purchase_orders Table

#### Purpose
Procurement and inventory replenishment orders

#### Columns
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| order_number | varchar(50) | NO | | Purchase order number |
| supplier_id | bigint unsigned | NO | | Supplier ID |
| branch_id | bigint unsigned | NO | | Receiving branch ID |
| user_id | bigint unsigned | NO | | Created by user |
| order_date | date | NO | | Order date |
| expected_date | date | YES | | Expected delivery date |
| status | enum('draft','sent','partial','received','cancelled') | NO | draft | Order status |
| subtotal | decimal(10,2) | NO | 0.00 | Subtotal |
| tax_amount | decimal(10,2) | NO | 0.00 | Tax amount |
| discount_amount | decimal(10,2) | NO | 0.00 | Discount amount |
| total_amount | decimal(10,2) | NO | 0.00 | Total amount |
| paid_amount | decimal(10,2) | NO | 0.00 | Amount paid |
| payment_status | enum('pending','partial','paid','refunded') | NO | pending | Payment status |
| notes | text | YES | | Order notes |
| cancelled_at | timestamp | YES | | Cancellation timestamp |
| cancelled_by | bigint unsigned | YES | | Cancelled by user |
| created_at | timestamp | YES | | Creation timestamp |
| updated_at | timestamp | YES | | Update timestamp |

#### JSON Structure
```json
{
  "table": "purchase_orders",
  "engine": "InnoDB",
  "charset": "utf8mb4",
  "collation": "utf8mb4_unicode_ci",
  "columns": {
    "id": {
      "type": "bigint unsigned",
      "nullable": false,
      "auto_increment": true,
      "description": "Primary key"
    },
    "order_number": {
      "type": "varchar(50)",
      "nullable": false,
      "description": "Unique purchase order number"
    },
    "supplier_id": {
      "type": "bigint unsigned",
      "nullable": false,
      "description": "Supplier ID"
    },
    "branch_id": {
      "type": "bigint unsigned",
      "nullable": false,
      "description": "Receiving branch ID"
    },
    "user_id": {
      "type": "bigint unsigned",
      "nullable": false,
      "description": "Created by user"
    },
    "order_date": {
      "type": "date",
      "nullable": false,
      "description": "Order date"
    },
    "expected_date": {
      "type": "date",
      "nullable": true,
      "description": "Expected delivery date"
    },
    "status": {
      "type": "enum('draft','sent','partial','received','cancelled')",
      "nullable": false,
      "default": "draft",
      "description": "Order status"
    },
    "subtotal": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Subtotal"
    },
    "tax_amount": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Tax amount"
    },
    "discount_amount": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Discount amount"
    },
    "total_amount": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Total amount"
    },
    "paid_amount": {
      "type": "decimal(10,2)",
      "nullable": false,
      "default": "0.00",
      "description": "Amount paid"
    },
    "payment_status": {
      "type": "enum('pending','partial','paid','refunded')",
      "nullable": false,
      "default": "pending",
      "description": "Payment status"
    },
    "notes": {
      "type": "text",
      "nullable": true,
      "description": "Order notes"
    },
    "cancelled_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Cancellation timestamp"
    },
    "cancelled_by": {
      "type": "bigint unsigned",
      "nullable": true,
      "description": "Cancelled by user"
    },
    "created_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record creation timestamp"
    },
    "updated_at": {
      "type": "timestamp",
      "nullable": true,
      "description": "Record update timestamp"
    }
  },
  "indexes": {
    "primary": ["id"],
    "purchase_orders_order_number_unique": ["order_number"],
    "purchase_orders_supplier_id_index": ["supplier_id"],
    "purchase_orders_branch_id_index": ["branch_id"],
    "purchase_orders_user_id_index": ["user_id"],
    "purchase_orders_order_date_index": ["order_date"],
    "purchase_orders_status_index": ["status"],
    "purchase_orders_payment_status_index": ["payment_status"]
  },
  "foreign_keys": {
    "purchase_orders_supplier_id_foreign": {
      "column": "supplier_id",
      "references": "suppliers.id",
      "on_delete": "RESTRICT",
      "on_update": "CASCADE"
    },
    "purchase_orders_branch_id_foreign": {
      "column": "branch_id",
      "references": "branches.id",
      "on_delete": "RESTRICT",
      "on_update": "CASCADE"
    },
    "purchase_orders_user_id_foreign": {
      "column": "user_id",
      "references": "users.id",
      "on_delete": "RESTRICT",
      "on_update": "CASCADE"
    },
    "purchase_orders_cancelled_by_foreign": {
      "column": "cancelled_by",
      "references": "users.id",
      "on_delete": "SET_NULL",
      "on_update": "CASCADE"
    }
  }
}
```

#### Indexes
- **Primary**: id
- **Unique**: order_number
- **Regular**: supplier_id, branch_id, user_id, order_date, status, payment_status

#### Foreign Keys
- purchase_orders_supplier_id_foreign: supplier_id → suppliers.id
- purchase_orders_branch_id_foreign: branch_id → branches.id
- purchase_orders_user_id_foreign: user_id → users.id
- purchase_orders_cancelled_by_foreign: cancelled_by → users.id

#### Business Rules
- Purchase order numbers must be unique
- Financial totals must balance
- Cannot delete received purchase orders

#### Access Patterns
- **Read**: Frequent purchase order lookups and reporting
- **Write**: Moderate purchase order creation and updates

## Data Integrity Rules

### Unique Constraints
- users.email: User email uniqueness
- branches.code: Branch code uniqueness
- roles.name: Role name uniqueness
- permissions.name: Permission name uniqueness
- products.sku: Product SKU uniqueness
- products.barcode: Product barcode uniqueness
- products.slug: Product URL slug uniqueness
- product_categories.slug: Category slug uniqueness
- customers.customer_code: Customer code uniqueness
- customers.email: Customer email uniqueness
- customers.phone: Customer phone uniqueness
- suppliers.supplier_code: Supplier code uniqueness
- suppliers.email: Supplier email uniqueness
- inventory_batches.batch_number: Batch number uniqueness
- sales_orders.order_number: Sales order number uniqueness
- purchase_orders.order_number: Purchase order number uniqueness

### Check Constraints
- inventory.quantity_on_hand >= 0: Non-negative stock levels
- inventory.quantity_reserved >= 0: Non-negative reservations
- inventory.quantity_available >= 0: Non-negative availability
- inventory_batches.quantity_remaining >= 0: Non-negative batch quantities
- sales_items.quantity > 0: Positive sales quantities
- products.selling_price >= 0: Non-negative selling prices
- products.cost_price >= 0: Non-negative cost prices
- sales_orders.total_amount >= 0: Non-negative order totals
- purchase_orders.total_amount >= 0: Non-negative purchase totals

### Default Values
- users.is_active: true (new users are active by default)
- branches.is_active: true (new branches are active by default)
- products.is_active: true (new products are active by default)
- inventory.quantity_on_hand: 0 (zero initial stock)
- inventory.quantity_reserved: 0 (zero initial reservations)
- inventory.quantity_available: 0 (zero initial availability)
- sales_orders.status: 'pending' (new orders start as pending)
- purchase_orders.status: 'draft' (new POs start as draft)

### Nullable Rules
- Critical business fields are NOT NULL (ids, codes, names, amounts)
- Optional fields are nullable (descriptions, notes, optional references)
- Foreign keys can be nullable for optional relationships
- Timestamps follow Laravel conventions (nullable for created_at/updated_at)

## Migration Plan

### Migration Order

#### Phase 1: Core Foundation Tables
1. **roles** - Role definitions
2. **permissions** - Permission definitions
3. **branches** - Branch management
4. **product_categories** - Product categorization

#### Phase 2: User and Product Management
5. **users** (extended) - User management with branch assignments
6. **model_has_roles** - User role assignments
7. **role_has_permissions** - Role permission assignments
8. **products** - Product catalog
9. **suppliers** - Supplier management

#### Phase 3: Inventory Management
10. **inventory** - Stock levels per branch
11. **inventory_batches** - FIFO batch tracking
12. **customers** - Customer management

#### Phase 4: Transaction Management
13. **purchase_orders** - Procurement orders
14. **sales_orders** - POS transactions
15. **sales_items** - Sales line items

#### Phase 5: Supporting Tables
16. **purchase_order_items** - Purchase line items (if needed)
17. **inventory_adjustments** - Manual stock corrections
18. **inventory_transfers** - Inter-branch transfers
19. **payments** - Payment processing details

### Rollback Strategy

#### Safe Rollback Procedures
- **Data Preservation**: Use soft deletes where possible
- **Transaction Safety**: Wrap migrations in database transactions
- **Backup Strategy**: Full database backup before major migrations
- **Testing**: Test migrations on staging environment first

#### Rollback Order
- Reverse of migration order (Phase 5 to Phase 1)
- Handle foreign key constraints properly
- Preserve critical business data

### Data Seeding

#### Initial Data Requirements
```php
// Default Roles
- System Administrator
- Business Owner  
- Branch Manager
- Branch Staff
- Cashier
- Store Staff

// Default Permissions
- User management permissions
- Product management permissions
- Inventory management permissions
- Sales permissions
- Reporting permissions

// Reference Data
- Default main branch (if single branch setup)
- Default product categories
- Default tax rates
- Default payment methods
```

#### Test Data Strategy
- Sample products across categories
- Sample inventory levels
- Sample customers
- Sample suppliers
- Historical sales data for testing

## Performance Optimization

### Indexing Strategy

#### Primary Indexes
- All tables have auto-increment primary keys
- Optimized for InnoDB clustering

#### Unique Indexes
- Business keys (SKU, email, codes, order numbers)
- Prevent duplicates and enforce data integrity

#### Composite Indexes
- **inventory**: (product_id, branch_id) for stock lookups
- **sales_orders**: (branch_id, order_date) for branch reporting
- **sales_items**: (sales_order_id, product_id) for order details
- **inventory_batches**: (inventory_id, received_date) for FIFO queries

#### Query-Specific Indexes
- **products**: (is_active, is_sellable) for catalog filtering
- **customers**: (is_active) for active customer lists
- **suppliers**: (is_active) for active supplier lists

### Partitioning Strategy

#### Large Table Partitioning
- **sales_orders**: Partition by order_date (monthly)
- **sales_items**: Partition by created_at (monthly)
- **inventory_batches**: Partition by received_date (quarterly)

#### Partition Benefits
- Improved query performance for date-range queries
- Easier archival of old data
- Better backup and maintenance operations

### Caching Strategy

#### Application-Level Caching
- **Product Catalog**: Cache active products and categories
- **User Permissions**: Cache role-based permissions
- **Branch Information**: Cache branch settings and configurations
- **Price Lists**: Cache product pricing by branch

#### Cache Invalidation Rules
- Product changes invalidate product cache
- User role changes invalidate permission cache
- Branch changes invalidate branch cache
- Price changes invalidate price cache

### Query Optimization

#### Common Query Patterns
```sql
-- Stock availability check
SELECT i.quantity_available 
FROM inventory i 
WHERE i.product_id = ? AND i.branch_id = ? AND i.is_active = 1;

-- FIFO batch selection
SELECT * 
FROM inventory_batches 
WHERE inventory_id = ? AND quantity_remaining > 0 
ORDER BY received_date ASC;

-- Sales reporting by branch
SELECT DATE(order_date) as date, COUNT(*) as orders, SUM(total_amount) as revenue
FROM sales_orders 
WHERE branch_id = ? AND status = 'completed'
GROUP BY DATE(order_date);

-- Product search
SELECT p.*, i.quantity_available
FROM products p
LEFT JOIN inventory i ON p.id = i.product_id AND i.branch_id = ?
WHERE p.is_active = 1 AND p.is_sellable = 1
AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode = ?);
```

#### Query Performance Tips
- Use appropriate indexes for WHERE clauses
- Limit result sets for pagination
- Use EXPLAIN to analyze query plans
- Avoid N+1 queries with proper eager loading

## Schema Validation Checklist

### Completeness Verification
- [ ] All entities from requirements are represented
- [ ] All relationships are properly defined
- [ ] Foreign key constraints are correct
- [ ] Indexes support query patterns
- [ ] Data types are appropriate for scale
- [ ] Business rules are enforced

### Performance Validation
- [ ] Critical queries have optimal indexes
- [ ] Large tables have partitioning strategy
- [ ] Caching strategy is defined
- [ ] Query patterns are optimized
- [ ] Database normalization is appropriate

### Security Validation
- [ ] Role-based access control is comprehensive
- [ ] Sensitive data is properly protected
- [ ] Audit trail capabilities exist
- [ ] Data integrity constraints are in place
- [ ] Soft deletes preserve data

### Scalability Validation
- [ ] Schema supports multi-branch operations
- [ ] FIFO inventory tracking is robust
- [ ] High-volume transactions are supported
- [ ] Reporting queries are performant
- [ ] Data archiving strategy exists

## Implementation Timeline

### Phase 1: Foundation (Week 1-2)
- Set up development environment
- Create core tables (roles, permissions, branches, users)
- Implement basic authentication and authorization
- Set up testing framework

### Phase 2: Product Management (Week 3)
- Implement product catalog and categories
- Create supplier management
- Set up basic inventory tracking
- Implement product search and filtering

### Phase 3: Inventory System (Week 4-5)
- Implement FIFO batch tracking
- Create inventory adjustment workflows
- Set up reorder point notifications
- Implement inventory transfers

### Phase 4: POS System (Week 6-7)
- Implement sales order processing
- Create payment processing
- Implement receipt generation
- Set up customer management

### Phase 5: Reporting & Analytics (Week 8)
- Implement sales reporting
- Create inventory reports
- Set up financial analytics
- Implement dashboard functionality

### Phase 6: Testing & Deployment (Week 9-10)
- Comprehensive testing
- Performance optimization
- Security validation
- Production deployment

## Success Metrics

### Technical Metrics
- Query response time < 100ms for critical operations
- Database uptime > 99.9%
- Concurrent user support > 100 users
- Data accuracy > 99.99%

### Business Metrics
- Inventory accuracy > 95%
- Transaction processing < 30 seconds
- User adoption rate > 80%
- Customer satisfaction > 4.5/5

### Operational Metrics
- System availability during business hours
- Backup completion success rate
- Data recovery time < 4 hours
- User training completion > 90%
