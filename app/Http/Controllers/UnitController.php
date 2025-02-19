<?php

namespace App\Http\Controllers;

use App\Models\Log\UnitHistory;
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
            return $query->where('name', 'ILIKE', '%' . $search . '%');
        })->paginate($perPage);
        return view('units.index', compact('units'));
    }

    public function showLog()
    {
        $logs = UnitHistory::with('unit')->latest()->paginate(25);
        return view('units.logs', compact('logs'));
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

        $unit = Unit::create([
            'name' => $request->unit_name,
            'created_at' => now(),
            'updated_at' => null
        ]);

        // Simpan data ke log
        $logData = [
            'user_id' => auth()->user()->id,
            'unit_id' => $unit->id,
            'action' => 'create',
            'old_data' => json_encode([]),
            'new_data' => json_encode($unit),
            'created_at' => now(),
            'updated_at' => null
        ];
        // Tambahkan log ke database
        UnitHistory::create($logData);

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

        // Simpan data lama
        $oldData = $unit->toArray();

        $unit->update([
            'name' => $request->unit_name,
            'updated_at' => now(),
        ]);

        // Simpan data ke log
        $logData = [
            'user_id' => auth()->user()->id,
            'unit_id' => $unit->id,
            'action' => 'update',
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($unit),
            'created_at' => now(),
            'updated_at' => null
        ];
        // Tambahkan log ke database
        UnitHistory::create($logData);

        return redirect()->back()->with('success', 'Unit berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);

        // Simpan data lama
        $oldData = $unit->toArray();

        $unit->delete();

        // Simpan data ke log
        $logData = [
            'user_id' => auth()->user()->id,
            'unit_id' => $unit->id,
            'action' => 'delete',
            'old_data' => json_encode($oldData),
            'new_data' => null,
            'created_at' => now(),
            'updated_at' => null,
        ];
        // Tambahkan log ke database
        UnitHistory::create($logData);

        return redirect()->back()->with('success', 'Unit berhasil dihapus!');
    }
}
