<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        $logs = $query->orderByDesc('created_at')->paginate(30);
        return view('audit_logs.index', compact('logs'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return view('audit_logs.show', compact('auditLog'));
    }
} 