<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->subYear()->format('d-m-Y');
            $endDate = Carbon::now()->format('d-m-Y');
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        // Ambil bulan-bulan dalam rentang tanggal yang dipilih
        $startDate = Carbon::createFromFormat('d-m-Y', $startDate);
        $endDate = Carbon::createFromFormat('d-m-Y', $endDate);

        $monthsAvailable = [];
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            // Hitung total price per bulan
            $totalPrice = DB::table('payments')
                        ->whereYear('created_at', $currentDate->year)
                        ->whereMonth('created_at', $currentDate->month)
                        ->sum('total_price');

            $monthsAvailable[] = [
                'num' => $currentDate->month,
                'month' => $months[$currentDate->month],
                'year' => $currentDate->year,
                'total_price' => $totalPrice
            ];
            $currentDate->addMonth();
            // Jika ada rentang tanggal yang dipilih, ambil bulan-bulan dalam rentang tersebut
        }
        $monthsAvailable = array_reverse($monthsAvailable);
        // jika data berasal dari ajax, kembalikan response json
        if ($request->ajax()) {
            return response()->json(['monthsAvailable' => $monthsAvailable]);
        }

        return view('expenses.index', compact('monthsAvailable', 'startDate', 'endDate'));
    }

    public function show(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Ambil data invoice berdasarkan bulan dan tahun
        $query = DB::table('payments')
            ->leftJoin('suppliers', 'payments.supplier_id', '=', 'suppliers.id')
            ->select(
                'payments.total_price',
                'payments.id',
                'payments.created_at',
                'payments.created_by',
                'suppliers.name as supplier_name',
            )
            ->whereYear('payments.created_at', $year)
            ->whereMonth('payments.created_at', $month)
            ->orderBy('payments.created_at', 'desc');

        // Jika ada rentang tanggal, filter berdasarkan rentang tersebut
        if ($startDate && $endDate) {
            $query->whereBetween('payments.created_at', [$startDate, $endDate]);
        }

        $payments = $query->get();

        $monthName = Carbon::createFromFormat('m', $month)->translatedFormat('F');
        return view('expenses.show', compact('payments', 'monthName', 'year', 'startDate', 'endDate'));
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
        //
    }

    /**
     * Display the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
