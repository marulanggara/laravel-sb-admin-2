@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Detail Invoice</h5>
        </div>

        {{-- Form Input Edit Supplier --}}
        <div class="card-body">
            {{-- Export invoice to PDF Button --}}
            <div class="form-group d-flex justify-content-end">
                <a href="{{ route('invoice.download', $invoice->id) }}" class="btn btn-danger">
                    <i class="fa-solid fa-download m-1"></i>Download</a>
            </div>  

            {{-- Invoice Information --}}
            <h4 class="mb-4">Invoice Information</h4>
            {{-- Supplier --}}
            <table class="table table-bordered">
                <tr>
                    <th>Invoice No.</th>
                    <td>{{ $invoice->invoice_no }}</td>
                </tr>
                <tr>
                    <th>Created By</th>
                    <td>{{ $invoice->created_by }}</td>
                </tr>
                <tr>
                    <th>Tanggal Invoice</th>
                    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y, H:i:s') }}</td>
                </tr>
                <tr>
                    <th>Tanggal Lunas</th>
                    <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y, H:i:s') }}</td>
                </tr>
            </table>

            {{-- Invoice Items --}}
            <h4 class="mb-4">Invoice Items</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Item Name</th>
                        <th>Code Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->product_code }}</td>
                            <td class="prices" data-price="{{ $item->unit_price }}">{{ $item->unit_price }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="prices" data-price="{{ $item->total_price }}">{{ $item->total_price }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="text-center"><b>Total</b></td>
                        <td>{{ $items->sum('quantity') }}</td>
                        <td class="prices" data-price="{{ $items->sum('total_price') }}">{{ $items->sum('total_price') }}</td>
                    </tr>
                </tbody>
            </table>
            <a href="{{ route('invoice.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>

@endsection