@extends('layouts.admin')

@section('main-content')

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">{{ __('Dashboard') }}</h1>

    @if (session('success'))
    <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success border-left-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="row">

        <!-- Earnings (Monthly) Card Example -->
        <div class=" col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-s font-weight-bold text-primary text-uppercase mb-1">Income (Monthly)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 prices" data-price="{{ $totalIncomeThisMonth }}">{{ $totalIncomeThisMonth }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-s font-weight-bold text-success text-uppercase mb-1">Expense (Monthly)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 prices" data-price="{{ $totalExpenseThisMonth }}">{{ $totalExpenseThisMonth}}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <!-- Content Column -->
        <div class="col-lg-12 mb-4">

            <!-- Project Card Example -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h3 class="m-0 font-weight-bold text-primary">Chart</h3>
                </div>
                <div class="card-body">
                    <canvas id="incomeExpenseChart"></canvas>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        fetch('/home/chart')
        .then(response => response.json())
        .then(data => {
            let labels = [];
            let incomeData = [];
            let expenseData = [];

            // Format tanggal menjadi dd-mm-yyyy
            data.dailyIncomeThisMonth.forEach(element => {
                let date = new Date(element.date);
                let formattedDate = date.getDate().toString().padStart(2, '0') + '-' + (date.getMonth() + 1).toString().padStart(2, '0') + '-' + date.getFullYear();
                labels.push(formattedDate);
                incomeData.push(element.total_income);
            });

            data.dailyExpenseThisMonth.forEach(element => {
                let date = new Date(element.date);
                let formattedDate = date.getDate().toString().padStart(2, '0') + '-' + (date.getMonth() + 1).toString().padStart(2, '0') + '-' + date.getFullYear();
                if (!labels.includes(formattedDate)) {
                    labels.push(formattedDate);
                }
                expenseData.push(element.total_expense);
            });

            // Ubah Set menjadi Array dan urutkan berdasarkan tanggal
            labels = [...new Set(labels)].sort((a, b) => {
                const dateA = new Date(a.split('-').reverse().join('-')); // Konversi 'dd-mm-yyyy' ke Date
                const dateB = new Date(b.split('-').reverse().join('-')); // Konversi 'dd-mm-yyyy' ke Date
                return dateA - dateB;
            });

            // Inisialisasi Chart.js
            const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Income',
                            data: incomeData,
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Expense',
                            data: expenseData,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
    });

    </script>
@endpush