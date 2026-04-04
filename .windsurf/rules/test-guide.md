---
trigger: manual
---

# Test Workflow

## Purpose
Ensure proper test structure by separating logic testing from database-dependent testing.

---

## 1. Test Classification

### Unit Tests
- Focus only on **pure logic**.
- Must NOT interact with:
  - Database
  - External services
  - File system
- Should be fast and isolated.
- Ideal for:
  - Services
  - Helpers
  - Business logic
  - Data transformations

---

### Feature Tests
- Any test that **touches the database** must be placed here.
- Covers:
  - API endpoints
  - Controllers
  - Jobs
  - Full request lifecycle
- Validates integration between components.

---

## 2. Rules

- ❌ Do NOT use database in Unit tests.
- ✅ Use Feature tests for:
  - Eloquent queries
  - Migrations
  - Seeders
  - Authentication flows
- ✅ Keep Unit tests lightweight and fast.
- ✅ Keep Feature tests realistic and closer to real-world usage.

---

## 3. Structure

- `tests/Unit/`
  - Pure logic tests only

- `tests/Feature/`
  - Database + integration tests

---

## 4. Best Practices

- Use factories in Feature tests.
- Use `RefreshDatabase` or transactions when needed.
- Mock dependencies in Unit tests.
- Avoid over-testing implementation details.

---

## 5. Key Principle

> If the test touches the database, it is NOT a Unit test.