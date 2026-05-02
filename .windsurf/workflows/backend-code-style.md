---
description: Laravel Backend Code Style — Clean architecture standards for controllers, services, repositories, DTOs, and models.
---

# Laravel Backend Code Style

## Architecture Rules

### Controllers
- Validate input and delegate to a Service — nothing else.
- Return Inertia responses or proper HTTP responses.
- Never contain business logic or direct DB calls.

### Services
- All write operations must use `DB::transaction()`.
- Convert request data to DTOs before any operation.
- Use `$dto->toArray()` for repository calls.
- Log activity via Spatie Activity Log **after** the transaction commits.
- Never query the database directly — use Repositories.

### Models
- Always use `SoftDeletes` trait.
- Never use permanent deletes unless explicitly required.

### DTOs
- Every entity must have a DTO with `fromRequest()`, `fromModel()`, and `toArray()`.
- `toArray()` should filter out null values.

---

## Canonical Patterns

### Controller
```php
public function store(StoreProductRequest $request, ProductService $service)
{
    $service->create($request);
    return redirect()->route('products.index')->with('success', 'Product created.');
}
```

### Service — write operation
```php
public function create(Request $request): Product
{
    $model = null;
    $dto = null;

    DB::transaction(function () use ($request, &$model, &$dto) {
        $dto = ProductData::fromRequest($request);
        $model = $this->repository->create($dto->toArray());
    });

    $this->logActivity('created', $model, $dto->toArray());

    return $model;
}

public function update(Request $request, Product $product): Product
{
    $old = $product->getOriginal();
    $dto = null;
    $updated = null;

    DB::transaction(function () use ($request, $product, &$dto, &$updated) {
        $dto = ProductData::fromRequest($request);
        $updated = $this->repository->update($product->id, $dto->toArray());
    });

    $this->logActivity('updated', $updated, ['old' => $old, 'new' => $dto->toArray()]);

    return $updated;
}

public function delete(Product $product): bool
{
    $data = $product->toArray();
    $result = false;

    DB::transaction(function () use ($product, &$result) {
        $result = $this->repository->delete($product->id);
    });

    $this->logActivity('deleted', $product, $data);

    return $result;
}
```

### Activity Logging
```php
private function logActivity(string $action, Model $model, array $data = []): void
{
    $properties = match ($action) {
        'updated' => ['old' => $model->getOriginal(), 'new' => $data],
        'deleted' => ['deleted_data' => $data, 'deleted_by' => auth()->id()],
        default   => [],
    };

    activity()
        ->causedBy(auth()->user())
        ->performedOn($model)
        ->withProperties($properties)
        ->log("{$action} " . class_basename($model));
}
```

### DTO
```php
class ProductData
{
    public function __construct(
        public readonly string $name,
        public readonly string $sku,
        public readonly float $price,
        public readonly ?string $description = null,
        public readonly bool $is_active = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            sku: $request->validated('sku'),
            price: $request->validated('price'),
            description: $request->validated('description'),
            is_active: $request->validated('is_active', true),
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
```

### Model
```php
class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'sku', 'price', 'description', 'is_active'];
}
```

### Migration
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('sku')->unique();
    $table->decimal('price', 10, 2);
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->index(['deleted_at']);
});
```

### Service Exception Handling
```php
public function create(Request $request): Model
{
    try {
        return DB::transaction(function () use ($request) {
            $dto = $this->getDtoClass()::fromRequest($request);
            return $this->repository->create($dto->toArray());
        });
    } catch (ValidationException $e) {
        throw $e;
    } catch (Exception $e) {
        Log::error('Service error: ' . $e->getMessage(), ['service' => static::class]);
        throw new ServiceException('Operation failed. Please try again.');
    }
}
```

---

## Forbidden Patterns
- Business logic in controllers
- Direct DB calls inside services
- Write operations outside transactions
- Raw arrays instead of DTOs
- Missing activity logging
- Missing soft deletes
- Permanent deletes without justification

---

## Review Commands
```bash
./vendor/bin/sail pint --test
./vendor/bin/sail artisan test
```
