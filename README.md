# Laravel 13 Modular Monolith

تطبيق Laravel 13 مبني بنمط **Modular Monolith**: نشر واحد (Deployment واحد وقاعدة بيانات واحدة)،
لكن الكود مقسّم إلى **وحدات (Modules)** مستقلة لكل قدرة عمل، ولكل وحدة حدود واضحة لا يجوز تجاوزها.

> المصادر التي استندنا إليها موجودة في [`docs/SOURCES.md`](docs/SOURCES.md).

## المتطلبات

- PHP **8.3** أو أعلى (مطلوب من Laravel 13)
- Composer 2
- Node.js 20+ (اختياري، لواجهات Vite)

## التشغيل

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

ثم جرّب:

```bash
curl http://localhost:8000/api/catalog/products

curl -X POST http://localhost:8000/api/orders \
     -H "Content-Type: application/json" \
     -d '{"product_id": 1, "quantity": 2}'
```

> ملاحظة: الجدول `catalog_products` فارغ في البداية. أضف منتجاً عبر `tinker`:
> `php artisan tinker` ثم `app(\Modules\Catalog\Contracts\ProductCatalog::class)->create('Coffee', 1500, 10);`

## الاختبارات

```bash
php artisan test                 # كل الاختبارات
php artisan test --testsuite=Architecture   # فحص حدود الوحدات فقط
php artisan test --testsuite=Modules        # اختبارات الوحدات
```

## هيكل المشروع

```text
Modules/
├── Catalog/                       # وحدة المنتجات (تملك جدول catalog_products)
│   ├── Contracts/                 # الواجهة العامة للوحدة: الوحدات الأخرى تستخدم هذا فقط
│   │   ├── ProductCatalog.php     # interface
│   │   ├── ProductData.php        # DTO غير قابل للتعديل يعبر الحدود
│   │   └── Exceptions/            # أخطاء عامة يمكن للوحدات الأخرى التقاطها
│   ├── Services/                  # التنفيذ الداخلي (Eloquent)
│   ├── Models/                    # داخلي
│   ├── Http/Controllers/          # داخلي
│   ├── Database/Migrations/       # جداول الوحدة فقط
│   ├── Routes/api.php
│   ├── Providers/CatalogServiceProvider.php
│   └── Tests/Feature/
└── Orders/                        # وحدة الطلبات (تملك جدول orders)
    ├── Contracts/Events/OrderPlaced.php   # حدث عام تستمع إليه الوحدات الأخرى
    ├── Actions/PlaceOrder.php             # منطق العمل
    ├── Models/ Http/ Database/ Routes/ Providers/ Tests/
app/
├── Support/ModuleRegistry.php     # يحوّل config/modules.php إلى Service Providers
└── Providers/AppServiceProvider.php
config/modules.php                 # قائمة الوحدات المفعّلة
tests/Architecture/ModuleBoundariesTest.php   # يفرض قواعد الحدود بين الوحدات
docs/SOURCES.md
```

## قواعد الحدود (Boundaries)

هذه القواعد هي جوهر النمط، وتفرضها `tests/Architecture/ModuleBoundariesTest.php` في كل تشغيل:

1. **الوحدة تملك بياناتها.** لا تكتب وحدة في جداول وحدة أخرى، ولا تنشئ مفاتيح أجنبية (Foreign Keys) نحو جداولها.
   مثال: جدول `orders` يخزن `product_id` بدون `FOREIGN KEY` نحو `catalog_products`.
2. **التواصل عبر `Contracts` فقط.** أي استيراد لوحدة أخرى يجب أن يكون من `Modules\<Other>\Contracts\...`.
   لا يُسمح باستيراد `Models` أو `Services` أو `Http` لوحدة أخرى.
3. **العقود لا تعتمد على التنفيذ.** ملفات `Contracts` لا تستورد داخليات وحدتها.
4. **الحدود تُنقل بـ DTOs، لا بنماذج Eloquent.** `ProductData` يعبر من Catalog إلى Orders، و`Product` (Model) لا يخرج أبداً من الوحدة.
5. **الأحداث تصف حقائق حدثت.** `OrderPlaced` يُطلق بعد انتهاء المعاملة (transaction) وليس داخلها.
6. **لا توجد مكتبة `Shared` متضخمة.** أي منطق عمل يذهب إلى الوحدة التي تملكه.
7. **لا قواعد تحقق عبر الوحدات.** مثلاً `exists:catalog_products,id` يربط Orders بجدول Catalog، لذلك يتم التحقق عبر الواجهة.

### التدفق: إنشاء طلب

```text
POST /api/orders
   │
   ▼
Orders\OrderController ──► Orders\Actions\PlaceOrder
                                  │  (داخل DB::transaction)
                                  ├─► ProductCatalog::reserve()   ← عقد Catalog
                                  │       (يخصم المخزون بقفل lockForUpdate)
                                  ├─► Order::create()             ← جدول orders
                                  │
                                  └─► event(OrderPlaced)          ← بعد نهاية المعاملة
```

## إضافة وحدة جديدة

مثال: وحدة `Billing`.

1. أنشئ البنية:
   ```text
   Modules/Billing/{Contracts,Actions,Models,Http/Controllers,Database/Migrations,Providers,Routes,Tests/Feature}
   ```
2. أنشئ `Modules/Billing/Providers/BillingServiceProvider.php` (انسخ `CatalogServiceProvider` كنقطة بداية):
   - `register()`: اربط الواجهات بالتنفيذ.
   - `boot()`: حمّل الـ migrations والـ routes.
3. أضف الاسم إلى `config/modules.php`:
   ```php
   'enabled' => ['Catalog', 'Orders', 'Billing'],
   ```
4. شغّل `php artisan test` للتأكد من الالتزام بقواعد الحدود.

لتعطيل وحدة مؤقتاً، أزلها من `enabled` دون حذف ملفاتها.

## لماذا لم نستخدم `nwidart/laravel-modules`؟

الحزمة تدعم Laravel 13 وهي خيار جيد للمشاريع الكبيرة (أوامر `module:make` وتفعيل/تعطيل وغيرها)،
لكن هذا المشروع يركّز على **بنية الحدود نفسها**، لذلك:

- لا توجد اعتمادية خارجية للوحدات، والتسجيل صريح في `config/modules.php`.
- الحد بين الوحدات يُفرض باختبار معماري، وليس بالاعتماد على اتفاقية التسمية فقط.

إذا احتجت أوامر Artisan لتوليد الوحدات، يمكن إضافة `nwidart/laravel-modules` لاحقاً؛ يتطلب فقط تعديل `ModuleRegistry`.

## متى تنتقل وحدة إلى خدمة مستقلة؟

ليس قبل أن تظهر حاجة قياسية: حمل مختلف للتوسع، أو فريق منفصل، أو تكرار تغييرات تكسر الحدود.
الواجهات الحالية (`ProductCatalog`) تجعل الاستخراج لاحقاً ممكناً بتغيير التنفيذ إلى HTTP client.
