<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class IncomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        // $year = Carbon::now()->year; // Ambil tahun lalu
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if(!$startDate || !$endDate) {
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
            $totalPrice = DB::table('invoices')
                        ->whereYear('invoice_date', $currentDate->year)
                        ->whereMonth('invoice_date', $currentDate->month)
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
        if ($request->ajax()) {
            return response()->json(['monthsAvailable' => $monthsAvailable]);
        }

        return view('incomes.index', compact('monthsAvailable', 'startDate', 'endDate'));
    }

    public function show(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Ambil data invoice berdasarkan bulan dan tahun
        $query = DB::table('invoices')
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->orderBy('invoice_date', 'desc');

        // Jika ada rentang tanggal, filter berdasarkan rentang tersebut
        if ($startDate && $endDate) {
            $query->whereBetween('invoice_date', [$startDate, $endDate]);
        }

        $invoices = $query->get();

        $monthName = Carbon::createFromFormat('m', $month)->translatedFormat('F');

        return view('incomes.show', compact('invoices', 'monthName', 'year', 'startDate', 'endDate'));
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
