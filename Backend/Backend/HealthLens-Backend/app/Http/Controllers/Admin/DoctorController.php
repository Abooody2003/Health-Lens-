<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Specialization;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with('specialization')->latest()->paginate(15);
        return view('admin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $specializations = Specialization::where('is_active', true)->orderBy('name')->get();
        return view('admin.doctors.create', compact('specializations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialization_id' => 'required|exists:specializations,id',
            'city' => 'required|string|max:100',
            'area' => 'required|string|max:100',
            'address' => 'nullable|string|max:500',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        Doctor::create($validated);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Doctor created successfully.');
    }

    public function show(Doctor $doctor)
    {
        $doctor->load('specialization', 'media');
        return view('admin.doctors.show', compact('doctor'));
    }

    public function edit(Doctor $doctor)
    {
        $specializations = Specialization::where('is_active', true)->orderBy('name')->get();
        return view('admin.doctors.edit', compact('doctor', 'specializations'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialization_id' => 'required|exists:specializations,id',
            'city' => 'required|string|max:100',
            'area' => 'required|string|max:100',
            'address' => 'nullable|string|max:500',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $doctor->update($validated);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Doctor updated successfully.');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Doctor deleted successfully.');
    }
}

