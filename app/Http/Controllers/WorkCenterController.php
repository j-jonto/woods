<?php
namespace App\Http\Controllers;

use App\Models\WorkCenter;
use Illuminate\Http\Request;

class WorkCenterController extends Controller
{
    public function index()
    {
        $workCenters = WorkCenter::paginate(20);
        return view('work_centers.index', compact('workCenters'));
    }

    public function create()
    {
        return view('work_centers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:work_centers',
            'name' => 'required',
        ]);
        WorkCenter::create($validated + [
            'description' => $request->input('description'),
            'is_active' => $request->input('is_active', true),
        ]);
        return redirect()->route('work_centers.index')->with('success', 'Work center created successfully.');
    }

    public function edit(WorkCenter $workCenter)
    {
        return view('work_centers.edit', compact('workCenter'));
    }

    public function update(Request $request, WorkCenter $workCenter)
    {
        $validated = $request->validate([
            'code' => 'required|unique:work_centers,code,' . $workCenter->id,
            'name' => 'required',
        ]);
        $workCenter->update($validated + [
            'description' => $request->input('description'),
            'is_active' => $request->input('is_active', true),
        ]);
        return redirect()->route('work_centers.index')->with('success', 'Work center updated successfully.');
    }

    public function destroy(WorkCenter $workCenter)
    {
        $workCenter->delete();
        return redirect()->route('work_centers.index')->with('success', 'Work center deleted successfully.');
    }
} 