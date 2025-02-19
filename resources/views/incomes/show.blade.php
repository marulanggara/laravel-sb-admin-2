@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">
    <div class="card">
        <div class="card-header">
            <h4 class="m-0 font-weight-bold text-primary">Income List - {{ $monthName }} {{ $year }}</h4>
        </div>
        <div class="card-body">
        <a href="{{ route('incomes.index') }}" class="btn btn-secondary mb-2">Back</a>
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
                            <th>Tanggal Invoice</th>
                            <th>Created By</th>
                            <th>Invoice No.</th>
                            <th>Total Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($invoices->count() == 0)
                        <tr>
                            <td colspan="5" class="text-center">No data for this month</td>
                        </tr>
                        @endif
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y, H:i:s') }}</td>
                                <td>{{ $invoice->created_by }}</td>
                                <td>{{ $invoice->invoice_no }}</td>
                                <td class="prices" data-price="{{ $invoice->total_price }}">{{ $invoice->total_price }}</td>
                                <td>
                                    <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-sm transparent"><i class="fa-solid fa-eye fa-lg"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <th colspan="3" class="text-center">Total</th>
                            <th>{{ $invoices->count() }}</th>
                            <th class="prices" data-price="{{ $invoices->sum('total_price') }}">{{ $invoices->sum('total_price') }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(function () {
        $('input[name="daterange"]').daterangepicker({
            opens: 'left'
        }, function (start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
        });
    });
</script>

@endsection