<?php

namespace App\Http\Controllers;

use App\Models\Log\SupplierHistory;
use App\Models\Product;
use App\Models\SupplierProduct;
use Illuminate\Http\Request;
use DB;
use App\Models\Supplier;

class SupplierController extends Controller
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
        $search = $request->input('search');
        $perPage = $request->get('per_page', 25);
        if ($search) {
            $suppliers = Supplier::searchSupplier($search);
        } else {
            $suppliers = Supplier::getAllSuppliersWithProducts($perPage);
        }
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Simpan data supplier, ambil data nama product pada tabel product yang terhubung pada tabel supplier_product
        $products = Product::with('unit')->get(); // Ambil product dan satuan
        return view('suppliers.add', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact' => 'required|numeric',
            'pic_name' => 'required|string|max:255',
            'products' => 'array',
            'products.*.id' => 'exists:products,id',
            'products.*.price' => 'required|numeric',
        ]);

        // Save data supplier
        Supplier::addSupplier($request->all());

        // Ambil data yang baru saja disimpan
        $newSupplier = DB::table('suppliers')->where('name', $request->name)->first();

        // Simpan Log supplier
        $logData = [
            'user_id' => auth()->user()->id,
            'supplier_id' => $newSupplier->id,
            'action' => 'create',
            'old_data' => json_encode([]),
            'new_data' => json_encode($newSupplier),
        ];

        // Simpan log supplier
        SupplierHistory::create($logData);

        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supplier = Supplier::findOrFail($id);

        if (!$supplier) {    
            return redirect()->route('suppliers.index')->with('error', 'Supplier not found');
        }
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $products = Product::with('unit')->get(); // Ambil product dan satuan
        $selectedProducts = $supplier->products->pluck('id')->toArray(); // Ambil id product yang terpilih
        $productPrices = $supplier->products->pluck('pivot.price', 'id')->toArray(); // Ambil harga product yang terpilih dari supplier_product

        return view('suppliers.edit', compact('supplier', 'products', 'selectedProducts', 'productPrices'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact' => 'required|numeric',
            'pic_name' => 'required|string|max:255',
            'products' => 'array',
            'products.*.id' => 'exists:products,id',
            'products.*.price' => 'required|numeric',
        ]);

        // Update supplier
        $updateSupplier = Supplier::updateSupplier($id, $request->all());

        if (!$updateSupplier) {
            return redirect()->route('suppliers.index')->with('error', 'Supplier not found or failed to update');
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Hapus supplier dari database
        $supplier = Supplier::findOrFail($id);
        if (!$supplier) {
            return redirect()->route('suppliers.index')->with('error', 'Supplier not found or failed to delete');
        }

        // Simpan log supplier
        $logData = [
            'user_id' => auth()->user()->id,
            'supplier_id' => $id,
            'action' => 'delete',
            'old_data' => json_encode($supplier),
            'new_data' => json_encode([]),
        ];
        SupplierHistory::create($logData);

        // Hapus supplier
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully');
    }

    // Show supplier log
    public function showLog()
    {
        $logs = SupplierHistory::with('supplier')->latest()->paginate(25);
        return view('suppliers.logs', compact('logs'));
    }
}
