<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create root categories for grocery store
        $freshProduce = Category::factory()->withName('Fresh Produce')->sortOrder(1)->create();
        $meatSeafood = Category::factory()->withName('Meat & Seafood')->sortOrder(2)->create();
        $dairyEggs = Category::factory()->withName('Dairy & Eggs')->sortOrder(3)->create();
        $bakery = Category::factory()->withName('Bakery')->sortOrder(4)->create();
        $pantry = Category::factory()->withName('Pantry Staples')->sortOrder(5)->create();
        $frozen = Category::factory()->withName('Frozen Foods')->sortOrder(6)->create();
        $beverages = Category::factory()->withName('Beverages')->sortOrder(7)->create();
        $snacks = Category::factory()->withName('Snacks & Confectionery')->sortOrder(8)->create();
        $household = Category::factory()->withName('Household & Cleaning')->sortOrder(9)->create();
        $personalCare = Category::factory()->withName('Personal Care')->sortOrder(10)->create();
        $baby = Category::factory()->withName('Baby Products')->sortOrder(11)->create();
        $pet = Category::factory()->withName('Pet Supplies')->sortOrder(12)->create();

        // Create subcategories for Fresh Produce
        Category::factory()->withName('Fruits')->withParent($freshProduce)->sortOrder(1)->create();
        Category::factory()->withName('Vegetables')->withParent($freshProduce)->sortOrder(2)->create();
        Category::factory()->withName('Herbs & Spices')->withParent($freshProduce)->sortOrder(3)->create();
        Category::factory()->withName('Salad Greens')->withParent($freshProduce)->sortOrder(4)->create();

        // Create subcategories for Meat & Seafood
        Category::factory()->withName('Beef')->withParent($meatSeafood)->sortOrder(1)->create();
        Category::factory()->withName('Poultry')->withParent($meatSeafood)->sortOrder(2)->create();
        Category::factory()->withName('Pork')->withParent($meatSeafood)->sortOrder(3)->create();
        Category::factory()->withName('Seafood')->withParent($meatSeafood)->sortOrder(4)->create();
        Category::factory()->withName('Deli Meats')->withParent($meatSeafood)->sortOrder(5)->create();

        // Create subcategories for Dairy & Eggs
        Category::factory()->withName('Milk & Cream')->withParent($dairyEggs)->sortOrder(1)->create();
        Category::factory()->withName('Cheese')->withParent($dairyEggs)->sortOrder(2)->create();
        Category::factory()->withName('Yogurt')->withParent($dairyEggs)->sortOrder(3)->create();
        Category::factory()->withName('Butter & Margarine')->withParent($dairyEggs)->sortOrder(4)->create();
        Category::factory()->withName('Eggs')->withParent($dairyEggs)->sortOrder(5)->create();

        // Create subcategories for Bakery
        Category::factory()->withName('Bread & Rolls')->withParent($bakery)->sortOrder(1)->create();
        Category::factory()->withName('Pastries & Cakes')->withParent($bakery)->sortOrder(2)->create();
        Category::factory()->withName('Cookies & Biscuits')->withParent($bakery)->sortOrder(3)->create();

        // Create subcategories for Pantry Staples
        Category::factory()->withName('Cereals & Breakfast')->withParent($pantry)->sortOrder(1)->create();
        Category::factory()->withName('Pasta & Rice')->withParent($pantry)->sortOrder(2)->create();
        Category::factory()->withName('Canned Goods')->withParent($pantry)->sortOrder(3)->create();
        Category::factory()->withName('Condiments & Sauces')->withParent($pantry)->sortOrder(4)->create();
        Category::factory()->withName('Spices & Seasonings')->withParent($pantry)->sortOrder(5)->create();
        Category::factory()->withName('Oils & Vinegars')->withParent($pantry)->sortOrder(6)->create();
        Category::factory()->withName('Flour & Baking')->withParent($pantry)->sortOrder(7)->create();

        // Create subcategories for Frozen Foods
        Category::factory()->withName('Frozen Vegetables')->withParent($frozen)->sortOrder(1)->create();
        Category::factory()->withName('Frozen Fruits')->withParent($frozen)->sortOrder(2)->create();
        Category::factory()->withName('Frozen Meals')->withParent($frozen)->sortOrder(3)->create();
        Category::factory()->withName('Ice Cream & Desserts')->withParent($frozen)->sortOrder(4)->create();

        // Create subcategories for Beverages
        Category::factory()->withName('Soft Drinks')->withParent($beverages)->sortOrder(1)->create();
        Category::factory()->withName('Juices')->withParent($beverages)->sortOrder(2)->create();
        Category::factory()->withName('Coffee & Tea')->withParent($beverages)->sortOrder(3)->create();
        Category::factory()->withName('Water')->withParent($beverages)->sortOrder(4)->create();
        Category::factory()->withName('Energy Drinks')->withParent($beverages)->sortOrder(5)->create();

        // Create subcategories for Snacks & Confectionery
        Category::factory()->withName('Chips & Crackers')->withParent($snacks)->sortOrder(1)->create();
        Category::factory()->withName('Candy & Chocolate')->withParent($snacks)->sortOrder(2)->create();
        Category::factory()->withName('Nuts & Dried Fruits')->withParent($snacks)->sortOrder(3)->create();
        Category::factory()->withName('Popcorn')->withParent($snacks)->sortOrder(4)->create();

        // Create subcategories for Household & Cleaning
        Category::factory()->withName('Cleaning Supplies')->withParent($household)->sortOrder(1)->create();
        Category::factory()->withName('Paper Products')->withParent($household)->sortOrder(2)->create();
        Category::factory()->withName('Laundry')->withParent($household)->sortOrder(3)->create();
        Category::factory()->withName('Trash Bags')->withParent($household)->sortOrder(4)->create();

        // Create subcategories for Personal Care
        Category::factory()->withName('Skin Care')->withParent($personalCare)->sortOrder(1)->create();
        Category::factory()->withName('Hair Care')->withParent($personalCare)->sortOrder(2)->create();
        Category::factory()->withName('Oral Care')->withParent($personalCare)->sortOrder(3)->create();
        Category::factory()->withName('Body Care')->withParent($personalCare)->sortOrder(4)->create();
        Category::factory()->withName('First Aid')->withParent($personalCare)->sortOrder(5)->create();

        // Create subcategories for Baby Products
        Category::factory()->withName('Baby Food')->withParent($baby)->sortOrder(1)->create();
        Category::factory()->withName('Diapers & Wipes')->withParent($baby)->sortOrder(2)->create();
        Category::factory()->withName('Baby Care')->withParent($baby)->sortOrder(3)->create();

        // Create subcategories for Pet Supplies
        Category::factory()->withName('Dog Food')->withParent($pet)->sortOrder(1)->create();
        Category::factory()->withName('Cat Food')->withParent($pet)->sortOrder(2)->create();
        Category::factory()->withName('Pet Treats')->withParent($pet)->sortOrder(3)->create();
        Category::factory()->withName('Pet Accessories')->withParent($pet)->sortOrder(4)->create();

        // Create an inactive category for testing
        Category::factory()->withName('Discontinued Items')->inactive()->sortOrder(99)->create();
    }
}
