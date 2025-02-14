<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Log\ProductHistory;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Warehouse;

class ProductController extends Controller
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
        // Ambil data products dan join dengan unit
        $search = $request->input('search');
        $perPage = $request->get('per_page', 25);

        // Jika ada pencarian, ambil data yang sesuai
        if ($search) {
            $products = Product::searchProduct($search);
        } else {
            $products = Product::getAllProductsWithUnits($perPage);
        }
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $units = Unit::with('products')->get();
        $units = Unit::all();
        return view('products.add', compact('units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Store new product to database using eloquent
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:5|unique:products,code',
            'unit_id' => 'required|exists:units,id',
        ]);
        
        // save data
        Product::addProduct($request->all());

        // Ambil data product yang baru saja disimpan
        $newProduct = DB::table('products')->where('code', $request->code)->first();

        // Simpan log product
        $logData = [
            'user_id' => auth()->user()->id,
            'product_id' => $newProduct->id,
            'action' => 'create',
            'old_data' => json_encode([]),
            'new_data' => json_encode($newProduct),
        ];

        // Simpan ke log product
        ProductHistory::create($logData);

        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::getProductById($id);
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::find($id);
        $units = Unit::all();

        return view('products.edit', compact('product', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Update product to database
        $request->validate([
            'name' => 'required',
            'unit_id' => 'required|exists:units,id',
        ]);
        
        // Cari product berdasarkan id
        $product = DB::table('products')->where('id', $id)->first();

        if(!$product) {
            return redirect()->route('products.index')->with('error', 'Product not found');
        }

        // Catat perubahan data sebelum update (old_data)
        $old_data = (array) $product; // Mengambil hasil data sebelum update

        // Update product dengan query builder
        $updated = Product::updateProduct($request->all(), $id);
        if ($updated) {
            // Ambil data terbaru dari product setelah update (new_data)
            $new_data = DB::table('products')->where('id', $id)->first();
            $new_data = (array) $new_data; // Mengambil data terbaru

            // Simpan perubahan data ke log
            ProductHistory::create([
                'user_id' => auth()->user()->id,
                'product_id' => $id,
                'action' => 'update',
                'old_data' => json_encode($old_data),
                'new_data' => json_encode($new_data),
            ]);
            return redirect()->route('products.index')->with('success', 'Product updated successfully');
        }
        // Jika product tidak ditemukan
        return redirect()->route('products.index')->with('error', 'Product not found');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Product not found');
        }

        // Simpan log sebelum dihapus
        $logData = [
            'user_id' => auth()->user()->id,
            'product_id' => $id,
            'action' => 'delete',
            'old_data' => json_encode($product),
            'new_data' => json_encode([]),
        ];
        ProductHistory::create($logData);

        // Hapus product
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }

    // Show log product
    public function showLog()
    {
        $logs = ProductHistory::with('product')->latest()->paginate(25);
        return view('products.logs', compact('logs'));     
    }

    // Get warehouse product
    public function getWarehouseProduct()
    {
        // Ambil data warehouse yang memiliki quantity lebih dari 0, dan kelompokkan berdasarkan product_id
        $products = Warehouse::with('product')
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'asc') // Untuk menerapkan FIFO berdasarkan created_at atau gunakan field lain untuk FIFO
            ->get()
            ->groupBy('product_id'); // Mengelompokkan berdasarkan product_id

        // Format data yang akan dikirim ke frontend
        $formattedProducts = $products->map(function ($warehouses, $productId) {
            $firstWarehouse = $warehouses->first();

            if (!$firstWarehouse || !$firstWarehouse->product) {
                return null;
            }

            return [
                'product_id' => $productId,
                'product_name' => $warehouses->first()->product->name, // Ambil nama produk dari warehouse pertama
                'product_code' => $warehouses->first()->product->code,
                'warehouses' => $warehouses->map(function ($warehouse) {
                    return [
                        'warehouse_id' => $warehouse->id, // Nama warehouse
                        'selling_price' => $warehouse->selling_price,
                        'quantity' => $warehouse->quantity
                    ];
                })->values()
            ];
        })->filter()->values();

        return response()->json($formattedProducts);
    }

    // get product detail berdasarkan warehouse_id
    public function getProductDetail($id)
    {
        $warehouse = Warehouse::with('product')->find($id);

        if(!$warehouse) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }

        return response()->json([
            'product_name' => $warehouse->product->name,
            'code' => $warehouse->product->code,
            'quantity' => $warehouse->quantity,
            'unit_name' => $warehouse->product->unit->name,
            'selling_price' => $warehouse->selling_price
        ]);
    }
}
