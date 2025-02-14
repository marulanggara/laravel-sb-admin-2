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
        $perPage = $request->get('per_page', 25);
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

        // Panggil fungsi add payment dari model
        $paymentId = Payment::addPayment($request->all());

        // Ambil data payment yang baru saja disimpan
        $newPayment = DB::table('payments')->where('id', $paymentId)->latest()->first();

        // Simpan Log payment
        $logData = [
            'user_id' => auth()->user()->id,
            'payment_id' => $newPayment->id,
            'action' => 'create',
            'old_data' => json_encode([]),
            'new_data' => json_encode([
                'id' => $newPayment->id,
                'supplier_id' => $newPayment->supplier_id,
                'total_price' => $newPayment->total_price,
                'status' => $newPayment->status,                
            ]),
        ];

        // Simpan ke log payment
        PaymentHistory::create($logData);
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

        // Ambil data pembayaran berdasarkan ID
        $payment = Payment::findOrFail($id);

        // Simpan data lama sebelum update
        $oldStatus = $payment->status;
        $oldReceived = $payment->is_received;

        // Update status pembayaran
        $payment->status = $request->status;
        $payment->is_received = $request->is_received ?? $payment->is_received;
        $payment->save();

        // Simpan log untuk status perubahan
        $logData = [
            'user_id' => auth()->user()->id,
            'payment_id' => $payment->id,
            'action' => 'update',
            'old_data' => json_encode([
                'status' => $oldStatus,
                'is_received' => $oldReceived,
            ]),
            'new_data' => json_encode([
                'status' => $payment->status,
                'is_received' => $payment->is_received,
            ]),
        ];

        // Simpan ke log payment
        PaymentHistory::create($logData);

        // Jika status pembayaran adalah 'lunas', update quantity produk di warehouse
        if ($payment->status == 'lunas') {
            foreach ($payment->items as $item) {
                // Ambil produk yang terkait dengan payment item
                $product = $item->product;

                if ($product) {
                    // Tambahkan quantity produk berdasarkan quantity yang ada di payment item
                    $newQuantity = $product->quantity + $item->quantity;
                    $product->quantity = $newQuantity;
                    $product->save();  // Simpan perubahan quantity ke produk

                    // Simpan log perubahan quantity dan product yang masuk
                    $logData = [
                        'user_id' => auth()->user()->id,
                        'payment_id' => $payment->id,
                        'action' => 'update',
                        'old_data' => json_encode([
                            'product_id' => $product->id,
                            'quantity' => $product->quantity - $item->quantity,
                        ]),
                        'new_data' => json_encode([
                            'product_id' => $product->id,
                            'quantity' => $product->quantity,
                        ]),
                    ];
                }
            }
        }
        return redirect()->route('payments.index')->with('success', 'Payment status updated successfully');
    }

    public function processPayment(Request $request)
    {
        $paymentId = $request->input('payment_id');
        $paymentStatus = $request->input('payment_status');
        $isReceived = $request->input('is_received', false);

        // Ambil data payment yang dipilih
        $payment = Payment::with('items')->findOrFail($paymentId);
        if (!$payment) {
            return redirect()->back()->with('error', 'Payment not found');
        }

        // Simpan data lama sebelum update
        $oldStatus = $payment->status;
        $oldIsReceived = $payment->is_received;

        try {
            // Cek jika status pembayaran adalah 'lunas'
            if ($paymentStatus == 'lunas' || $paymentStatus == 'on progress') {
                if ($payment->is_received) {
                    // Barang sudah diterima, hanya update status
                    $payment->status = $paymentStatus;
                } else {
                    // Barang belum diterima, maka proses quantity dan update status
                    $payment->status = $paymentStatus;
                    $payment->is_received = true;

                    // \Log::info('Barang diterima, mulai proses penambahan quantity ke warehouse.');

                    // Loop untuk setiap item dan update quantity di warehouse
                    foreach ($payment->items as $item) {
                        // Cek apakah barang sudah ada di warehouse
                        $warehouse = Warehouse::where('product_id', $item->product_id)
                            ->where('supplier_id', $payment->supplier_id)
                            ->where('unit_id', $item->unit_id)
                            ->where('price', $item->price)
                            ->where('payment_id', $payment->id)
                            ->whereNull('deleted_at')
                            ->first();

                        if ($warehouse) {
                            $oldQuantity = $warehouse->quantity;
                            // Jika barang sudah ada di warehouse, tambah quantity-nya
                            $warehouse->quantity += $item->quantity;
                            $warehouse->save();
                        } else {
                            $oldQuantity = 0;
                            // Jika barang belum ada, buat entri baru di warehouse
                            Warehouse::create([
                                'payment_id' => $payment->id,
                                'product_id' => $item->product_id,
                                'supplier_id' => $payment->supplier_id,
                                'unit_id' => $item->unit_id,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                            ]);
                        }

                        // Simpan log perubahan quantity dan product yang masuk
                        $logData = [
                            'user_id' => auth()->user()->id,
                            'payment_id' => $payment->id,
                            'action' => 'update',
                            'old_data' => json_encode([
                                'product_id' => $item->product_id,
                                'quantity' => $oldQuantity,
                            ]),
                            'new_data' => json_encode([
                                'product_id' => $item->product_id,
                                'quantity' => $warehouse->quantity,
                            ]),
                        ];
                    }
                }
            } else {
                // Jika statusnya bukan 'lunas', update status dan is_received sesuai data
                $payment->status = $paymentStatus;
                $payment->is_received = $isReceived;
            }

            // Simpan perubahan status dan is_received
            $payment->save();

            // Simpan log untuk status perubahan
            $logData = [
                'user_id' => auth()->user()->id,
                'payment_id' => $payment->id,
                'action' => 'update',
                'old_data' => json_encode([
                    'status' => $oldStatus,
                    'is_received' => $oldIsReceived,
                ]),
                'new_data' => json_encode([
                    'status' => $payment->status,
                    'is_received' => $payment->is_received,
                ]),
            ];
            
            PaymentHistory::create($logData);
            return redirect()->route('payments.index')->with('success', 'Payment updated successfully');
        } catch (\Exception $e) {
            // \Log::error('Gagal memproses pembayaran: ' . $e->getMessage());
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
