---
description: Laravel Backend Code Review Workflow - 10 Years Experience Standards
---

# Laravel Backend Code Review Workflow

## Overview
This workflow establishes the coding standards and review process for Laravel backend development based on 10 years of experience. It enforces clean architecture, proper separation of concerns, and robust error handling.

**Note**: This workflow combines Laravel framework standards with architectural patterns (Repository, DTO, Service Layer) that are team-specific choices, not Laravel framework requirements. These patterns promote maintainability and testability in enterprise applications.

## Core Principles

### 1. Controller Responsibilities
- **ONLY** receive HTTP requests and perform validation
- **NEVER** contain business logic
- **ALWAYS** delegate to appropriate Service layer
- **MUST** return Inertia responses or proper HTTP responses

### 2. Service Layer Architecture
- **ALL** create/update/delete operations **MUST** use database transactions
- **ALWAYS** convert requests to DTOs before processing
- **MUST** use `DTO->toArray()` for data operations
- **REQUIRED** to log activities using Spatie Activity Log
- **NEVER** contain database queries directly (use Repositories)
- **CRITICAL**: Activity logging within transactions must handle commit timing to avoid logging data before transaction commits

### 3. Data Management Standards
- **ALL** models **MUST** support soft deletes
- **ALL** migrations **MUST** include soft delete columns
- **NEVER** use permanent deletes unless explicitly required
- **ALWAYS** use `deleted_at` timestamp for soft deletes

## Code Review Checklist

### Controller Review
```php
// ✅ GOOD EXAMPLE
public function store(StoreSubjectRequest $request, SubjectService $service)
{
    // Only validation and delegation
    $subject = $service->create($request);
    
    return redirect()->route('subjects.index')
        ->with('success', 'Subject created successfully');
}

// ❌ BAD EXAMPLE
public function store(Request $request)
{
    // Business logic in controller - FORBIDDEN
    $subject = Subject::create($request->validated());
    return response()->json($subject);
}
```

### Service Layer Review
```php
// ✅ GOOD EXAMPLE - Activity logging after transaction commit
public function create(Request $request): Subject
{
    $model = null;
    
    DB::transaction(function () use ($request, &$model) {
        $dto = SubjectData::fromRequest($request);
        $model = $this->repository->create($dto->toArray());
    });
    
    // Log activity AFTER transaction commits to ensure data integrity
    $this->logActivity('created', $model, $dto->toArray());
    
    return $model;
}

// ✅ ALTERNATIVE - Using after_commit configuration
// Set 'after_commit' => true in config/activitylog.php or queue connection
public function create(Request $request): Subject
{
    return DB::transaction(function () use ($request) {
        $dto = SubjectData::fromRequest($request);
        $subject = $this->repository->create($dto->toArray());
        
        $this->logActivity('created', $subject, $dto->toArray());
        
        return $subject;
    });
}

// ❌ BAD EXAMPLE
public function create(Request $request)
{
    // No transaction - FORBIDDEN
    // No DTO conversion - FORBIDDEN
    // No activity logging - FORBIDDEN
    return Subject::create($request->validated());
}
```

### Activity Logging Standards
```php
// ✅ REQUIRED LOGGING PATTERN
private function logActivity(string $action, Model $model, array $data = []): void
{
    $properties = [];
    
    if ($action === 'updated') {
        $properties['old'] = $model->getOriginal();
        $properties['new'] = $data;
    }
    
    if ($action === 'deleted') {
        $properties['deleted_data'] = $data;
        $properties['deleted_by'] = auth()->id();
    }
    
    activity()
        ->causedBy(auth()->user())
        ->performedOn($model)
        ->withProperties($properties)
        ->log("{$action} " . class_basename($model));
}
```

## Required File Structure

### 1. Models with Soft Deletes
```php
// ALL MODELS MUST INCLUDE (Laravel Standard):
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes;
    
    protected $fillable = [...];
    // Note: SoftDeletes trait automatically casts deleted_at to DateTime/Carbon
    // No need to manually add to $dates array in Laravel 8+
}
```

### 2. Migrations with Soft Deletes
```php
// ALL MIGRATIONS MUST INCLUDE:
Schema::create('subjects', function (Blueprint $table) {
    $table->id();
    // ... other columns
    $table->timestamps();
    $table->softDeletes(); // REQUIRED
    $table->index(['deleted_at']); // RECOMMENDED
});
```

### 3. DTO Structure
```php
// ALL ENTITIES MUST HAVE DTO:
class SubjectData
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $description = null,
        public readonly bool $is_active = true,
    ) {}
    
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            code: $request->validated('code'),
            description: $request->validated('description'),
            is_active: $request->validated('is_active', true),
        );
    }
    
    public static function fromModel(Subject $subject): self
    {
        return new self(
            name: $subject->name,
            code: $subject->code,
            description: $subject->description,
            is_active: $subject->is_active,
        );
    }
    
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
```

### 4. Service Layer Template
```php
abstract class BaseService
{
    public function __construct(
        protected RepositoryInterface $repository
    ) {}
    
    public function create(Request $request): Model
    {
        $model = null;
        $dto = null;
        
        DB::transaction(function () use ($request, &$model, &$dto) {
            $dto = $this->getDtoClass()::fromRequest($request);
            $model = $this->repository->create($dto->toArray());
        });
        
        // Log activity AFTER transaction commits to ensure data integrity
        // This follows Laravel's best practice for events within transactions
        $this->logActivity('created', $model, $dto->toArray());
        
        return $model;
    }
    
    public function update(Request $request, Model $model): Model
    {
        $oldData = $model->getOriginal();
        $dto = null;
        $updatedModel = null;
        
        DB::transaction(function () use ($request, $model, &$dto, &$updatedModel) {
            $dto = $this->getDtoClass()::fromRequest($request);
            $updatedModel = $this->repository->update($model->id, $dto->toArray());
        });
        
        // Log activity AFTER transaction commits
        $this->logActivity('updated', $updatedModel, [
            'old' => $oldData,
            'new' => $dto->toArray()
        ]);
        
        return $updatedModel;
    }
    
    public function delete(Model $model): bool
    {
        $data = $model->toArray();
        $result = false;
        
        DB::transaction(function () use ($model, &$result) {
            $result = $this->repository->delete($model->id);
        });
        
        // Log activity AFTER transaction commits
        $this->logActivity('deleted', $model, $data);
        
        return $result;
    }
    
    abstract protected function getDtoClass(): string;
    abstract protected function logActivity(string $action, Model $model, array $data): void;
}
```

### 4.1 Alternative: Using after_commit Configuration
If you prefer to keep activity logging within the transaction, configure Spatie Activitylog to use `after_commit`:

```php
// In config/activitylog.php
'after_commit' => true,
```

This ensures activity logs are only persisted after the database transaction commits successfully, following Laravel's recommendation for jobs/events within transactions.

### 4.2 Laravel Standard: ShouldHandleEventsAfterCommit
For model observers that log activity, Laravel provides the `ShouldHandleEventsAfterCommit` interface:

```php
<?php

namespace App\Observers;

use App\Models\Subject;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class SubjectObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Subject $subject): void
    {
        // This only executes after the database transaction commits
        activity()
            ->causedBy(auth()->user())
            ->performedOn($subject)
            ->log('created subject');
    }
    
    public function updated(Subject $subject): void
    {
        // This only executes after the database transaction commits
        activity()
            ->causedBy(auth()->user())
            ->performedOn($subject)
            ->withProperties(['old' => $subject->getOriginal()])
            ->log('updated subject');
    }
    
    public function deleted(Subject $subject): void
    {
        // This only executes after the database transaction commits
        activity()
            ->causedBy(auth()->user())
            ->performedOn($subject)
            ->withProperties(['deleted_data' => $subject->toArray()])
            ->log('deleted subject');
    }
}
```

This is Laravel's recommended approach for observers that need to ensure data consistency when models are created within transactions.

## Review Process

### Phase 1: Automated Checks
- [ ] PHPStan/Pint passes without errors
- [ ] All models have soft deletes
- [ ] All migrations include soft deletes
- [ ] Controllers only validate and delegate
- [ ] Services use transactions for write operations

### Phase 2: Manual Code Review
- [ ] DTO conversion is present in all service methods
- [ ] Activity logging is implemented for all operations
- [ ] Activity logging handles transaction commit timing correctly
- [ ] Repository pattern is properly implemented (architectural choice)
- [ ] Error handling is comprehensive
- [ ] Input validation is thorough
- [ ] Laravel standards are followed (soft deletes, transactions, etc.)

### Phase 3: Integration Testing
- [ ] All CRUD operations work with transactions
- [ ] Soft deletes function properly
- [ ] Activity logs are created correctly
- [ ] Rollback scenarios are tested

## Common Anti-Patterns to Reject

### ❌ FORBIDDEN PATTERNS
1. **Business logic in controllers**
2. **Direct database calls in services**
3. **Missing transactions for write operations**
4. **Hard-coded arrays instead of DTOs**
5. **Missing activity logging**
6. **Permanent deletes without justification**
7. **Missing soft delete support**

### ✅ REQUIRED PATTERNS
1. **Controllers only validate and delegate**
2. **Services use repositories for data access**
3. **All write operations in transactions**
4. **DTO conversion for all data operations**
5. **Comprehensive activity logging**
6. **Soft deletes by default**
7. **Proper error handling and rollback**

## Review Commands

### Before Review
```bash
# Check code quality
./vendor/bin/sail pint --test

# Run tests
./vendor/bin/sail artisan test

# Check types (if using PHPStan)
./vendor/bin/sail phpstan analyse
```

### During Review
- Focus on architecture compliance
- Verify transaction boundaries
- Check activity logging completeness
- Ensure soft delete implementation

### After Review
- Update documentation if needed
- Add missing tests
- Refactor any non-compliant code

## Exception Handling

### Service Layer Error Handling
```php
public function create(Request $request): Model
{
    try {
        return DB::transaction(function () use ($request) {
            // Service logic here
        });
    } catch (ValidationException $e) {
        throw $e; // Re-throw validation errors
    } catch (Exception $e) {
        Log::error('Service error: ' . $e->getMessage(), [
            'service' => static::class,
            'data' => $request->all()
        ]);
        
        throw new ServiceException('Operation failed. Please try again.');
    }
}
```

This workflow ensures consistent, maintainable, and robust Laravel backend development following enterprise-grade standards.
