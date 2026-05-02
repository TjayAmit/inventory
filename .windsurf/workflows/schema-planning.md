---
description: Data Engineering Planning Workflow - Comprehensive schema design and documentation for software development projects
---

# Data Engineering Planning Workflow

This workflow provides a systematic approach to designing database schemas for software development projects. It follows a 10-year data engineering discipline to ensure solid foundations that deliver project goals.

## Prerequisites

- Laravel project initialized
- Database connection configured
- MCP Laravel-Boost server available
- Access to database schema tools

## Phase 1: Project Definition and Goal Setting

### Step 1: Understand Project Purpose
- Document the project's core mission and business objectives
- Identify the problem domain and scope
- Define success metrics and KPIs
- List stakeholders and their data needs

### Step 2: Define Functional Requirements
- List all user stories and use cases
- Identify data entities and their relationships
- Map business processes to data flows
- Document constraints and assumptions

### Step 3: Create Project Planning Document
Create a markdown file in `./windsurf/plans/[project-name]-plan.md` with:

```markdown
# [Project Name] - Data Engineering Plan

## Project Overview
- **Purpose**: [Clear statement of what the system achieves]
- **Business Goal**: [What business problem does this solve?]
- **Target Users**: [Who will use this system]
- **Success Criteria**: [Measurable outcomes]

## Functional Requirements
1. [Requirement 1]
2. [Requirement 2]
...

## Data Entities Identified
- [Entity 1]: [Description]
- [Entity 2]: [Description]
...

## Relationships
- [Entity A] → [Entity B]: [Relationship type and purpose]
...
```

## Phase 2: Existing Schema Analysis

### Step 4: Document Existing Laravel Schema
Use MCP tools to extract and document existing database structure:

```bash
# Get database schema summary
mcp0_database-schema --summary=true

# Get detailed schema with column information
mcp0_database-schema --include_column_details=true
```

Document existing tables in the planning markdown:

```markdown
## Existing Laravel Schema

### Standard Laravel Tables
- **users**: User authentication and management
- **cache**: Application caching
- **sessions**: User session management
- **jobs**: Queue job management
- **failed_jobs**: Failed job tracking
- **migrations**: Database migration tracking
- **password_reset_tokens**: Password reset functionality

### Custom Tables (if any)
- [Table Name]: [Purpose and description]
```

### Step 5: JSON Schema Documentation
For each existing table, document the JSON structure:

```markdown
### users Table JSON Structure
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
    }
  },
  "indexes": {
    "primary": ["id"],
    "users_email_unique": ["email"]
  }
}
```

## Phase 3: Schema Design

### Step 6: Entity-Relationship Modeling
For each identified entity, define:

**Entity Structure Template:**
```markdown
### [Entity Name]
- **Purpose**: [Why this entity exists]
- **Business Role**: [What business concept it represents]
- **Lifecycle**: [How records are created, updated, deleted]
- **Cardinality**: [Expected number of records]
- **Access Patterns**: [Read/write frequency, query patterns]
```

### Step 7: Relationship Design
Document all relationships:

```markdown
## Entity Relationships

### [Entity A] to [Entity B]
- **Relationship Type**: [One-to-One, One-to-Many, Many-to-Many]
- **Foreign Key**: [Which table holds the FK]
- **Cascade Rules**: [ON DELETE, ON UPDATE behavior]
- **Business Rule**: [Why this relationship exists]
- **Query Pattern**: [Common join queries]
```

### Step 8: Table Schema Design
For each new table, create a complete schema definition:

**Table Schema Template:**
```markdown
### [table_name] Table

#### Purpose
[Clear description of what this table stores and why]

#### Columns
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| [col_name] | [type] | [YES/NO] | [default] | [description] |

#### JSON Structure
```json
{
  "table": "[table_name]",
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
    "[column_name]": {
      "type": "[data_type]",
      "nullable": [true/false],
      "default": "[default_value]",
      "description": "[Column purpose]"
    }
  },
  "indexes": {
    "primary": ["id"],
    "[index_name]": ["column1", "column2"]
  },
  "foreign_keys": {
    "[fk_name]": {
      "column": "[local_column]",
      "references": "[referenced_table].[referenced_column]",
      "on_delete": "[CASCADE/RESTRICT/SET_NULL/NO_ACTION]",
      "on_update": "[CASCADE/RESTRICT/SET_NULL/NO_ACTION]"
    }
  }
}
```

#### Indexes
- **Primary**: id
- **Unique**: [list unique indexes]
- **Regular**: [list regular indexes with purpose]

#### Foreign Keys
- [FK name]: [local_column] → [referenced_table].[referenced_column]

#### Business Rules
- [Rule 1]: [Description]
- [Rule 2]: [Description]

#### Access Patterns
- **Read**: [Query patterns, frequency]
- **Write**: [Insert/update patterns, frequency]
```

### Step 9: Data Integrity Rules
Document all constraints:

```markdown
## Data Integrity Rules

### Unique Constraints
- [Constraint]: [Business reason]

### Check Constraints
- [Constraint]: [Validation rule]

### Default Values
- [Column]: [Default value and reason]

### Nullable Rules
- [Column]: [Why nullable or not]
```

## Phase 4: Migration Planning

### Step 10: Migration Strategy
```markdown
## Migration Plan

### Migration Order
1. [Base tables without dependencies]
2. [Tables with single dependencies]
3. [Tables with multiple dependencies]
4. [Pivot tables for many-to-many]

### Rollback Strategy
- [How to safely rollback each migration]
- [Data preservation considerations]

### Data Seeding
- [Initial data requirements]
- [Reference data needed]
- [Test data strategy]
```

### Step 11: Performance Considerations
```markdown
## Performance Optimization

### Indexing Strategy
- [Which columns need indexes and why]
- [Composite indexes for common query patterns]
- [Index size considerations]

### Partitioning (if applicable)
- [Partitioning strategy for large tables]
- [Partition key selection]

### Caching Strategy
- [Which data should be cached]
- [Cache invalidation rules]
```

## Phase 5: Documentation Review

### Step 12: Schema Validation Checklist
Before finalizing, verify:

- [ ] All entities from requirements are represented
- [ ] All relationships are properly defined
- [ ] Foreign key constraints are correct
- [ ] Indexes support query patterns
- [ ] Data types are appropriate for the data
- [ ] Nullable rules align with business logic
- [ ] Default values are sensible
- [ ] Cascade rules prevent orphaned records
- [ ] Naming conventions are consistent
- [ ] JSON structures are complete and accurate
- [ ] Existing Laravel schema is documented
- [ ] Migration order is logical
- [ ] Rollback strategy is safe

### Step 13: Cross-Reference Validation
```markdown
## Cross-Reference Validation

### Requirements to Schema Mapping
| Requirement | Schema Element | Status |
|-------------|----------------|--------|
| [Req 1] | [Table/Column] | ✓/✗ |
| [Req 2] | [Table/Column] | ✓/✗ |

### Completeness Check
- [ ] All user stories have corresponding schema
- [ ] All business rules are enforced
- [ ] All data flows are supported
```

## Phase 6: Implementation Guidance

### Step 14: Laravel Migration Templates
Provide migration templates for each table:

```php
// database/migrations/YYYY_MM_DD_HHMMSS_create_[table_name]_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('[table_name]', function (Blueprint $table) {
            $table->id();
            // Add columns here
            $table->timestamps();
            
            // Add indexes
            $table->index(['column1', 'column2'], '[index_name]');
            
            // Add foreign keys
            $table->foreignId('[column]')->constrained('[referenced_table]')->onDelete('[action]');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('[table_name]');
    }
};
```

### Step 15: Model Documentation
```markdown
## Model Definitions

### [ModelName]
```php
// app/Models/[ModelName].php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo, BelongsToMany};

class [ModelName] extends Model
{
    protected $fillable = [
        // Fillable fields
    ];

    protected $casts = [
        // Type casts
    ];

    // Relationships
    public function [related](): [RelationshipType]
    {
        return $this->[relationshipMethod]([RelatedModel]::class);
    }
}
```

**Relationships:**
- [Relation 1]: [Type and purpose]
- [Relation 2]: [Type and purpose]

**Scopes:**
- [Scope 1]: [Purpose]
- [Scope 2]: [Purpose]
```

## Phase 7: Final Review

### Step 16: Quality Assurance
Review the complete plan and ensure:

1. **Completeness**: All requirements are addressed
2. **Consistency**: Naming and structure are uniform
3. **Correctness**: Schema accurately represents the domain
4. **Clarity**: Documentation is understandable
5. **Maintainability**: Future changes will be easy
6. **Performance**: Design supports expected load
7. **Security**: Sensitive data is properly protected
8. **Scalability**: Design can handle growth

### Step 17: Stakeholder Review
- Share the planning document with stakeholders
- Gather feedback on schema design
- Validate assumptions with domain experts
- Adjust schema based on feedback

### Step 18: Final Approval
- Obtain sign-off on the schema design
- Document any approved changes
- Lock the schema for implementation
- Create implementation timeline

## Output Structure

The final output should be a comprehensive markdown file located at:
```
./windsurf/plans/[project-name]-data-plan.md
```

With the following sections:
1. Project Overview
2. Functional Requirements
3. Existing Laravel Schema Documentation
4. Entity Definitions
5. Relationship Design
6. Complete Table Schemas with JSON Structures
7. Data Integrity Rules
8. Migration Plan
9. Performance Considerations
10. Model Definitions
11. Implementation Guidance
12. Validation Checklist

## Best Practices

1. **Start with requirements, not tables** - Always derive schema from business needs
2. **Normalize appropriately** - Balance normalization with performance
3. **Index strategically** - Add indexes based on query patterns, not just columns
4. **Document everything** - Future you will thank present you
5. **Think about data lifecycle** - Consider how data is created, updated, and deleted
6. **Plan for growth** - Design for scale from day one
7. **Use appropriate data types** - Choose the smallest type that fits the data
8. **Enforce constraints at database level** - Don't rely solely on application validation
9. **Consider soft deletes** - For audit trails and recovery
10. **Plan for migrations** - Design schemas that can evolve gracefully

## Common Pitfalls to Avoid

1. **Over-normalization** - Can hurt performance and complexity
2. **Under-indexing** - Causes slow queries as data grows
3. **Missing foreign keys** - Leads to orphaned records
4. **Inconsistent naming** - Makes the schema hard to understand
5. **Ignoring NULL semantics** - NULL ≠ empty string or 0
6. **Using ENUM inappropriately** - Hard to modify, use lookup tables instead
7. **Storing delimited data** - Violates first normal form
8. **Ignoring character sets** - Use utf8mb4 for full Unicode support
9. **Forgetting timestamps** - Critical for auditing and debugging
10. **Not planning for rollbacks** - Every migration needs a down() method

## Quick Reference Commands

```bash
# Get database schema
mcp0_database-schema --summary=true
mcp0_database-schema --include_column_details=true

# Get application info
mcp0_application-info

# Query database
mcp0_database-query --query="SELECT * FROM information_schema.tables"

# Get last error
mcp0_last-error

# Read logs
mcp0_read-log-entries --entries=50
```

## Example Usage

To use this workflow for a new project:

1. Create a new planning document: `./windsurf/plans/[project-name]-data-plan.md`
2. Follow each phase sequentially
3. Use the templates provided for consistency
4. Complete the validation checklist before implementation
5. Share with stakeholders for review
6. Obtain approval before writing migrations

## Continuous Improvement

After each project:
- Document what worked well
- Note areas for improvement
- Update this workflow with lessons learned
- Refine templates based on actual usage
