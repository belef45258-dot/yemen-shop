<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>متجري اليمني</title>

    {{-- Tailwind محلي --}}
    <script src="{{ asset('vendor/tailwind.js') }}"></script>
    <script>
        if (window.tailwind) {
            tailwind.config = {
                theme: {
                    extend: {
                        colors: { primary: { DEFAULT: '#6366f1', dark: '#4f46e5' } },
                    },
                },
            };
        }
    </script>

    <style>
        
        body { font-family: system-ui, -apple-system, sans-serif; }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

<div id="app">

    <header class="sticky top-0 z-50 bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <a href="/" class="text-2xl font-black text-primary">🛍️ متجري</a>
            <div class="flex-1 max-w-md hidden md:block">
                <input v-model="search" @keyup.enter="loadProducts" type="text"
                    placeholder="ابحث عن منتج..."
                    class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
            </div>
            <div class="flex items-center gap-2">
                <button @click="toggleLang" class="px-3 py-2 rounded-lg hover:bg-gray-100 text-sm font-semibold">
                    @{{ locale === 'ar' ? 'EN' : 'ع' }}
                </button>
                <button @click="showCart = true" class="relative px-3 py-2 rounded-lg hover:bg-gray-100">
                    🛒
                    <span v-if="cartCount > 0"
                        class="absolute -top-1 -left-1 bg-primary text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        @{{ cartCount }}
                    </span>
                </button>
            </div>
        </div>
    </header>

    <section class="bg-gradient-to-l from-primary to-indigo-500 text-white py-12">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-black mb-3">أهلاً بك في متجرنا</h1>
            <p class="text-lg opacity-90">تسوّق أفضل المنتجات بأفضل الأسعار</p>
        </div>
    </section>

    <section class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">
                @{{ search ? 'نتائج البحث: ' + search : 'المنتجات' }}
            </h2>
            <select v-model="sort" @change="loadProducts"
                class="px-3 py-2 rounded-lg border border-gray-200 focus:border-primary outline-none">
                <option value="newest">الأحدث</option>
                <option value="price_asc">الأرخص أولًا</option>
                <option value="price_desc">الأغلى أولًا</option>
            </select>
        </div>

        <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="i in 8" :key="i" class="h-80 bg-gray-200 rounded-lg animate-pulse"></div>
        </div>

        <div v-else-if="products.length" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <article v-for="p in products" :key="p.id"
                class="bg-white rounded-lg shadow-sm hover:shadow-lg transition overflow-hidden group">
                <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                    <img v-if="p.images && p.images.length" :src="p.images[0].url" :alt="productName(p)"
                        class="w-full h-full object-cover group-hover:scale-105 transition">
                    <div v-else class="w-full h-full flex items-center justify-center text-4xl text-gray-400">📦</div>
                    <div v-if="p.is_on_sale"
                        class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                        -@{{ p.discount_percentage }}%
                    </div>
                </div>
                <div class="p-3">
                    <h3 class="font-semibold mb-1 h-12 text-sm line-clamp-2">@{{ productName(p) }}</h3>
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-lg font-black text-primary">@{{ formatPrice(p.final_price) }}</span>
                        <span v-if="p.is_on_sale" class="text-xs text-gray-400 line-through">
                            @{{ formatPrice(p.price) }}
                        </span>
                    </div>
                    <button @click="addToCart(p)" :disabled="!p.in_stock"
                        :class="p.in_stock ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500 cursor-not-allowed'"
                        class="w-full py-2 rounded-lg font-semibold text-sm transition">
                        @{{ p.in_stock ? '🛒 أضف للسلة' : 'نفدت الكمية' }}
                    </button>
                </div>
            </article>
        </div>

        <div v-else class="text-center py-16">
            <div class="text-6xl mb-4">🔍</div>
            <p class="text-gray-500">لا توجد منتجات مطابقة</p>
        </div>
    </section>

    <div v-if="showCart" @click.self="showCart = false"
        class="fixed inset-0 bg-black/50 z-50 flex justify-end">
        <div class="bg-white w-full max-w-md h-full overflow-y-auto flex flex-col mr-auto">
            <div class="p-4 border-b flex items-center justify-between sticky top-0 bg-white">
                <h3 class="text-xl font-bold">🛒 السلة</h3>
                <button @click="showCart = false" class="text-2xl text-gray-400">×</button>
            </div>
            <div class="flex-1 p-4">
                <div v-if="!cart.items || !cart.items.length" class="text-center py-12">
                    <div class="text-5xl mb-3">🛒</div>
                    <p class="text-gray-500">السلة فارغة</p>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="item in cart.items" :key="item.id" class="flex gap-3 border-b pb-3">
                        <div class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center text-2xl">📦</div>
                        <div class="flex-1">
                            <div class="font-semibold text-sm mb-1">@{{ productName(item.product) }}</div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center border rounded-lg">
                                    <button @click="updateQty(item, item.quantity - 1)" class="px-2 py-1">−</button>
                                    <span class="px-3 text-sm">@{{ item.quantity }}</span>
                                    <button @click="updateQty(item, item.quantity + 1)" class="px-2 py-1">+</button>
                                </div>
                                <div class="text-primary font-bold text-sm">@{{ formatPrice(item.line_total) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="cart.items && cart.items.length" class="p-4 border-t bg-gray-50 space-y-2">
                <div class="flex justify-between text-sm">
                    <span>المجموع:</span>
                    <span class="font-semibold">@{{ formatPrice(totals.subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>الشحن:</span>
                    <span class="font-semibold">@{{ totals.shipping ? formatPrice(totals.shipping) : 'مجاني' }}</span>
                </div>
                <div class="flex justify-between text-lg font-black border-t pt-2">
                    <span>الإجمالي:</span>
                    <span class="text-primary">@{{ formatPrice(totals.total) }}</span>
                </div>
                <button class="w-full bg-primary text-white py-3 rounded-lg font-bold mt-2">
                    إتمام الطلب ←
                </button>
            </div>
        </div>
    </div>

    <div v-if="toast"
        class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-6 py-3 rounded-lg shadow-xl z-50">
        @{{ toast }}
    </div>

    <footer class="bg-gray-900 text-white py-8 mt-16">
        <div class="container mx-auto px-4 text-center">
            <p class="mb-2">© 2026 متجري اليمني</p>
            <p class="text-sm text-gray-400">صنع في اليمن بكل ❤️</p>
        </div>
    </footer>

</div>

{{-- Vue محلي --}}
<script src="{{ asset('vendor/vue.global.prod.js') }}"></script>
<script src="{{ asset('js/shop.js') }}"></script>
</body>
</html>
