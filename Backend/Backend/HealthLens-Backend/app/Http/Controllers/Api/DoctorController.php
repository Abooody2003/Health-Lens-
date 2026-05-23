<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * List doctors (with optional filters)
     */
    public function index(Request $request)
    {
        $query = Doctor::query()
            ->where('is_active', true)
            ->with('specialization');

        // Filter by specialization_id
        if ($request->filled('specialization_id')) {
            $query->where('specialization_id', $request->specialization_id);
        }

        // Filter by specialization slug (e.g. eye, skin)
        if ($request->filled('specialization_slug')) {
            $query->whereHas('specialization', function ($q) use ($request) {
                $q->where('slug', $request->specialization_slug);
            });
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Filter by area
        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        // Search by doctor name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $doctors = $query
            ->orderBy('name')
            ->paginate(
                $request->integer('per_page', 20)
            );

        return response()->json([
            'success' => true,
            'data' => $doctors->items(),
            'meta' => [
                'current_page' => $doctors->currentPage(),
                'last_page'    => $doctors->lastPage(),
                'per_page'     => $doctors->perPage(),
                'total'        => $doctors->total(),
            ],
        ]);
    }

    /**
     * Show single doctor
     */
    public function show($id)
    {
        $doctor = Doctor::with('specialization')
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $doctor,
        ]);
    }

    /**
     * Get list of available cities (for filtering)
     */
    public function getCities()
    {
        $cities = Doctor::where('is_active', true)
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return response()->json([
            'success' => true,
            'data' => $cities->values(),
        ]);
    }

    /**
     * Get list of available areas (for filtering)
     * Optionally filtered by city
     */
    public function getAreas(Request $request)
    {
        $query = Doctor::where('is_active', true)
            ->whereNotNull('area');

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $areas = $query->distinct()
            ->orderBy('area')
            ->pluck('area');

        return response()->json([
            'success' => true,
            'data' => $areas->values(),
        ]);
    }
}
