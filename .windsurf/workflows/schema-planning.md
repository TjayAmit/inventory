---
description: Schema Planning Workflow — Systematic database schema design for Laravel projects.
---

# Schema Planning Workflow

Design database schemas from business requirements, not the other way around.

---

## Phase 1: Requirements

Before touching any schema:
1. State the project's **core purpose** and who uses it.
2. List all **user stories** and data entities they imply.
3. Map **relationships** between entities (1:1, 1:N, N:M).
4. Identify **access patterns**: what queries run most often.

Output: a short requirements doc at `./windsurf/plans/[project]-plan.md`.

---

## Phase 2: Existing Schema Audit

Use MCP tools to extract current state:
```bash
mcp0_database-schema --summary=true
mcp0_database-schema --include_column_details=true
```

Document what already exists before designing anything new. Note any tables that will be extended vs. created fresh.

---

## Phase 3: Schema Design

For each new entity define:
- **Purpose** — why it exists
- **Columns** — type, nullable, default, index strategy
- **Relationships** — FK targets, cascade rules
- **Soft deletes** — required on all tables (`deleted_at`)

### Column type checklist
| Data | Use |
|------|-----|
| Names/strings | `varchar(255)` |
| Long text | `text` |
| Money | `decimal(10,2)` — never float |
| Booleans | `boolean` with default |
| Status/type | lookup table, not `ENUM` |
| Timestamps | `timestamps()` + `softDeletes()` |

### Every migration must include
```php
$table->timestamps();
$table->softDeletes();
$table->index(['deleted_at']);
```

### Foreign key conventions
```php
$table->foreignId('category_id')->constrained()->cascadeOnDelete();
```
Use `cascadeOnDelete()` for owned records, `restrictOnDelete()` for referenced records.

---

## Phase 4: Migration Order

Migrate in dependency order:
1. Standalone lookup/reference tables
2. Core entity tables (no cross-dependencies)
3. Entity tables with FKs to core tables
4. Pivot tables for N:M relationships

Every `up()` must have a working `down()`.

---

## Phase 5: Indexing Strategy

Add indexes for:
- All foreign keys (Laravel does this automatically with `foreignId()`)
- Columns used in `WHERE`, `ORDER BY`, or `GROUP BY` at scale
- Composite indexes for frequent multi-column filters
- `deleted_at` on every soft-delete table

Don't index every column — unused indexes slow writes.

---

## Phase 6: Validation Checklist

Before writing any migration code:
- [ ] All requirements map to a table/column
- [ ] All relationships have FK constraints
- [ ] No ENUMs — use lookup tables
- [ ] All tables have `timestamps()` and `softDeletes()`
- [ ] Nullable rules match business logic
- [ ] Cascade rules prevent orphaned records
- [ ] Migration order is dependency-safe
- [ ] Every `up()` has a `down()`
- [ ] Naming is consistent (`snake_case`, plural tables, singular FKs)

---

## Common Pitfalls

- **ENUM columns** — hard to modify; use a lookup table
- **Storing delimited data** — violates 1NF; use a join table
- **Float for money** — precision errors; use `decimal`
- **Missing soft deletes** — no recovery path, no audit trail
- **Over-normalizing** — join-heavy schemas kill read performance
- **Under-indexing** — slow queries as data grows

---

## Quick Reference
```bash
mcp0_database-schema --summary=true
mcp0_database-schema --include_column_details=true
mcp0_database-query --query="SELECT * FROM information_schema.tables WHERE table_schema = DATABASE()"
mcp0_read-log-entries --entries=50
```
