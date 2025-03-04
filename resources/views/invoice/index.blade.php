@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h4 class="m-0 font-weight-bold text-primary">Invoice List</h4>
        </div>

        {{-- Add New Products Button and search Button --}}
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    @can('create invoice')
                        <a href="{{ route('invoice.add') }}" class="btn btn-primary">+ Create Invoice</a>
                    @else
                        <a href="#" class="btn btn-primary disabled">+ Create Invoice</a>
                    @endcan
                    @can('list invoice')
                        <a href="{{ route('invoice.logs') }}" class="btn btn-primary">Log History</a>
                    @else
                        <a href="#" class="btn btn-primary disabled">Log History</a>
                    @endcan
                </div>
                <div class="col-md-6">
                    <form action="{{ route('invoice.index') }}" method="get">
                        @can('list invoice')
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" name="search"
                                 placeholder="Search by No. Invoice" aria-label="Search"
                                aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" name="search"
                                 placeholder="Search by No. Invoice" aria-label="Search"
                                aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" disabled>
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                        @endcan
                    </form>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <form action="{{ route('invoice.index') }}" method="get">
                        <label for="per_page">Show</label>
                        <select name="per_page" id="per_page" class="form-control d-inline-block w-auto"
                            onchange="this.form.submit()">
                            <option value="25" {{ request()->per_page == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request()->per_page == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request()->per_page == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span>entries</span>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>No. Invoice</th>
                            <th>Total Quantity</th>
                            <th>Total Price</th>
                            <th>Tanggal Invoice</th>
                            <th>Created By</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    {{-- Integrate product data --}}
                    <tbody>
                        @if(count($invoices) == 0)
                        <tr>
                            <td colspan="7" class="text-center">No Data</td>
                        </tr>
                        @else
                            @foreach($invoices as $key => $invoice)
                                <tr>
                                    <td>{{ $invoices->firstItem() + $key }}</td>
                                    <td>{{ $invoice->invoice_no }}</td>
                                    <td>{{ $invoice->total_quantity }}</td>
                                    <td class="prices" data-price="{{ $invoice->total_price }}">{{ $invoice->total_price }}</td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y, H:i:s') }}</td>
                                    <td>{{ ($invoice->created_by) }}</td>
                                    <td>
                                        @can('update invoice')
                                        <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-sm transparent"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @else
                                        <a href="#" class="btn btn-sm transparent disabled"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $invoices->appends(['per_page' => request()->per_page])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection