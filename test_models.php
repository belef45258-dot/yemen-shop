<?php
use App\Models\{Product, Category, Order, Payment};

$c = Category::first();
if (!$c) {
    echo "لا توجد أقسام. تم إنشاء قسم جديد...\n";
    $c = Category::create([
        'slug' => 'electronics',
        'name_ar' => 'إلكترونيات',
        'name_en' => 'Electronics',
    ]);
}

$p = Product::first();
echo "Category: " . $c->name_ar . "\n";
if ($p) {
    echo "Product: " . $p->name_ar . "\n";
    echo "Final Price: " . $p->final_price . "\n";
    echo "Discount: " . $p->discount_percentage . "%\n";
    echo "On Sale: " . ($p->is_on_sale ? 'Yes' : 'No') . "\n";
    echo "In Stock: " . ($p->is_in_stock ? 'Yes' : 'No') . "\n";
    app()->setLocale('en');
    echo "English: " . $p->name . "\n";
    app()->setLocale('ar');
    echo "Relation: " . $p->category->name_ar . "\n";
} else {
    echo "لا يوجد منتج، أنشئ واحدًا لاحقًا.\n";
}

echo "\nCounts:\n";
echo "  Categories: " . Category::count() . "\n";
echo "  Products: " . Product::count() . "\n";
echo "  Orders: " . Order::count() . "\n";
echo "  Payments: " . Payment::count() . "\n";
