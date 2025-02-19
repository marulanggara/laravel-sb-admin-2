<?php

namespace App\Http\Controllers;

use App\Models\Log\InvoiceHistory;
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
        $stockData = Warehouse::getAvailableStockByProductId($product_id);

        if ($stockData) {
            return response()->json($stockData);
        }
        return response()->json(['error' => 'No stock available'], 404);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'items.*.warehouse_id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        Invoice::addInvoice($request);
        return redirect()->route('invoice.index')->with('success', 'Invoice berhasil dibuat!');
    }

    public function show($id)
    {
        // Ambil data invoice berdasarkan ID
        $invoice = DB::table('invoices')->where('id', $id)->first();
        // Kelompokkan item dengan product_id yang sama
        $items = DB::table('invoice_items')
        ->select('product_id', 'product_name', 'product_code', DB::raw('MIN(unit_price) as unit_price'), DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(total_price) as total_price'))
        ->where('invoice_id', $id)
        ->groupBy('product_id', 'product_name', 'product_code')
        ->get();

        return view('invoice.show', compact('invoice', 'items'));
    }

    public function downloadPdf($id)
    {
        // Ambil data invoice berdasarkan ID
        $invoice = DB::table('invoices')->where('id', $id)->first();

        // Kelompokkan item dengan product_id yang sama
        $items = DB::table('invoice_items')
            ->select(
                'product_id',
                'product_name',
                'product_code',
                'unit_name',
                DB::raw('MIN(unit_price) as unit_price'), // Harga satuan diambil dari harga minimum (karena harus sama)
                DB::raw('SUM(quantity) as quantity'), // Menjumlahkan jumlah barang
                DB::raw('SUM(total_price) as total_price') // Menjumlahkan total harga
            )
            ->where('invoice_id', $id)
            ->groupBy('product_id', 'product_name', 'product_code', 'unit_name')
            ->get();

        // Generate PDF dari tampilan Blade
        $pdf = Pdf::loadView('invoice.pdf', compact('invoice', 'items'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHTML5ParserEnabled', true)
            ->setOption('isPhpEnabled', true)
            ->setOption('dpi', 150);
        // Mengunduh PDF
        return $pdf->download('Invoice_' . $invoice->invoice_no . '.pdf');
    }

    public function showLog()
    {
        $logs = InvoiceHistory::with('invoice')->latest()->paginate(25);
        return view('invoice.logs', compact('logs'));
    }
}
