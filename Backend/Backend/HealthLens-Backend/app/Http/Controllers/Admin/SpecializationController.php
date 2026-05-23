<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpecializationController extends Controller
{
    public function index()
    {
        $specializations = Specialization::orderBy('display_order')->paginate(15);
        return view('admin.specializations.index', compact('specializations'));
    }

    public function create()
    {
        return view('admin.specializations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specializations,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['display_order'] = $validated['display_order'] ?? Specialization::max('display_order') + 1;

        Specialization::create($validated);

        return redirect()->route('admin.specializations.index')
            ->with('success', 'Specialization created successfully.');
    }

    public function show(Specialization $specialization)
    {
        $specialization->load('doctors');
        return view('admin.specializations.show', compact('specialization'));
    }

    public function edit(Specialization $specialization)
    {
        return view('admin.specializations.edit', compact('specialization'));
    }

    public function update(Request $request, Specialization $specialization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specializations,name,' . $specialization->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $specialization->update($validated);

        return redirect()->route('admin.specializations.index')
            ->with('success', 'Specialization updated successfully.');
    }

    public function destroy(Specialization $specialization)
    {
        $specialization->delete();

        return redirect()->route('admin.specializations.index')
            ->with('success', 'Specialization deleted successfully.');
    }
}

