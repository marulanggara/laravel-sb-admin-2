<?php

namespace App\Http\Controllers;

use App\Models\Log\WarehouseHistory;
use Illuminate\Http\Request;

use App\Models\Warehouse;

class WarehouseController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil semua data warehouse dengan relasi product, supplier, dan unit
        $search = $request->input('search');
        $perPage = $request->get('per_page', 25);

        if ($search) {
            $warehouses = Warehouse::searchWarehouse($search);
        } else {
            $warehouses = Warehouse::getAllWarehouse($perPage);
        }
        return view('warehouses.index', compact('warehouses'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        // Ambil data product, supplier, unit, quantity, dan price dari warehouse
        $perPage = $request->get('per_page', 25);
        $warehouses = Warehouse::getWarehouseById($id, $perPage);
        // Jika data warehouse tidak ditemukan
        if ($warehouses->isEmpty()) {
            return response()->view('warehouses.error404', [], 404);
        }

        return view('warehouses.show', compact('warehouses'));

    }
    public function update(Request $request, string $id)
    {
        // Validasi input
        $request->validate([
            'selling_price' => 'required|numeric',
        ]);

        // Ambil semua warehouse terkait product_id yang diupdate
        $warehouses = Warehouse::where('product_id', $id)->get();

        // Update selling price untuk semua warehouse terkait product_id
        foreach ($warehouses as $warehouse) {
            // Simpan data lama sebelum update
            $oldData = [
                'id' => $warehouse->id,
                'selling_price' => $warehouse->selling_price,
            ];

            $warehouse->update([
                'selling_price' => $request->selling_price,
                'updated_at' => now(),
            ]);

            // Simpan data baru setelah update
            $newData = [
                'id' => $warehouse->id,
                'selling_price' => $warehouse->selling_price,
            ];

            // Simpan log perubahan
            WarehouseHistory::create([
                'user_id' => auth()->user()->id,
                'warehouse_id' => $warehouse->id,
                'action' => 'update',
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($newData),
            ]);
        }

        // Redirect ke halaman warehouse
        return redirect()->route('warehouses.index')->with('success', 'Selling price updated successfully');
    }

    // Show log
    public function showLog()
    {
        $logs = WarehouseHistory::with('warehouse')->latest()->paginate(25);
        return view('warehouses.logs', compact('logs'));
    }
    
}
