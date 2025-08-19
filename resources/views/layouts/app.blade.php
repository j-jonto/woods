<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'نظام إدارة المصنع') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'نظام إدارة المصنع') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="تبديل التنقل">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">لوحة التحكم</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                البيانات الأساسية
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('items.index') }}">المنتجات</a></li>
                                <li><a class="dropdown-item" href="{{ route('warehouses.index') }}">المستودعات</a></li>
                                <li><a class="dropdown-item" href="{{ route('item_categories.index') }}">فئات المنتجات</a></li>
                                <li><a class="dropdown-item" href="{{ route('work_centers.index') }}">مراكز العمل</a></li>
                                <li><a class="dropdown-item" href="{{ route('customers.index') }}">العملاء</a></li>
                                <li><a class="dropdown-item" href="{{ route('suppliers.index') }}">الموردين</a></li>
                                <li><a class="dropdown-item" href="{{ route('sales_representatives.index') }}">مندوبي المبيعات</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('fixed_assets.index') }}">الأصول الثابتة</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                المحاسبة
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('coa.index') }}">دليل الحسابات</a></li>
                                <li><a class="dropdown-item" href="{{ route('journal_entries.index') }}">القيود المحاسبية</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                المخزون
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('inventory_transactions.index') }}">معاملات المخزون</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.inventory') }}">تقرير المخزون</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.item_movement') }}">حركة المنتجات</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('inventory_transactions.index') }}">
                                <i class="fas fa-boxes"></i> المخزن
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                الإنتاج
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('boms.index') }}">قوائم المواد</a></li>
                                <li><a class="dropdown-item" href="{{ route('production_orders.index') }}">أوامر الإنتاج</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('reports.production') }}">تقرير الإنتاج</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                المبيعات والمشتريات
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('sales_orders.index') }}">أوامر البيع</a></li>
                                <li><a class="dropdown-item" href="{{ route('purchase_orders.index') }}">أوامر الشراء</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('purchase_invoices.index') }}">فواتير الشراء</a></li>
                                <li><a class="dropdown-item" href="{{ route('supplier_payments.index') }}">مدفوعات الموردين</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                المالية
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('treasury.index') }}">الخزنة</a></li>
                                <li><a class="dropdown-item" href="{{ route('cash_accounts.index') }}">الحسابات النقدية</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('expenses.index') }}">المصروفات</a></li>
                                <li><a class="dropdown-item" href="{{ route('expense_types.index') }}">أنواع المصروفات</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('revenues.index') }}">الإيرادات</a></li>
                                <li><a class="dropdown-item" href="{{ route('revenue_types.index') }}">أنواع الإيرادات</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('payment_vouchers.index') }}">أوامر الدفع</a></li>
                                <li><a class="dropdown-item" href="{{ route('receipt_vouchers.index') }}">أوامر القبض</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                التقارير
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('reports.sales') }}">تقرير المبيعات</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.purchases') }}">تقرير المشتريات</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.inventory') }}">تقرير المخزون</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.production') }}">تقرير الإنتاج</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('reports.financial') }}">التقرير المالي</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.treasury') }}">تقرير الخزنة</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.expenses') }}">تقرير المصروفات</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.revenues') }}">تقرير الإيرادات</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('reports.audit') }}">تقرير المراجعة</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.performance') }}">تقرير الأداء</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                النظام
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('users.index') }}">المستخدمين</a></li>
                                <li><a class="dropdown-item" href="{{ route('audit_logs.index') }}">سجل المراجعة</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('asset_categories.index') }}">فئات الأصول</a></li>
                                <li><a class="dropdown-item" href="{{ route('asset_inventories.index') }}">جرد الأصول</a></li>
                                <li><a class="dropdown-item" href="{{ route('asset_depreciations.index') }}">إهلاك الأصول</a></li>
                            </ul>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">تسجيل الدخول</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">تسجيل جديد</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        تسجيل الخروج
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    @stack('scripts')
</body>
</html> 