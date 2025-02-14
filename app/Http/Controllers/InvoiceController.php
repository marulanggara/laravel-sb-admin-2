<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;
use Log;
use Carbon\Carbon;


use App\Models\Warehouse;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;

class InvoiceController extends Controller
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
        // Ambil semua data invoice
        $search = $request->input('search');
        $perPage = $request->get('per_page', 25);

        if ($search) {
            $invoices = Invoice::searchInvoice($search);
        } else {
            $invoices = Invoice::getAllInvoices($perPage);
        }
        return view('invoice.index', compact('invoices'));
    }

    public function create()
    {
        $products = Product::with('unit')
            ->whereHas('warehouses') // Hanya produk yang ada di warehouse
            ->get();

        return view('invoice.add', compact('products'));
    }

    public function getProductDetail($id)
    {
        $warehouse = Warehouse::with('product')->findOrFail($id);

        return response()->json([
            'code' => $warehouse->product->code,
            'selling_price' => $warehouse->selling_price,
        ]);
    }

    public function getProductStock($product_id)
    {
        $warehouse = Warehouse::where('product_id', $product_id)
            ->where('quantity', '>', 0) // Hanya stok yang tersedia
            ->orderBy('created_at', 'asc') // FIFO: stok masuk pertama digunakan dulu
            ->first();

        if ($warehouse) {
            return response()->json([
                'warehouse_id' => $warehouse->id,
                'product_code' => $warehouse->product->code,
                'unit_name' => $warehouse->unit->name,
                'selling_price' => $warehouse->selling_price,
                'available_stock' => $warehouse->quantity
            ]);
        }

        return response()->json(['error' => 'No stock available'], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items.*.warehouse_id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $invoice = Invoice::create([
                'invoice_no' => $request->invoice_no,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'created_by' => auth()->user()->name,
                'total_price' => 0,
                'created_at' => now(),
            ]);

            $totalPrice = 0;
            foreach ($request->items as $item) {
                $warehouse = Warehouse::findOrFail($item['warehouse_id']);
                $totalItemPrice = $warehouse->selling_price * $item['quantity'];

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $warehouse->product_id,
                    'product_name' => $warehouse->product->name,
                    'product_code' => $warehouse->product->code,
                    'unit_name' => $warehouse->unit->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $warehouse->selling_price,
                    'total_price' => $totalItemPrice,
                    'created_at' => now(),
                ]);

                $warehouse->decrement('quantity', $item['quantity']);
                $totalPrice += $totalItemPrice;
            }
            
            $invoice->update(['total_price' => $totalPrice]);
        });

        return redirect()->route('invoice.add')->with('success', 'Invoice berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    // InvoiceController.php
    public function show($id)
    {
        // Ambil data invoice berdasarkan ID
        $invoice = Invoice::with('items')->findOrFail($id);
        // Kirim data invoice dan item invoice ke view
        return view('invoice.show', compact('invoice'));
    }

    public function downloadPdf($id)
    {
        // Ambil data invoice berdasarkan ID
        $invoice = Invoice::with('items')->findOrFail($id);
        // Generate PDF dari tampilan blade
        $pdf = Pdf::loadView('invoice.pdf', compact('invoice'));
        // Mengunduh PDF
        return $pdf->download('Invoice_' . $invoice->invoice_no . '.pdf');
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('q');

        $products = Product::whereRaw("LOWER(name) LIKE ?", ['%' . strtolower($search) . '%'])
                ->whereHas('warehouses', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->limit(10)
            ->get();

        $results = $products->map(function ($product) {
            // Mengambil warehouse dengan stok tersedia menggunakan FIFO
            $warehouse = $product->warehouses()
                ->where('quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->first();

            if (!$warehouse) {
                return null; // Meskipun seharusnya sudah terpenuhi kondisi whereHas
            }

            return [
                'id' => $product->id,
                'text' => $product->name,
                'available_stock' => $warehouse->quantity,
                'warehouse_id' => $warehouse->id,
                'product_code' => $warehouse->product->code,
                'unit_name' => $warehouse->unit->name,
                'selling_price' => $warehouse->selling_price,
            ];
        })->filter()->sortBy('text')->values(); // Menghapus data null dan mereset index array

        return response()->json(['results' => $results]);
    }

}
