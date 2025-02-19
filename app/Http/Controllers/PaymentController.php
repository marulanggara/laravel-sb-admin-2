<?php

namespace App\Http\Controllers;

use App\Models\SupplierProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\Log\PaymentHistory;

class PaymentController extends Controller
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
        $perPage = $request->get('per_page', $perPage = 25);
        if ($search) {
            $payments = Payment::searchPayment($search);
        } else {
            $payments = Payment::getAllPayment($perPage);
        }
        return view('payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = DB::table('suppliers')->whereNull('deleted_at')->get();
        return view('payments.add', compact('suppliers'));
    }

    // Fungsi untuk ambil data product yang terkait dengan supplier
    public function getProductsBySupplier(Request $request)
    {
        $supplier_id = $request->input('supplier_id');
        $products = SupplierProduct::getProduct($supplier_id);
        
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // Validasi input
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.product_code' => 'required|string|max:255',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.unit_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric',
            'items.*.price' => 'required|numeric',
        ]);

        // Panggil fungsi addPayment
        $payment = Payment::addPayment($request->all());
        // Simpan log payments
        PaymentHistory::create([
            'user_id' => auth()->user()->id,
            'payment_id' => $payment->id,
            'action' => 'create',
            'old_data' => json_encode([]),
            'new_data' => json_encode([
                'id' => $payment->id,
                'supplier_id' => $payment->supplier_id,
                'total_price' => $payment->total_price,
                'status' => $payment->status,
            ]),
        ]);

        return redirect()->route('payments.index')->with('success', 'Payment created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Ambil data payment termasuk data item terkait
        $payment = Payment::getPaymentById($id);

        if (!$payment) {
            return redirect()->route('payments.index')->with('error', 'Payment not found');
        }
        return view('payments.show', compact('payment'));
    }

    // Fungsi update payment status
    public function updateStatus(Request $request, $id)
    {
        // Validasi status yang dikirim
        $request->validate([
            'status' => 'required|string|in:on progress,lunas,cancelled',
            'is_received' => 'nullable|boolean',
        ]);

        // Panggil fungsi updateStatus
        Payment::updateStatus($id, $request->all());
        return redirect()->route('payments.index')->with('success', 'Payment status updated successfully');
    }

    public function processPayment(Request $request)
    {
        // Validasi input
        $paymentId = $request->input('payment_id');
        $paymentStatus = $request->input('payment_status');
        $isReceived = $request->input('is_received', false);

        try {
            // Panggil fungsi processPayment
            Payment::processPaymentFunction($paymentId, $paymentStatus, $isReceived,  $request);

            return redirect()->route('payments.index')->with('success', 'Payment processed successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }


    // Show log paymen
    public function showLog()
    {
        $logs = PaymentHistory::with('payment')->latest()->paginate(25);
        return view('payments.logs', compact('logs'));
    }
}
