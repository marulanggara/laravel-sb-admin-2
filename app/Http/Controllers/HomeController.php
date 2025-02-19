<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // Ambil bulan dan tahun saat ini
    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    // Query untuk mendapatkan total pengeluaran bulan ini
    $totalExpenseThisMonth = DB::table('payments')
        ->whereYear('created_at', $currentYear)
        ->whereMonth('created_at', $currentMonth)
        ->sum('total_price'); // Ganti 'amount' dengan kolom yang sesuai di tabel payments

    // Query untuk mendapatkan total income bulan ini
    $totalIncomeThisMonth = DB::table('invoices')
        ->whereYear('invoice_date', $currentYear)
        ->whereMonth('invoice_date', $currentMonth)
        ->sum('total_price');

    return view('home', compact('totalExpenseThisMonth', 'totalIncomeThisMonth'));
    }

    // Chart function
    public function chart(Request $request)
    {
        // Ambil bulan dan tahun saat ini
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Query untuk mendapatkan total pengeluaran bulan ini
        $dailyExpenseThisMonth = DB::table('payments')
                ->selectRaw('to_char(created_at, \'YYYY-MM-DD\') as date, sum(total_price) as total_expense')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->groupByRaw('to_char(created_at, \'YYYY-MM-DD\')')
                ->orderByRaw('to_char(created_at, \'YYYY-MM-DD\')')
                ->get();

        $dailyIncomeThisMonth = DB::table('invoices')
        ->selectRaw('to_char(invoice_date, \'YYYY-MM-DD\') as date, sum(total_price) as total_income')
        ->whereYear('invoice_date', $currentYear)
        ->whereMonth('invoice_date', $currentMonth)
        ->groupByRaw('to_char(invoice_date, \'YYYY-MM-DD\')')
        ->orderByRaw('to_char(invoice_date, \'YYYY-MM-DD\')')
        ->get();
        

        return response()->json([
            'dailyExpenseThisMonth' => $dailyExpenseThisMonth, 
            'dailyIncomeThisMonth' => $dailyIncomeThisMonth
        ]);
    }
}
