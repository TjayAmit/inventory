<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only run in local and staging environments
        if (!in_array(App::environment(), ['local', 'staging'])) {
            $this->command->warn('ProductSeeder skipped: Only runs in local and staging environments.');
            return;
        }

        $this->command->info('Seeding grocery store products...');

        // Get categories by name
        $freshProduce = Category::where('name', 'Fresh Produce')->first();
        $meatSeafood = Category::where('name', 'Meat & Seafood')->first();
        $dairyEggs = Category::where('name', 'Dairy & Eggs')->first();
        $bakery = Category::where('name', 'Bakery')->first();
        $pantry = Category::where('name', 'Pantry Staples')->first();
        $frozen = Category::where('name', 'Frozen Foods')->first();
        $beverages = Category::where('name', 'Beverages')->first();
        $snacks = Category::where('name', 'Snacks & Confectionery')->first();
        $household = Category::where('name', 'Household & Cleaning')->first();
        $personalCare = Category::where('name', 'Personal Care')->first();
        $baby = Category::where('name', 'Baby Products')->first();
        $pet = Category::where('name', 'Pet Supplies')->first();

        // Get subcategories
        $fruits = Category::where('name', 'Fruits')->where('parent_id', $freshProduce->id)->first();
        $vegetables = Category::where('name', 'Vegetables')->where('parent_id', $freshProduce->id)->first();
        $beef = Category::where('name', 'Beef')->where('parent_id', $meatSeafood->id)->first();
        $poultry = Category::where('name', 'Poultry')->where('parent_id', $meatSeafood->id)->first();
        $seafood = Category::where('name', 'Seafood')->where('parent_id', $meatSeafood->id)->first();
        $milkCream = Category::where('name', 'Milk & Cream')->where('parent_id', $dairyEggs->id)->first();
        $cheese = Category::where('name', 'Cheese')->where('parent_id', $dairyEggs->id)->first();
        $bread = Category::where('name', 'Bread & Rolls')->where('parent_id', $bakery->id)->first();
        $cannedGoods = Category::where('name', 'Canned Goods')->where('parent_id', $pantry->id)->first();
        $softDrinks = Category::where('name', 'Soft Drinks')->where('parent_id', $beverages->id)->first();
        $chips = Category::where('name', 'Chips & Crackers')->where('parent_id', $snacks->id)->first();

        // Fresh Produce Products
        $this->createProduct('Fresh Apples', 'Red Delicious Apples, 1kg', 120.00, 85.00, $fruits, 'Del Monte Philippines', 'kg');
        $this->createProduct('Fresh Bananas', 'Cavendish Bananas, 1kg', 65.00, 45.00, $fruits, 'La Trinidad', 'kg');
        $this->createProduct('Fresh Oranges', 'Navel Oranges, 1kg', 95.00, 65.00, $fruits, 'Dole Philippines', 'kg');
        $this->createProduct('Fresh Carrots', 'Baby Carrots, 500g', 55.00, 38.00, $vegetables, 'Benguet', 'kg');
        $this->createProduct('Fresh Tomatoes', 'Roma Tomatoes, 1kg', 80.00, 55.00, $vegetables, 'Baguio', 'kg');
        $this->createProduct('Fresh Potatoes', 'Russet Potatoes, 2kg', 75.00, 52.00, $vegetables, 'Benguet', 'kg');
        $this->createProduct('Fresh Onions', 'Yellow Onions, 1kg', 55.00, 38.00, $vegetables, 'Nueva Ecija', 'kg');
        $this->createProduct('Fresh Lettuce', 'Iceberg Lettuce, 1 head', 45.00, 31.00, $vegetables, 'Benguet', 'pcs');

        // Meat & Seafood Products
        $this->createProduct('Beef Steak', 'Prime Ribeye Steak, 500g', 550.00, 380.00, $beef, 'Monterey Meat Shop', 'kg');
        $this->createProduct('Ground Beef', 'Lean Ground Beef, 500g', 350.00, 240.00, $beef, 'Monterey Meat Shop', 'kg');
        $this->createProduct('Chicken Breast', 'Boneless Chicken Breast, 1kg', 240.00, 165.00, $poultry, 'Magnolia Chicken', 'kg');
        $this->createProduct('Chicken Thighs', 'Chicken Thighs, 1kg', 210.00, 145.00, $poultry, 'Magnolia Chicken', 'kg');
        $this->createProduct('Whole Chicken', 'Whole Roaster Chicken, 1.5kg', 295.00, 205.00, $poultry, 'Magnolia Chicken', 'pcs');
        $this->createProduct('Salmon Fillet', 'Atlantic Salmon Fillet, 500g', 450.00, 310.00, $seafood, 'Puregold', 'kg');
        $this->createProduct('Shrimp', 'Peeled Shrimp, 500g', 380.00, 265.00, $seafood, 'Puregold', 'kg');
        $this->createProduct('Tilapia', 'Whole Tilapia, 500g', 180.00, 125.00, $seafood, 'Local Fish Port', 'pcs');

        // Dairy & Eggs Products
        $this->createProduct('Whole Milk', 'Fresh Whole Milk, 1L', 95.00, 65.00, $milkCream, 'Magnolia', 'ltr');
        $this->createProduct('Low Fat Milk', 'Low Fat Milk, 1L', 95.00, 65.00, $milkCream, 'Magnolia', 'ltr');
        $this->createProduct('Cheddar Cheese', 'Aged Cheddar Cheese, 200g', 155.00, 108.00, $cheese, 'Magnolia', 'kg');
        $this->createProduct('Mozzarella Cheese', 'Shredded Mozzarella, 200g', 145.00, 100.00, $cheese, 'Magnolia', 'kg');
        $this->createProduct('Greek Yogurt', 'Greek Yogurt, 500g', 180.00, 125.00, $dairyEggs, 'Magnolia', 'kg');
        $this->createProduct('Butter', 'Unsalted Butter, 250g', 135.00, 92.00, $dairyEggs, 'Magnolia', 'kg');
        $this->createProduct('Eggs', 'Large Eggs, 12 pcs', 105.00, 72.00, $dairyEggs, 'Margarita', 'pcs');

        // Bakery Products
        $this->createProduct('White Bread', 'Sliced White Bread, 500g', 60.00, 38.00, $bread, 'Gardenia', 'pcs');
        $this->createProduct('Whole Wheat Bread', 'Whole Wheat Bread, 500g', 68.00, 44.00, $bread, 'Gardenia', 'pcs');
        $this->createProduct('Pandesal', 'Filipino Bread Rolls, 10 pcs', 45.00, 29.00, $bakery, 'Julies Bakeshop', 'pcs');
        $this->createProduct('Ensaymada', 'Filipino Sweet Bun, 4 pcs', 95.00, 62.00, $bakery, 'Red Ribbon', 'pcs');

        // Pantry Staples Products
        $this->createProduct('Canned Tomatoes', 'Canned Tomato Sauce, 400g', 42.00, 26.00, $cannedGoods, 'Del Monte Philippines', 'pcs');
        $this->createProduct('Canned Corn', 'Canned Sweet Corn, 400g', 40.00, 25.00, $cannedGoods, 'Del Monte Philippines', 'pcs');
        $this->createProduct('Canned Beans', 'Canned Kidney Beans, 400g', 36.00, 23.00, $cannedGoods, 'Universal Robina', 'pcs');
        $this->createProduct('Pasta', 'Spaghetti Pasta, 500g', 55.00, 34.00, $pantry, 'Mama Sita', 'pcs');
        $this->createProduct('Rice', 'Premium Sinandomeng Rice, 5kg', 350.00, 225.00, $pantry, 'Datu Puti', 'kg');
        $this->createProduct('Cooking Oil', 'Vegetable Oil, 1L', 110.00, 70.00, $pantry, 'Angel', 'ltr');
        $this->createProduct('Sugar', 'White Sugar, 1kg', 65.00, 42.00, $pantry, 'Premier', 'kg');
        $this->createProduct('Salt', 'Iodized Salt, 500g', 30.00, 18.00, $pantry, 'Morton Philippines', 'kg');
        $this->createProduct('Coffee', 'Instant Coffee, 100g', 180.00, 115.00, $pantry, 'Nescafe Philippines', 'kg');
        $this->createProduct('Tea', 'Black Tea Bags, 25 pcs', 105.00, 65.00, $pantry, 'Lipton Philippines', 'pcs');

        // Frozen Foods Products
        $this->createProduct('Frozen Peas', 'Frozen Green Peas, 500g', 65.00, 42.00, $frozen, 'Magnolia', 'kg');
        $this->createProduct('Frozen Corn', 'Frozen Sweet Corn, 500g', 65.00, 42.00, $frozen, 'Magnolia', 'kg');
        $this->createProduct('Frozen Pizza', 'Pepperoni Pizza, 400g', 220.00, 140.00, $frozen, 'Magnolia', 'pcs');
        $this->createProduct('Ice Cream', 'Ube Ice Cream, 1L', 240.00, 155.00, $frozen, 'Magnolia', 'ltr');
        $this->createProduct('Frozen Dinners', 'Chicken Adobo, 400g', 175.00, 110.00, $frozen, 'Magnolia', 'pcs');

        // Beverages Products
        $this->createProduct('Cola', 'Coca-Cola Soft Drink, 1.5L', 72.00, 45.00, $softDrinks, 'Coca-Cola Philippines', 'ltr');
        $this->createProduct('Orange Soda', 'Royal Tru-Orange, 1.5L', 72.00, 45.00, $softDrinks, 'Coca-Cola Philippines', 'ltr');
        $this->createProduct('Lemon Soda', 'Sprite Soft Drink, 1.5L', 72.00, 45.00, $softDrinks, 'Coca-Cola Philippines', 'ltr');
        $this->createProduct('Bottled Water', 'Mineral Water, 1L', 25.00, 15.00, $beverages, 'Wilkins', 'ltr');
        $this->createProduct('Orange Juice', '100% Orange Juice, 1L', 120.00, 78.00, $beverages, 'Del Monte Philippines', 'ltr');
        $this->createProduct('Apple Juice', 'Apple Juice, 1L', 108.00, 70.00, $beverages, 'Minute Maid Philippines', 'ltr');
        $this->createProduct('Energy Drink', 'Cobra Energy Drink, 250ml', 60.00, 38.00, $beverages, 'Asia Brewery', 'ltr');

        // Snacks & Confectionery Products
        $this->createProduct('Potato Chips', 'Classic Potato Chips, 150g', 72.00, 45.00, $chips, 'Jack n Jill', 'pcs');
        $this->createProduct('Corn Chips', 'Corn Chips, 200g', 66.00, 42.00, $chips, 'Oishi', 'pcs');
        $this->createProduct('Chocolate Bar', 'Milk Chocolate Bar, 100g', 60.00, 36.00, $snacks, 'Cadbury Philippines', 'pcs');
        $this->createProduct('Cookies', 'Chocolate Chip Cookies, 200g', 85.00, 54.00, $snacks, 'Oreo Philippines', 'pcs');
        $this->createProduct('Mixed Nuts', 'Mixed Nuts, 200g', 180.00, 115.00, $snacks, 'Planters Philippines', 'kg');

        // Household & Cleaning Products
        $this->createProduct('Dish Soap', 'Liquid Dish Soap, 750ml', 95.00, 60.00, $household, 'Joy Philippines', 'ltr');
        $this->createProduct('Laundry Detergent', 'Liquid Laundry Detergent, 1.5L', 300.00, 190.00, $household, 'Surf Philippines', 'ltr');
        $this->createProduct('All-Purpose Cleaner', 'All-Purpose Cleaner, 1L', 155.00, 95.00, $household, 'Zonrox', 'ltr');
        $this->createProduct('Paper Towels', 'Paper Towels, 6 rolls', 180.00, 115.00, $household, 'Tissue', 'pcs');
        $this->createProduct('Trash Bags', 'Trash Bags, 30 pcs', 108.00, 65.00, $household, 'Clever', 'pcs');

        // Personal Care Products
        $this->createProduct('Shampoo', 'Shampoo, 400ml', 180.00, 115.00, $personalCare, 'Creamsilk', 'ltr');
        $this->createProduct('Conditioner', 'Conditioner, 400ml', 180.00, 115.00, $personalCare, 'Creamsilk', 'ltr');
        $this->createProduct('Toothpaste', 'Toothpaste, 100ml', 95.00, 60.00, $personalCare, 'Colgate Philippines', 'pcs');
        $this->createProduct('Body Wash', 'Body Wash, 500ml', 155.00, 98.00, $personalCare, 'Palmolive Philippines', 'ltr');
        $this->createProduct('Hand Soap', 'Hand Soap, 300ml', 72.00, 45.00, $personalCare, 'Safeguard Philippines', 'ltr');

        // Baby Products
        $this->createProduct('Baby Formula', 'Infant Formula, 400g', 520.00, 350.00, $baby, 'Nestle Philippines', 'kg');
        $this->createProduct('Diapers', 'Baby Diapers, Size M, 30 pcs', 410.00, 260.00, $baby, 'Pampers Philippines', 'pcs');
        $this->createProduct('Baby Wipes', 'Baby Wipes, 80 pcs', 175.00, 110.00, $baby, 'Huggies Philippines', 'pcs');

        // Pet Supplies
        $this->createProduct('Dog Food', 'Dog Food, 5kg', 650.00, 415.00, $pet, 'Pedigree Philippines', 'kg');
        $this->createProduct('Cat Food', 'Cat Food, 3kg', 475.00, 300.00, $pet, 'Whiskas Philippines', 'kg');
        $this->createProduct('Dog Treats', 'Dog Treats, 500g', 180.00, 115.00, $pet, 'Pedigree Philippines', 'kg');
        $this->createProduct('Cat Treats', 'Cat Treats, 100g', 105.00, 65.00, $pet, 'Whiskas Philippines', 'kg');

        // Create some inactive products for testing
        $this->createProduct('Discontinued Soda', 'Old Soda Flavor, 1L', 60.00, 38.00, $softDrinks, 'Generic', 'ltr', false);

        $this->command->info('Grocery store products seeded successfully.');
    }

    /**
     * Create a product with proper barcode.
     */
    private function createProduct(
        string $name,
        string $description,
        float $price,
        float $costPrice,
        ?Category $category,
        ?string $brand = null,
        string $unit = 'pcs',
        bool $isActive = true
    ): void {
        Product::create([
            'name' => $name,
            'product_code' => 'PRD' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'barcode' => Product::generateDummyBarcode(),
            'description' => $description,
            'price' => $price,
            'cost_price' => $costPrice,
            'category_id' => $category?->id,
            'is_active' => $isActive,
            'is_taxable' => true,
            'unit' => $unit,
            'brand' => $brand,
            'supplier' => $brand ?? 'Generic Supplier',
            'reorder_point' => mt_rand(5, 20),
            'max_stock' => mt_rand(100, 500),
        ]);
    }
}
