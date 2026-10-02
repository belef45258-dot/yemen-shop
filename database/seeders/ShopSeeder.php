<?php

namespace Database\Seeders;

use App\Models\{Category, Product, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        /* ─────── الأقسام ─────── */
        $electronics = Category::create([
            'slug' => 'electronics',
            'name_ar' => 'إلكترونيات',
            'name_en' => 'Electronics',
            'description_ar' => 'أحدث الأجهزة الإلكترونية',
            'description_en' => 'Latest electronic devices',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $phones = Category::create([
            'parent_id' => $electronics->id,
            'slug' => 'smartphones',
            'name_ar' => 'هواتف ذكية',
            'name_en' => 'Smartphones',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $clothing = Category::create([
            'slug' => 'clothing',
            'name_ar' => 'ملابس',
            'name_en' => 'Clothing',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        /* ─────── المنتجات ─────── */
        $productsData = [
            [
                'category_id' => $phones->id,
                'sku' => 'IPHONE-15',
                'slug' => 'iphone-15',
                'name_ar' => 'آيفون 15 برو',
                'name_en' => 'iPhone 15 Pro',
                'description_ar' => 'آيفون 15 برو بشاشة 6.1 بوصة ومعالج A17 Pro',
                'description_en' => 'iPhone 15 Pro with 6.1 inch display and A17 Pro chip',
                'price' => 750000,
                'sale_price' => 699000,
                'stock' => 15,
                'is_featured' => true,
            ],
            [
                'category_id' => $phones->id,
                'sku' => 'SAMSUNG-S24',
                'slug' => 'samsung-galaxy-s24',
                'name_ar' => 'سامسونج جالكسي S24',
                'name_en' => 'Samsung Galaxy S24',
                'description_ar' => 'هاتف سامسونج جالكسي S24 بكاميرا 50 ميجابكسل',
                'description_en' => 'Samsung Galaxy S24 with 50MP camera',
                'price' => 620000,
                'sale_price' => null,
                'stock' => 8,
                'is_featured' => true,
            ],
            [
                'category_id' => $phones->id,
                'sku' => 'XIAOMI-14',
                'slug' => 'xiaomi-14',
                'name_ar' => 'شاومي 14',
                'name_en' => 'Xiaomi 14',
                'description_ar' => 'شاومي 14 مع كاميرا لايكا',
                'description_en' => 'Xiaomi 14 with Leica camera',
                'price' => 480000,
                'sale_price' => 450000,
                'stock' => 20,
                'is_featured' => false,
            ],
            [
                'category_id' => $clothing->id,
                'sku' => 'TSHIRT-001',
                'slug' => 'classic-tshirt',
                'name_ar' => 'تيشيرت كلاسيكي',
                'name_en' => 'Classic T-Shirt',
                'description_ar' => 'تيشيرت قطني 100% بألوان متعددة',
                'description_en' => '100% cotton t-shirt in multiple colors',
                'price' => 8000,
                'sale_price' => 6500,
                'stock' => 100,
                'is_featured' => true,
            ],
            [
                'category_id' => $clothing->id,
                'sku' => 'JACKET-001',
                'slug' => 'winter-jacket',
                'name_ar' => 'جاكيت شتوي',
                'name_en' => 'Winter Jacket',
                'description_ar' => 'جاكيت شتوي دافئ ومقاوم للماء',
                'description_en' => 'Warm and waterproof winter jacket',
                'price' => 25000,
                'sale_price' => null,
                'stock' => 30,
                'is_featured' => false,
            ],
        ];

        foreach ($productsData as $data) {
            Product::create(array_merge($data, [
                'currency' => 'YER',
                'is_active' => true,
                'low_stock_alert' => 5,
            ]));
        }

        /* ─────── المستخدمون ─────── */
        User::create([
            'name' => 'المدير',
            'email' => 'admin@shop.test',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'locale' => 'ar',
        ]);

        User::create([
            'name' => 'عميل تجريبي',
            'email' => 'customer@shop.test',
            'password' => Hash::make('customer123'),
            'phone' => '+967777000000',
            'role' => 'customer',
            'locale' => 'ar',
        ]);

        $this->command->info('✅ تم إنشاء:');
        $this->command->info('   • 3 أقسام');
        $this->command->info('   • 5 منتجات');
        $this->command->info('   • 2 مستخدم');
    }
}
