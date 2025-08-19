<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ManagePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:manage {action} {--user=} {--role=} {--permission=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إدارة الصلاحيات والأدوار في النظام';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list-roles':
                $this->listRoles();
                break;
            case 'list-permissions':
                $this->listPermissions();
                break;
            case 'list-users':
                $this->listUsers();
                break;
            case 'assign-role':
                $this->assignRole();
                break;
            case 'assign-permission':
                $this->assignPermission();
                break;
            case 'user-permissions':
                $this->showUserPermissions();
                break;
            case 'role-permissions':
                $this->showRolePermissions();
                break;
            default:
                $this->error('الإجراء غير معروف. الإجراءات المتوفرة: list-roles, list-permissions, list-users, assign-role, assign-permission, user-permissions, role-permissions');
        }
    }

    private function listRoles()
    {
        $this->info('الأدوار المتوفرة:');
        $roles = Role::with('permissions')->get();
        
        $headers = ['الدور', 'عدد الصلاحيات', 'الصلاحيات'];
        $rows = [];
        
        foreach ($roles as $role) {
            $permissions = $role->permissions->pluck('name')->implode(', ');
            $rows[] = [
                $role->name,
                $role->permissions->count(),
                $permissions ?: 'لا توجد صلاحيات'
            ];
        }
        
        $this->table($headers, $rows);
    }

    private function listPermissions()
    {
        $this->info('الصلاحيات المتوفرة:');
        $permissions = Permission::with('roles')->get();
        
        $headers = ['الصلاحية', 'عدد الأدوار', 'الأدوار'];
        $rows = [];
        
        foreach ($permissions as $permission) {
            $roles = $permission->roles->pluck('name')->implode(', ');
            $rows[] = [
                $permission->name,
                $permission->roles->count(),
                $roles ?: 'لا توجد أدوار'
            ];
        }
        
        $this->table($headers, $rows);
    }

    private function listUsers()
    {
        $this->info('المستخدمين وأدوارهم:');
        $users = User::with('roles')->get();
        
        $headers = ['المستخدم', 'البريد الإلكتروني', 'الأدوار'];
        $rows = [];
        
        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->implode(', ');
            $rows[] = [
                $user->name,
                $user->email,
                $roles ?: 'لا توجد أدوار'
            ];
        }
        
        $this->table($headers, $rows);
    }

    private function assignRole()
    {
        $userEmail = $this->option('user');
        $roleName = $this->option('role');

        if (!$userEmail || !$roleName) {
            $this->error('يجب تحديد المستخدم والدور: --user=email --role=role_name');
            return;
        }

        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $this->error('المستخدم غير موجود');
            return;
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error('الدور غير موجود');
            return;
        }

        $user->assignRole($role);
        $this->info("تم تعيين دور '{$roleName}' للمستخدم '{$user->name}' بنجاح");
    }

    private function assignPermission()
    {
        $userEmail = $this->option('user');
        $permissionName = $this->option('permission');

        if (!$userEmail || !$permissionName) {
            $this->error('يجب تحديد المستخدم والصلاحية: --user=email --permission=permission_name');
            return;
        }

        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $this->error('المستخدم غير موجود');
            return;
        }

        $permission = Permission::where('name', $permissionName)->first();
        if (!$permission) {
            $this->error('الصلاحية غير موجودة');
            return;
        }

        $user->givePermissionTo($permission);
        $this->info("تم تعيين صلاحية '{$permissionName}' للمستخدم '{$user->name}' بنجاح");
    }

    private function showUserPermissions()
    {
        $userEmail = $this->option('user');

        if (!$userEmail) {
            $this->error('يجب تحديد المستخدم: --user=email');
            return;
        }

        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $this->error('المستخدم غير موجود');
            return;
        }

        $this->info("صلاحيات المستخدم: {$user->name}");
        $this->info("الأدوار: " . $user->roles->pluck('name')->implode(', '));
        $this->info("الصلاحيات المباشرة: " . $user->permissions->pluck('name')->implode(', '));
        $this->info("جميع الصلاحيات: " . $user->getAllPermissions()->pluck('name')->implode(', '));
    }

    private function showRolePermissions()
    {
        $roleName = $this->option('role');

        if (!$roleName) {
            $this->error('يجب تحديد الدور: --role=role_name');
            return;
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error('الدور غير موجود');
            return;
        }

        $this->info("صلاحيات الدور: {$role->name}");
        $this->info("الصلاحيات: " . $role->permissions->pluck('name')->implode(', '));
    }
} 