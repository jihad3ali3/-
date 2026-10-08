# المصادر / Sources

تم الاطلاع على المصادر التالية قبل بناء الهيكل. المصادر الرسمية هي المرجع الأول،
والمصادر المجتمعية استُخدمت للمقارنة وأخذ الأنماط العملية.

## 1. مصادر رسمية / Official

| # | المصدر | ما أخذناه منه |
|---|--------|----------------|
| 1 | [Laravel 13.x Release Notes](https://laravel.com/framework/docs/13.x/releases) | متطلب PHP 8.3+، سياسة الدعم (Laravel 13 حتى Q3 2027 للإصلاحات و 17 مارس 2028 للأمان)، `PreventRequestForgery`، `Queue::route()`، PHP Attributes مثل `#[Middleware]` و `#[Tries]` |
| 2 | [laravel/laravel – branch 13.x](https://github.com/laravel/laravel/tree/13.x) | هيكل المشروع الأساسي (bootstrap/app.php، bootstrap/providers.php، config، routes)، و `laravel/framework: ^13.17` و `php: ^8.3`، و `#[Fillable]` على النماذج |
| 3 | [laravel/framework – branch 13.x](https://github.com/laravel/framework/tree/13.x) | تحققنا من واجهات الكود الفعلية: `Route::group` و `loadMigrationsFrom` و `Model::__call` لـ `decrement` و `DatabaseTransactionsManager` (سلوك الأحداث بعد الـ commit داخل `RefreshDatabase`) |
| 4 | [nWidart/laravel-modules](https://github.com/nWidart/laravel-modules) | أشهر حزمة للوحدات في Laravel، وتدعم Laravel 13 (`^13.0`). اخترنا عدم استخدامها للبداية لأن المطلوب هنا بنية واضحة قابلة للشرح، ويمكن الانتقال إليها لاحقاً |

## 2. مصادر مجتمعية / Community

| # | المصدر | ما أخذناه منه |
|---|--------|----------------|
| 5 | [Modular Monoliths: Creating Real Boundaries – Wendell Adriel](https://wendelladriel.com/blog/modular-monoliths-creating-real-boundaries-before-reaching-for-microservices) | تعريف "الحدود الحقيقية": الوحدة تكتب فقط جداولها، والقراءة عبر عقد (contract)، والأحداث تصف حقائق مكتملة، واختبارات المعمارية تفرض الاتجاه |
| 6 | [Modular Monolith – Software Architecture Guild](https://software-architecture-guild.com/guide/architecture/styles/modular-monolith/) | لا كتابة عبر الوحدات على نفس البيانات، والاعتماد على واجهات وأحداث، و"fitness functions" في CI، ومتى يكون النمط مناسباً |
| 7 | [Modular Monolithic Architecture in Laravel 12 – 200oksolutions](https://www.200oksolutions.com/blog/modular-monolithic-architecture-in-laravel-12/) | تسجيل Service Providers لكل وحدة، وإضافة مساحة `Modules\\` في PSR-4، وطبقة Service |
| 8 | [The Modular Monolith: Laravel Edition – DEV Community](https://dev.to/insight105/the-modular-monolith-laravel-edition-jpc) | قاعدة "الوحدة لا تستورد Models أو Services لوحدة أخرى"، واستخدام DTOs عند الحدود، وتجنب مكتبة `Shared` المتضخمة |
| 9 | [Building modular systems in Laravel – Sevalla](https://sevalla.com/blog/building-modular-systems-laravel/) | اختبارات التكامل بين الوحدات، و Deptrac لفرض الحدود |
| 10 | [Building modular systems in Laravel – JustSteveKing](https://www.juststeveking.com/articles/building-modular-systems-in-laravel-a-practical-guide/) | تجنب الدورات (circular dependencies)، وعدم تقسيم وحدة لكل Model |
| 11 | [Laravel Modular Monolith – iflair](https://www.iflair.com/laravel-modular-monolith-the-powerful-architecture-you-need/) | الاستقلالية والتغليف، والتواصل عبر الواجهات والأحداث والطوابير |
| 12 | [Exploring Modular Monolithic Architecture – Medium (harryespant)](https://medium.com/@harryespant/exploring-modular-monolithic-architecture-a-laravel-developers-guide-with-an-e-commerce-example-0548668ec222) | مثال تجارة إلكترونية: تسلسل الأحداث (OrderPlaced) بين الوحدات |
| 13 | [Laravel Modular Architecture: Breaking the Monolith – muneebdev](https://muneebdev.com/laravel-modular-architecture-breaking-the-monolith/) | هيكل مجلد `modules/` ومعالجة الترحيل التدريجي |
| 14 | [Rethinking Laravel Folder Structure for a Modular Monolith – r/laravel](https://www.reddit.com/r/laravel/comments/1kmqrsm/rethinking_laravel_folder_structure_for_a_modular/) | نقاش حول تسرب الاعتماديات عبر العلاقات (`User::payments()`)، وفائدة الفصل بالـ DTOs |

## 3. مصادر قرأتها للمقارنة فقط / Read for cross-checking only

مقالات عن Laravel 13 تكرر معلومات المصدر الرسمي (PHP 8.3، PHP Attributes، AI SDK، Cache::touch).
لم نعتمد على أي ادعاء منها ما لم يتأكد في المصادر الرسمية أعلاه:

- [Laravel 13 New Features – pola5h](https://pola5h.github.io/blog/laravel-13-new-features/)
- [Laravel 13 and the AI SDK – LaraCopilot](https://laracopilot.com/blog/laravel-13/)
- [Laravel 13: New Features, Release Date & Upgrade Guide – ITPath](https://www.itpathsolutions.com/laravel-13-new-features-updates-guide)
- [Laravel PHP compatibility matrix – ITMarkerz](https://itmarkerz.co.in/blog/laravel-php-compatibility-matrix) (ادعاء أن Laravel 13.3+ يحتاج PHP 8.4 عملياً لم نتحقق منه)
- [Laravel 12 vs Laravel 13 – WebRoom Tech](https://webroomtech.com/laravel-12-vs-laravel-13-what-actually-changed-and-when-should-you-upgrade/)
