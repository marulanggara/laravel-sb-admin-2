<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->get('per_page', 25);

        $units = Unit::when($search, function ($query, $search) {
            return $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        })->paginate($perPage);
        return view('units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255'
        ]);

        Unit::create([
            'name' => $request->unit_name,
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Unit berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $unit = Unit::find($id);
        return response()->json($unit);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255'
        ]);

        $unit = Unit::findOrFail($id);
        $unit->update([
            'name' => $request->unit_name,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Unit berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->back()->with('success', 'Unit berhasil dihapus!');
    }
}
