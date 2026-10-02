// ═══════════════════════════════════════
// تشخيص أولي
// ═══════════════════════════════════════
console.log('✅ shop.js بدأ التنفيذ');

if (typeof Vue === 'undefined') {
    console.error('❌ Vue غير محمّل!');
    document.body.innerHTML = '<div style="padding:20px;font-family:sans-serif"><h2 style="color:red">خطأ: Vue لم يُحمّل</h2><p>تأكد من تحميل ملف Vue بشكل صحيح.</p></div>';
    throw new Error('Vue not loaded');
}

console.log('✅ Vue محمّل:', Vue.version);

const appEl = document.getElementById('app');
if (!appEl) {
    console.error('❌ عنصر #app غير موجود!');
    document.body.innerHTML = '<div style="padding:20px;font-family:sans-serif"><h2 style="color:red">خطأ: عنصر #app غير موجود في الصفحة</h2></div>';
    throw new Error('#app not found');
}

console.log('✅ عنصر #app موجود');

const { createApp, ref, reactive, computed, onMounted } = Vue;
const API_BASE = '/api/v1';

try {
    createApp({
        setup() {
            const products = ref([]);
            const loading = ref(false);
            const search = ref('');
            const sort = ref('newest');
            const locale = ref('ar');
            const showCart = ref(false);
            const toast = ref('');
            const cart = reactive({ items: [], item_count: 0, subtotal: 0 });
            const totals = reactive({ subtotal: 0, tax: 0, shipping: 0, total: 0 });

            const sessionId = localStorage.getItem('session_id') || 'guest_' + Date.now();
            localStorage.setItem('session_id', sessionId);
            const cartCount = computed(() => cart.item_count || 0);

            function productName(p) {
                if (!p) return '';
                return locale.value === 'ar' ? (p.name_ar || p.name) : (p.name_en || p.name);
            }

            async function api(path, options = {}) {
                const res = await fetch(API_BASE + path, {
                    ...options,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Session-ID': sessionId,
                        'Accept-Language': locale.value,
                        ...(options.headers || {}),
                    },
                });
                return res.json();
            }

            function formatPrice(v) {
                const num = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(v);
                return num + ' ر.ي';
            }

            function notify(msg) {
                toast.value = msg;
                setTimeout(() => toast.value = '', 2500);
            }

            async function loadProducts() {
                console.log('⏬ تحميل المنتجات...');
                loading.value = true;
                try {
                    const params = new URLSearchParams();
                    if (search.value) params.append('search', search.value);
                    if (sort.value) params.append('sort', sort.value);
                    params.append('per_page', 12);
                    const res = await api('/products?' + params.toString());
                    console.log('📦 الرد:', res);
                    if (res.success) products.value = res.data;
                } catch (e) {
                    console.error('❌ فشل تحميل المنتجات:', e);
                    notify('فشل تحميل المنتجات');
                } finally {
                    loading.value = false;
                }
            }

            async function loadCart() {
                try {
                    const res = await api('/cart');
                    if (res.success) {
                        Object.assign(cart, res.data);
                        Object.assign(totals, res.totals);
                    }
                } catch (e) { console.error('❌ فشل تحميل السلة:', e); }
            }

            async function addToCart(product) {
                try {
                    const res = await api('/cart/items', {
                        method: 'POST',
                        body: JSON.stringify({ product_id: product.id, quantity: 1 }),
                    });
                    if (res.success) {
                        Object.assign(cart, res.data);
                        Object.assign(totals, res.totals);
                        notify('✅ أضيف للسلة');
                    } else {
                        notify(res.message || 'فشل الإضافة');
                    }
                } catch (e) {
                    console.error(e);
                    notify('حدث خطأ');
                }
            }

            async function updateQty(item, quantity) {
                if (quantity < 0) return;
                try {
                    const res = await api('/cart/items/' + item.id, {
                        method: 'PATCH',
                        body: JSON.stringify({ quantity }),
                    });
                    if (res.success) {
                        Object.assign(cart, res.data);
                        Object.assign(totals, res.totals);
                    }
                } catch (e) { console.error(e); }
            }

            function toggleLang() {
                locale.value = locale.value === 'ar' ? 'en' : 'ar';
                document.documentElement.lang = locale.value;
                document.documentElement.dir = locale.value === 'ar' ? 'rtl' : 'ltr';
                loadProducts();
            }

            onMounted(() => {
                console.log('✅ Vue mounted!');
                loadProducts();
                loadCart();
            });

            return {
                products, loading, search, sort, locale,
                cart, totals, cartCount, showCart, toast,
                loadProducts, loadCart, addToCart, updateQty,
                formatPrice, toggleLang, productName,
            };
        },
    }).mount('#app');
    console.log('✅ Vue app mounted بنجاح');
} catch (e) {
    console.error('❌ خطأ في Vue:', e);
    document.body.innerHTML = '<div style="padding:20px;font-family:sans-serif"><h2 style="color:red">خطأ في Vue:</h2><pre>' + e.message + '</pre></div>';
}
