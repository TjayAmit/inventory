<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'product_code' => 'PRD' . fake()->unique()->numerify('######'),
            'barcode' => fake()->optional(0.7)->ean13(), // 70% chance of having a barcode
            'description' => fake()->sentence(15),
            'price' => fake()->randomFloat(2, 10, 1000),
            'cost_price' => fake()->randomFloat(2, 5, 500),
            'category_id' => Category::factory(),
            'is_active' => true,
            'is_taxable' => true,
            'unit' => fake()->randomElement(['pcs', 'kg', 'ltr', 'box', 'pack']),
            'weight' => fake()->optional(0.5)->randomFloat(3, 0.1, 100), // 50% chance of having weight
            'volume' => fake()->optional(0.3)->randomFloat(3, 0.1, 50), // 30% chance of having volume
            'brand' => fake()->optional(0.8)->company(), // 80% chance of having a brand
            'manufacturer' => fake()->optional(0.6)->company(), // 60% chance of having manufacturer
            'supplier' => fake()->optional(0.7)->company(), // 70% chance of having supplier
            'reorder_point' => fake()->numberBetween(5, 50),
            'max_stock' => fake()->numberBetween(100, 1000),
            'notes' => fake()->optional(0.4)->sentence(10), // 40% chance of having notes
        ];
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is not taxable.
     */
    public function nonTaxable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_taxable' => false,
        ]);
    }

    /**
     * Create a product with a specific category.
     */
    public function withCategory(?Category $category = null): static
    {
        if (!$category) {
            $category = Category::factory()->create();
        }

        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Create a product without a category.
     */
    public function withoutCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => null,
        ]);
    }

    /**
     * Create a product with a barcode.
     */
    public function withBarcode(): static
    {
        return $this->state(fn (array $attributes) => [
            'barcode' => Product::generateDummyBarcode(),
        ]);
    }

    /**
     * Create a product without a barcode.
     */
    public function withoutBarcode(): static
    {
        return $this->state(fn (array $attributes) => [
            'barcode' => null,
        ]);
    }

    /**
     * Create a product with a specific price.
     */
    public function withPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
        ]);
    }

    /**
     * Create a product with a specific cost price.
     */
    public function withCostPrice(float $costPrice): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_price' => $costPrice,
        ]);
    }

    /**
     * Create a product without cost price.
     */
    public function withoutCostPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_price' => null,
        ]);
    }

    /**
     * Create a product with a specific brand.
     */
    public function withBrand(string $brand): static
    {
        return $this->state(fn (array $attributes) => [
            'brand' => $brand,
        ]);
    }

    /**
     * Create a product with a specific supplier.
     */
    public function withSupplier(string $supplier): static
    {
        return $this->state(fn (array $attributes) => [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Create a product with specific stock levels.
     */
    public function withStockLevels(int $reorderPoint, int $maxStock): static
    {
        return $this->state(fn (array $attributes) => [
            'reorder_point' => $reorderPoint,
            'max_stock' => $maxStock,
        ]);
    }

    /**
     * Create a product with a specific unit.
     */
    public function withUnit(string $unit): static
    {
        return $this->state(fn (array $attributes) => [
            'unit' => $unit,
        ]);
    }

    /**
     * Create a product with weight and volume.
     */
    public function withDimensions(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => fake()->randomFloat(3, 0.1, 100),
            'volume' => fake()->randomFloat(3, 0.1, 50),
        ]);
    }

    /**
     * Create a product with a specific product code.
     */
    public function withProductCode(string $productCode): static
    {
        return $this->state(fn (array $attributes) => [
            'product_code' => $productCode,
        ]);
    }

    /**
     * Create a product with high profit margin.
     */
    public function highProfitMargin(): static
    {
        $price = fake()->randomFloat(2, 50, 200);
        $costPrice = $price * fake()->randomFloat(2, 0.2, 0.5); // 20-50% of price

        return $this->state(fn (array $attributes) => [
            'price' => $price,
            'cost_price' => $costPrice,
        ]);
    }

    /**
     * Create a product with low profit margin.
     */
    public function lowProfitMargin(): static
    {
        $price = fake()->randomFloat(2, 10, 100);
        $costPrice = $price * fake()->randomFloat(2, 0.7, 0.9); // 70-90% of price

        return $this->state(fn (array $attributes) => [
            'price' => $price,
            'cost_price' => $costPrice,
        ]);
    }
}
