<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ChartOfAccount;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Warehouse;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ExpenseType;
use App\Models\RevenueType;
use App\Models\Expense;
use App\Models\Revenue;
use App\Models\CashAccount;
use App\Models\SupplierPayment;
use App\Models\ReceiptVoucher;
use App\Models\PaymentVoucher;
use App\Models\PurchaseInvoice;
use App\Models\SalesOrder;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use App\Models\FixedAsset;
use App\Models\AssetCategory;
use App\Models\AssetDepreciation;
use App\Models\AssetInventory;
use App\Models\InventoryTransaction;
use App\Models\ProductionOrder;
use App\Models\BillOfMaterial;
use App\Models\WorkCenter;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('بدء إنشاء البيانات الافتراضية...');

        // إنشاء المستخدمين
        $this->createUsers();
        
        // إنشاء دليل الحسابات
        $this->createChartOfAccounts();
        
        // إنشاء فئات الأصناف
        $this->createItemCategories();
        
        // إنشاء الأصناف
        $this->createItems();
        
        // إنشاء العملاء
        $this->createCustomers();
        
        // إنشاء الموردين
        $this->createSuppliers();
        
        // إنشاء أنواع المصروفات
        $this->createExpenseTypes();
        
        // إنشاء أنواع الإيرادات
        $this->createRevenueTypes();
        
        // إنشاء المصروفات
        $this->createExpenses();
        
        // إنشاء الإيرادات
        $this->createRevenues();
        
        // إنشاء حسابات الصندوق والبنك
        $this->createCashAccounts();
        
        // إنشاء مدفوعات الموردين
        $this->createSupplierPayments();
        
        // إنشاء سندات القبض
        $this->createReceiptVouchers();
        
        // إنشاء سندات الصرف
        $this->createPaymentVouchers();
        
        // إنشاء فواتير الشراء
        $this->createPurchaseInvoices();
        
        // إنشاء طلبات المبيعات
        $this->createSalesOrders();
        
        // إنشاء الخزنة العامة
        $this->createTreasury();
        
        // إنشاء الأصول الثابتة
        $this->createFixedAssets();
        
        // إنشاء معاملات المخزون
        $this->createInventoryTransactions();
        
        // إنشاء أوامر الإنتاج
        $this->createProductionOrders();
        
        // إنشاء قوائم المواد
        $this->createBillOfMaterials();
        
        // إنشاء مراكز العمل
        $this->createWorkCenters();
        
        // إنشاء القيود اليومية
        $this->createJournalEntries();

        $this->command->info('تم إنشاء البيانات الافتراضية بنجاح!');
    }

    private function createUsers()
    {
        $this->command->info('إنشاء المستخدمين...');
        
        $admin = User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@example.com',
            'password' => Hash::make('123456'),
        ]);
        $admin->assignRole('Administrator');

        $accountant = User::create([
            'name' => 'محمد أحمد',
            'email' => 'mohamed@example.com',
            'password' => Hash::make('123456'),
        ]);
        $accountant->assignRole('Accountant');

        $salesClerk = User::create([
            'name' => 'فاطمة علي',
            'email' => 'fatima@example.com',
            'password' => Hash::make('123456'),
        ]);
        $salesClerk->assignRole('Sales Clerk');
    }

    private function createChartOfAccounts()
    {
        $this->command->info('إنشاء دليل الحسابات...');
        
        $accounts = [
            ['code' => '1000', 'name' => 'الأصول', 'type' => 'asset', 'parent_id' => null],
            ['code' => '1100', 'name' => 'الأصول المتداولة', 'type' => 'asset', 'parent_id' => 1],
            ['code' => '1110', 'name' => 'النقد وما في حكمه', 'type' => 'asset', 'parent_id' => 2],
            ['code' => '1111', 'name' => 'الصندوق', 'type' => 'asset', 'parent_id' => 3],
            ['code' => '1112', 'name' => 'البنك', 'type' => 'asset', 'parent_id' => 3],
            ['code' => '1120', 'name' => 'المدينون', 'type' => 'asset', 'parent_id' => 2],
            ['code' => '1121', 'name' => 'أرصدة العملاء', 'type' => 'asset', 'parent_id' => 6],
            ['code' => '1130', 'name' => 'المخزون', 'type' => 'asset', 'parent_id' => 2],
            ['code' => '1131', 'name' => 'مخزون الأصناف', 'type' => 'asset', 'parent_id' => 8],
            ['code' => '1200', 'name' => 'الأصول الثابتة', 'type' => 'asset', 'parent_id' => 1],
            ['code' => '1210', 'name' => 'الأراضي والمباني', 'type' => 'asset', 'parent_id' => 10],
            ['code' => '1220', 'name' => 'الآلات والمعدات', 'type' => 'asset', 'parent_id' => 10],
            ['code' => '2000', 'name' => 'الخصوم', 'type' => 'liability', 'parent_id' => null],
            ['code' => '2100', 'name' => 'الخصوم المتداولة', 'type' => 'liability', 'parent_id' => 13],
            ['code' => '2110', 'name' => 'الدائنون', 'type' => 'liability', 'parent_id' => 14],
            ['code' => '2111', 'name' => 'أرصدة الموردين', 'type' => 'liability', 'parent_id' => 15],
            ['code' => '3000', 'name' => 'حقوق الملكية', 'type' => 'equity', 'parent_id' => null],
            ['code' => '3100', 'name' => 'رأس المال', 'type' => 'equity', 'parent_id' => 17],
            ['code' => '3200', 'name' => 'الأرباح المحتجزة', 'type' => 'equity', 'parent_id' => 17],
            ['code' => '4000', 'name' => 'الإيرادات', 'type' => 'revenue', 'parent_id' => null],
            ['code' => '4100', 'name' => 'إيرادات المبيعات', 'type' => 'revenue', 'parent_id' => 20],
            ['code' => '4200', 'name' => 'إيرادات أخرى', 'type' => 'revenue', 'parent_id' => 20],
            ['code' => '5000', 'name' => 'المصروفات', 'type' => 'expense', 'parent_id' => null],
            ['code' => '5100', 'name' => 'مصروفات التشغيل', 'type' => 'expense', 'parent_id' => 23],
            ['code' => '5200', 'name' => 'مصروفات إدارية', 'type' => 'expense', 'parent_id' => 23],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::create($account);
        }
    }

    private function createItemCategories()
    {
        $this->command->info('إنشاء فئات الأصناف...');
        
        $categories = [
            ['name' => 'خشب طبيعي', 'description' => 'أخشاب طبيعية من مصادر مختلفة'],
            ['name' => 'خشب مصنع', 'description' => 'أخشاب مصنعة مثل MDF و HDF'],
            ['name' => 'أدوات نجارة', 'description' => 'أدوات ومعدات النجارة'],
            ['name' => 'مواد لاصقة', 'description' => 'غراء ومواد لاصقة'],
            ['name' => 'دهانات', 'description' => 'دهانات وورنيش'],
        ];

        foreach ($categories as $category) {
            ItemCategory::create($category);
        }
    }

    private function createItems()
    {
        $this->command->info('إنشاء الأصناف...');
        
        $items = [
            [
                'code' => 'WOOD-001',
                'name' => 'خشب زان طبيعي',
                'description' => 'خشب زان طبيعي عالي الجودة',
                'category_id' => 1,
                'type' => 'raw_material',
                'unit_of_measure' => 'متر مكعب',
                'standard_cost' => 2500.00,
                'selling_price' => 3000.00,
                'reorder_point' => 10,
                'reorder_quantity' => 50,
                'is_active' => true,
            ],
            [
                'code' => 'WOOD-002',
                'name' => 'خشب صنوبر',
                'description' => 'خشب صنوبر للبناء',
                'category_id' => 1,
                'type' => 'raw_material',
                'unit_of_measure' => 'متر مكعب',
                'standard_cost' => 1800.00,
                'selling_price' => 2200.00,
                'reorder_point' => 20,
                'reorder_quantity' => 100,
                'is_active' => true,
            ],
            [
                'code' => 'WOOD-003',
                'name' => 'خشب MDF',
                'description' => 'خشب MDF للديكور',
                'category_id' => 2,
                'type' => 'raw_material',
                'unit_of_measure' => 'لوح',
                'standard_cost' => 150.00,
                'selling_price' => 200.00,
                'reorder_point' => 50,
                'reorder_quantity' => 250,
                'is_active' => true,
            ],
            [
                'code' => 'TOOL-001',
                'name' => 'منشار كهربائي',
                'description' => 'منشار كهربائي احترافي',
                'category_id' => 3,
                'type' => 'raw_material',
                'unit_of_measure' => 'قطعة',
                'standard_cost' => 800.00,
                'selling_price' => 1200.00,
                'reorder_point' => 5,
                'reorder_quantity' => 10,
                'is_active' => true,
            ],
            [
                'code' => 'ADH-001',
                'name' => 'غراء خشب',
                'description' => 'غراء خشب قوي',
                'category_id' => 4,
                'type' => 'raw_material',
                'unit_of_measure' => 'لتر',
                'standard_cost' => 25.00,
                'selling_price' => 35.00,
                'reorder_point' => 10,
                'reorder_quantity' => 50,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }

    private function createWarehouses()
    {
        $this->command->info('إنشاء المستودعات...');
        
        $warehouses = [
            ['code' => 'MAIN', 'name' => 'المستودع الرئيسي', 'location' => 'طرابلس', 'description' => 'المستودع الرئيسي للشركة'],
            ['code' => 'BENG', 'name' => 'مستودع بنغازي', 'location' => 'بنغازي', 'description' => 'مستودع فرعي في بنغازي'],
            ['code' => 'MISR', 'name' => 'مستودع مصراتة', 'location' => 'مصراتة', 'description' => 'مستودع فرعي في مصراتة'],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }

    private function createCustomers()
    {
        $this->command->info('إنشاء العملاء...');
        
        $customers = [
            [
                'code' => 'CUST001',
                'name' => 'شركة البناء الحديث',
                'contact_person' => 'أحمد محمد',
                'email' => 'ahmed@modern-building.com',
                'phone' => '0218-91-1234567',
                'address' => 'شارع طرابلس، طرابلس',
                'tax_number' => '123456789',
            ],
            [
                'code' => 'CUST002',
                'name' => 'مؤسسة الديكور الفاخر',
                'contact_person' => 'فاطمة علي',
                'email' => 'fatima@luxury-decor.com',
                'phone' => '0218-91-2345678',
                'address' => 'شارع بنغازي، بنغازي',
                'tax_number' => '234567890',
            ],
            [
                'code' => 'CUST003',
                'name' => 'شركة الأثاث العالمية',
                'contact_person' => 'محمد حسن',
                'email' => 'mohamed@global-furniture.com',
                'phone' => '0218-91-3456789',
                'address' => 'شارع مصراتة، مصراتة',
                'tax_number' => '345678901',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }

    private function createSuppliers()
    {
        $this->command->info('إنشاء الموردين...');
        
        $suppliers = [
            [
                'code' => 'SUPP001',
                'name' => 'شركة الأخشاب العالمية',
                'contact_person' => 'علي أحمد',
                'email' => 'ali@global-wood.com',
                'phone' => '0218-91-4567890',
                'address' => 'شارع الميناء، طرابلس',
                'tax_number' => '456789012',
            ],
            [
                'code' => 'SUPP002',
                'name' => 'مؤسسة الأدوات المهنية',
                'contact_person' => 'حسن محمد',
                'email' => 'hassan@pro-tools.com',
                'phone' => '0218-91-5678901',
                'address' => 'شارع الصناعة، بنغازي',
                'tax_number' => '567890123',
            ],
            [
                'code' => 'SUPP003',
                'name' => 'شركة المواد الكيميائية',
                'contact_person' => 'خالد علي',
                'email' => 'khalid@chemical-materials.com',
                'phone' => '0218-91-6789012',
                'address' => 'شارع التجارة، مصراتة',
                'tax_number' => '678901234',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }

    private function createExpenseTypes()
    {
        $this->command->info('إنشاء أنواع المصروفات...');
        
        $types = [
            ['name' => 'مصروفات إدارية',   'code' => 'ADMIN',  'description' => 'مصروفات الإدارة العامة'],
            ['name' => 'مصروفات تشغيلية', 'code' => 'OPER',   'description' => 'مصروفات التشغيل اليومي'],
            ['name' => 'مصروفات صيانة',   'code' => 'MAINT',  'description' => 'مصروفات الصيانة والإصلاح'],
            ['name' => 'مصروفات كهرباء',  'code' => 'ELEC',   'description' => 'فواتير الكهرباء'],
            ['name' => 'مصروفات مياه',    'code' => 'WATER',  'description' => 'فواتير المياه'],
        ];

        foreach ($types as $type) {
            ExpenseType::firstOrCreate(['code' => $type['code']], $type);
        }
    }

    private function createRevenueTypes()
    {
        $this->command->info('إنشاء أنواع الإيرادات...');
        
        $types = [
            ['name' => 'إيرادات مبيعات',  'code' => 'SALES',    'description' => 'إيرادات من بيع الأصناف'],
            ['name' => 'إيرادات خدمات',  'code' => 'SERVICES', 'description' => 'إيرادات من تقديم الخدمات'],
            ['name' => 'إيرادات أخرى',    'code' => 'OTHER',    'description' => 'إيرادات متنوعة'],
        ];

        foreach ($types as $type) {
            RevenueType::firstOrCreate(['code' => $type['code']], $type);
        }
    }

    private function createExpenses()
    {
        $this->command->info('إنشاء المصروفات...');
        
        $expenses = [
            [
                'expense_type_id' => 1,
                'amount' => 500.00,
                'expense_date' => now()->subDays(5),
                'description' => 'مصروفات مكتبية',
                'reference_no' => 'INV-001',
                'created_by' => 1,
            ],
            [
                'expense_type_id' => 2,
                'amount' => 1200.00,
                'expense_date' => now()->subDays(3),
                'description' => 'مصروفات تشغيل المصنع',
                'reference_no' => 'INV-002',
                'created_by' => 1,
            ],
            [
                'expense_type_id' => 3,
                'amount' => 800.00,
                'expense_date' => now()->subDays(1),
                'description' => 'صيانة المعدات',
                'reference_no' => 'INV-003',
                'created_by' => 1,
            ],
        ];

        foreach ($expenses as $expense) {
            $expense['expense_no'] = 'EXP-' . time() . '-' . rand(1000, 9999);
            Expense::create($expense);
        }
    }

    private function createRevenues()
    {
        $this->command->info('إنشاء الإيرادات...');
        
        $revenues = [
            [
                'revenue_type_id' => 1,
                'amount' => 5000.00,
                'revenue_date' => now()->subDays(4),
                'description' => 'بيع خشب زان',
                'reference_no' => 'REV-001',
                'created_by' => 1,
            ],
            [
                'revenue_type_id' => 2,
                'amount' => 1500.00,
                'revenue_date' => now()->subDays(2),
                'description' => 'خدمات نجارة',
                'reference_no' => 'REV-002',
                'created_by' => 1,
            ],
        ];

        foreach ($revenues as $revenue) {
            $revenue['revenue_no'] = 'REV-' . time() . '-' . rand(1000, 9999);
            Revenue::create($revenue);
        }
    }

    private function createCashAccounts()
    {
        $this->command->info('إنشاء حسابات الصندوق والبنك...');
        
        $accounts = [
            [
                'name' => 'الصندوق الرئيسي',
                'type' => 'cash',
                'account_number' => 'CASH-001',
                'opening_balance' => 10000.00,
                'current_balance' => 10000.00,
                'description' => 'الصندوق الرئيسي للشركة',
            ],
            [
                'name' => 'حساب البنك التجاري',
                'type' => 'bank',
                'account_number' => 'BANK-001',
                'opening_balance' => 50000.00,
                'current_balance' => 50000.00,
                'description' => 'حساب البنك التجاري',
            ],
        ];

        foreach ($accounts as $account) {
            CashAccount::create($account);
        }
    }

    private function createSupplierPayments()
    {
        $this->command->info('إنشاء مدفوعات الموردين...');
        
        $payments = [
            [
                'supplier_id' => 1,
                'amount' => 5000.00,
                'payment_date' => now()->subDays(3),
                'payment_method' => 'bank_transfer',
                'notes' => 'دفع فاتورة خشب',
                'created_by' => 1,
            ],
            [
                'supplier_id' => 2,
                'amount' => 2000.00,
                'payment_date' => now()->subDays(1),
                'payment_method' => 'cash',
                'notes' => 'دفع فاتورة أدوات',
                'created_by' => 1,
            ],
        ];

        foreach ($payments as $payment) {
            $payment['payment_no'] = 'SP-' . time() . '-' . rand(1000, 9999);
            SupplierPayment::create($payment);
        }
    }

    private function createReceiptVouchers()
    {
        $this->command->info('إنشاء سندات القبض...');
        
        $receipts = [
            [
                'reference_no' => 'REC-001',
                'amount' => 3000.00,
                'date' => now()->subDays(2),
                'source' => 'customer_payment',
                'description' => 'قبض من عميل',
                'created_by' => 1,
            ],
            [
                'reference_no' => 'REC-002',
                'amount' => 1500.00,
                'date' => now()->subDays(1),
                'source' => 'cash_sales',
                'description' => 'مبيعات نقدية',
                'created_by' => 1,
            ],
        ];

        foreach ($receipts as $receipt) {
            ReceiptVoucher::create($receipt);
        }
    }

    private function createPaymentVouchers()
    {
        $this->command->info('إنشاء سندات الصرف...');
        
        $payments = [
            [
                'reference_no' => 'PAY-001',
                'amount' => 1000.00,
                'date' => now()->subDays(2),
                'destination' => 'supplier_payment',
                'description' => 'دفع لمورد',
                'created_by' => 1,
            ],
            [
                'reference_no' => 'PAY-002',
                'amount' => 500.00,
                'date' => now()->subDays(1),
                'destination' => 'misc',
                'description' => 'مصروفات متنوعة',
                'created_by' => 1,
            ],
        ];

        foreach ($payments as $payment) {
            PaymentVoucher::create($payment);
        }
    }

    private function createPurchaseInvoices()
    {
        $this->command->info('إنشاء فواتير الشراء...');
        
        $invoices = [
            [
                'invoice_no' => 'PUR-001',
                'supplier_id' => 1,
                'invoice_date' => now()->subDays(5),
                'payment_type' => 'cash',
                'subtotal' => 5000.00,
                'total_amount' => 5000.00,
                'status' => 'received',
                'notes' => 'شراء خشب زان',
                'created_by' => 1,
            ],
            [
                'invoice_no' => 'PUR-002',
                'supplier_id' => 2,
                'invoice_date' => now()->subDays(3),
                'payment_type' => 'cash',
                'subtotal' => 2000.00,
                'total_amount' => 2000.00,
                'status' => 'received',
                'notes' => 'شراء أدوات نجارة',
                'created_by' => 1,
            ],
        ];

        foreach ($invoices as $invoice) {
            PurchaseInvoice::create($invoice);
        }
    }

    private function createSalesOrders()
    {
        $this->command->info('إنشاء طلبات المبيعات...');
        
        $orders = [
            [
                'order_no' => 'SAL-001',
                'customer_id' => 1,
                'order_date' => now()->subDays(4),
                'total_amount' => 3000.00,
                'payment_type' => 'cash',
                'status' => 'confirmed',
                'created_by' => 1,
            ],
            [
                'order_no' => 'SAL-002',
                'customer_id' => 2,
                'order_date' => now()->subDays(2),
                'total_amount' => 1500.00,
                'payment_type' => 'cash',
                'status' => 'confirmed',
                'created_by' => 1,
            ],
        ];

        foreach ($orders as $order) {
            SalesOrder::create($order);
        }
    }

    private function createTreasury()
    {
        $this->command->info('إنشاء الخزنة العامة...');
        
        $treasury = Treasury::create([
            'name' => 'الخزنة العامة',
            'opening_balance' => 100000.00,
            'current_balance' => 100000.00,
            'total_receipts' => 4500.00,
            'total_payments' => 1500.00,
            'description' => 'الخزنة العامة للشركة',
            'is_active' => true,
        ]);

        // إنشاء معاملات الخزنة
        $transactions = [
            [
                'treasury_id' => $treasury->id,
                'type' => 'receipt',
                'amount' => 3000.00,
                'balance_after' => 103000.00,
                'description' => 'قبض من عميل',
                'transaction_date' => now()->subDays(2),
                'created_by' => 1,
            ],
            [
                'treasury_id' => $treasury->id,
                'type' => 'receipt',
                'amount' => 1500.00,
                'balance_after' => 104500.00,
                'description' => 'مبيعات نقدية',
                'transaction_date' => now()->subDays(1),
                'created_by' => 1,
            ],
            [
                'treasury_id' => $treasury->id,
                'type' => 'payment',
                'amount' => 1000.00,
                'balance_after' => 103500.00,
                'description' => 'دفع لمورد',
                'transaction_date' => now()->subDays(1),
                'created_by' => 1,
            ],
            [
                'treasury_id' => $treasury->id,
                'type' => 'payment',
                'amount' => 500.00,
                'balance_after' => 103000.00,
                'description' => 'دفع مصروفات',
                'transaction_date' => now(),
                'created_by' => 1,
            ],
        ];

        foreach ($transactions as $transaction) {
            TreasuryTransaction::create($transaction);
        }
    }

    private function createFixedAssets()
    {
        $this->command->info('إنشاء الأصول الثابتة...');
        
        // إنشاء فئات الأصول
        $categories = [
            ['name' => 'مباني', 'description' => 'المباني والعقارات'],
            ['name' => 'آلات ومعدات', 'description' => 'الآلات والمعدات الإنتاجية'],
            ['name' => 'أثاث ومفروشات', 'description' => 'الأثاث والمفروشات المكتبية'],
        ];

        foreach ($categories as $category) {
            AssetCategory::create($category);
        }

        // إنشاء الأصول
        $assets = [
            [
                'name' => 'مبنى المصنع',
                'category_id' => 1,
                'purchase_date' => now()->subYears(2),
                'purchase_cost' => 500000.00,
                'current_value' => 450000.00,
                'location' => 'طرابلس',
                'description' => 'مبنى المصنع الرئيسي',
                'status' => 'active',
            ],
            [
                'name' => 'منشار كهربائي',
                'category_id' => 2,
                'purchase_date' => now()->subMonths(6),
                'purchase_cost' => 5000.00,
                'current_value' => 4000.00,
                'location' => 'المصنع',
                'description' => 'منشار كهربائي احترافي',
                'status' => 'active',
            ],
        ];

        foreach ($assets as $asset) {
            FixedAsset::create($asset);
        }
    }

    private function createInventoryTransactions()
    {
        $this->command->info('إنشاء معاملات المخزون...');
        
        $transactions = [
            [
                'item_id' => 1,
                'warehouse_id' => 1,
                'type' => 'receipt',
                'quantity' => 50,
                'unit_cost' => 2500.00,
                'total_cost' => 125000.00,
                'transaction_date' => now()->subDays(5),
                'reference' => 'PUR-001',
                'description' => 'استلام خشب زان',
                'created_by' => 1,
            ],
            [
                'item_id' => 2,
                'warehouse_id' => 1,
                'type' => 'receipt',
                'quantity' => 100,
                'unit_cost' => 1800.00,
                'total_cost' => 180000.00,
                'transaction_date' => now()->subDays(3),
                'reference' => 'PUR-002',
                'description' => 'استلام خشب صنوبر',
                'created_by' => 1,
            ],
            [
                'item_id' => 1,
                'warehouse_id' => 1,
                'type' => 'issue',
                'quantity' => 10,
                'unit_cost' => 2500.00,
                'total_cost' => 25000.00,
                'transaction_date' => now()->subDays(2),
                'reference' => 'SAL-001',
                'description' => 'صرف خشب زان للبيع',
                'created_by' => 1,
            ],
        ];

        foreach ($transactions as $transaction) {
            InventoryTransaction::create($transaction);
        }
    }

    private function createProductionOrders()
    {
        $this->command->info('إنشاء أوامر الإنتاج...');
        
        $orders = [
            [
                'order_number' => 'PROD-001',
                'item_id' => 1,
                'planned_quantity' => 100,
                'actual_quantity' => 95,
                'start_date' => now()->subDays(7),
                'end_date' => now()->subDays(2),
                'status' => 'completed',
                'description' => 'إنتاج خشب زان',
                'created_by' => 1,
            ],
        ];

        foreach ($orders as $order) {
            ProductionOrder::create($order);
        }
    }

    private function createBillOfMaterials()
    {
        $this->command->info('إنشاء قوائم المواد...');
        
        $boms = [
            [
                'name' => 'قائمة مواد طاولة خشبية',
                'item_id' => 1,
                'description' => 'المواد المطلوبة لصناعة طاولة خشبية',
                'is_active' => true,
                'created_by' => 1,
            ],
        ];

        foreach ($boms as $bom) {
            BillOfMaterial::create($bom);
        }
    }

    private function createWorkCenters()
    {
        $this->command->info('إنشاء مراكز العمل...');
        
        $centers = [
            [
                'name' => 'ورشة النجارة',
                'location' => 'المصنع الرئيسي',
                'capacity' => 10,
                'description' => 'ورشة النجارة الرئيسية',
                'is_active' => true,
            ],
            [
                'name' => 'ورشة الطلاء',
                'location' => 'المصنع الرئيسي',
                'capacity' => 5,
                'description' => 'ورشة الطلاء والدهان',
                'is_active' => true,
            ],
        ];

        foreach ($centers as $center) {
            WorkCenter::create($center);
        }
    }

    private function createJournalEntries()
    {
        $this->command->info('إنشاء القيود اليومية...');
        
        $entries = [
            [
                'entry_number' => 'JE-001',
                'entry_date' => now()->subDays(5),
                'description' => 'تسجيل شراء مخزون',
                'total_debit' => 125000.00,
                'total_credit' => 125000.00,
                'status' => 'posted',
                'created_by' => 1,
            ],
            [
                'entry_number' => 'JE-002',
                'entry_date' => now()->subDays(2),
                'description' => 'تسجيل مبيعات',
                'total_debit' => 3000.00,
                'total_credit' => 3000.00,
                'status' => 'posted',
                'created_by' => 1,
            ],
        ];

        foreach ($entries as $entry) {
            JournalEntry::create($entry);
        }

        // إنشاء تفاصيل القيود
        $details = [
            // تفاصيل القيد الأول
            [
                'journal_entry_id' => 1,
                'account_id' => 9, // مخزون الأصناف
                'debit_amount' => 125000.00,
                'credit_amount' => 0.00,
                'description' => 'شراء خشب زان',
            ],
            [
                'journal_entry_id' => 1,
                'account_id' => 16, // أرصدة الموردين
                'debit_amount' => 0.00,
                'credit_amount' => 125000.00,
                'description' => 'مديونية للمورد',
            ],
            // تفاصيل القيد الثاني
            [
                'journal_entry_id' => 2,
                'account_id' => 16, // أرصدة الموردين
                'debit_amount' => 3000.00,
                'credit_amount' => 0.00,
                'description' => 'قبض من عميل',
            ],
            [
                'journal_entry_id' => 2,
                'account_id' => 22, // إيرادات المبيعات
                'debit_amount' => 0.00,
                'credit_amount' => 3000.00,
                'description' => 'إيرادات مبيعات',
            ],
        ];

        foreach ($details as $detail) {
            JournalEntryDetail::create($detail);
        }
    }
} 