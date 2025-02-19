@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">
    <div class="card">
        <div class="card-header">
            <h4 class="m-0 font-weight-bold text-primary">Expense List - {{ $monthName }} {{ $year }}</h4>
        </div>
        <div class="card-body">
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary mb-2">Back</a>
            <div class="row">
                <div class="col-md-6">

                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="income-table">
                    <thead>
                        {{-- Tampilkan Bulan 1-12 --}}
                        <tr>
                            <th>No.</th>
                            <th>Tanggal Pembelian</th>
                            <th>Created By</th>
                            <th>Supplier Name</th>
                            <th>Total Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($payments->count() == 0)
                        <tr>
                            <td colspan="5" class="text-center">No data for this month</td>
                        </tr>
                        @endif
                        @foreach ($payments as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d-m-Y, H:i:s') }}</td>
                            <td>{{ $payment->created_by == null ? '-' : $payment->created_by }}</td>
                            <td>{{ $payment->supplier_name }}</td>
                            <td class="prices" data-price="{{ $payment->total_price }}">{{ $payment->total_price }}</td>
                            <td>
                                <a href="{{ route('payments.show', $payment->id) }}" class="btn btn-sm transparent"><i class="fa-solid fa-eye fa-lg"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        <tr>
                            <th colspan="3" class="text-center">Total</th>
                            <th>{{ $payments->count() }}</th>
                            <th class="prices" data-price="{{ $payments->sum('total_price') }}">{{ $payments->sum('total_price') }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


@endsection