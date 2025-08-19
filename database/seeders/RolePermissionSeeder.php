<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء الصلاحيات
        $permissions = [
            // إدارة النظام
            'manage users', 'manage roles', 'manage permissions',
            'view dashboard',
            
            // إدارة الحسابات
            'manage coa', 'manage journal entries', 'view financial reports',
            'manage expenses', 'manage revenues', 'manage treasury',
            'manage cash accounts', 'manage supplier payments',
            'manage receipt vouchers', 'manage payment vouchers',
            
            // إدارة المخزون
            'manage inventory', 'view inventory reports',
            'manage items', 'manage warehouses', 'manage item categories',
            
            // إدارة الإنتاج
            'manage production', 'view production reports',
            'manage production orders', 'manage bill of materials', 'manage work centers',
            
            // إدارة المشتريات
            'manage purchasing', 'view purchasing reports',
            'manage suppliers', 'manage purchase invoices',
            
            // إدارة المبيعات
            'manage sales', 'view sales reports',
            'manage customers', 'manage sales orders',
            
            // إدارة الأصول
            'manage fixed assets', 'view fixed asset reports',
            'manage asset categories', 'manage asset depreciations', 'manage asset inventories',
            
            // إدارة الرواتب
            'manage payroll', 'view payroll reports',
            
            // إدارة السجلات
            'manage audit logs',
            
            // الطباعة
            'print reports', 'print invoices', 'print vouchers',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // إنشاء الأدوار وتعيين الصلاحيات
        $roles = [
            'Administrator' => $permissions, // جميع الصلاحيات
            
            'Accountant' => [
                'view dashboard',
                'manage coa', 'manage journal entries', 'view financial reports',
                'manage expenses', 'manage revenues', 'manage treasury',
                'manage cash accounts', 'manage supplier payments',
                'manage receipt vouchers', 'manage payment vouchers',
                'view inventory reports', 'view production reports',
                'view purchasing reports', 'view sales reports',
                'view fixed asset reports', 'view payroll reports',
                'print reports', 'print invoices', 'print vouchers',
            ],
            
            'Production Manager' => [
                'view dashboard',
                'manage production', 'view production reports',
                'manage production orders', 'manage bill of materials', 'manage work centers',
                'manage inventory', 'view inventory reports',
                'manage items', 'manage warehouses', 'manage item categories',
                'view financial reports', 'view purchasing reports',
                'print reports',
            ],
            
            'Sales Clerk' => [
                'view dashboard',
                'manage sales', 'view sales reports',
                'manage customers', 'manage sales orders',
                'view inventory reports', 'view financial reports',
                'print invoices', 'print reports',
            ],
            
            'Inventory Clerk' => [
                'view dashboard',
                'manage inventory', 'view inventory reports',
                'manage items', 'manage warehouses', 'manage item categories',
                'view production reports', 'view purchasing reports',
                'print reports',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $permissions = Permission::whereIn('name', $rolePermissions)->get();
            $role->syncPermissions($permissions);
        }
    }
} 