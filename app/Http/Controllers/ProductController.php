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
        // Validasi input
        $request->validate([
            'name' => 'required',
            'unit_id' => 'required|exists:units,id',
        ]);

        // Panggil model untuk melakukan update dan pencatatan log
        $result = Product::updateProduct($request->all(), $id);

        if ($result) {
            return redirect()->route('products.index')->with('success', 'Product updated successfully');
        }

        return redirect()->route('products.index')->with('error', 'Product not found');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Panggil fungsi dari model
        $result = Product::deleteProduct($id);

        if ($result) {
            return redirect()->route('products.index')->with('success', 'Product deleted successfully');
        }

        return redirect()->route('products.index')->with('error', 'Product not found');
    }

    // Show log product
    public function showLog()
    {
        $logs = ProductHistory::with('product')->latest()->paginate(25);
        return view('products.logs', compact('logs'));     
    }
}
