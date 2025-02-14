@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Warehouse</h6>
        </div>

        {{-- Add New Warehouse Button and search Button --}}
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    @can('list warehouse')
                        <a href="{{ route('warehouses.logs') }}" class="btn btn-primary">Log History</a>
                    @else
                        <a href="#" class="btn btn-primary disabled">Log History</a>
                    @endcan
                </div>
                <div class="col-md-6">
                    <form action="{{ route('warehouses.index') }}" method="get">
                        @can('list warehouse')
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" name="search" value="{{ request()->search }}" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" name="search" placeholder="Search for..."
                            aria-label="Search" aria-describedby="basic-addon2" disabled>
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
                    <form action="{{ route('warehouses.index') }}" method="get">
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
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Selling Price</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    {{-- table list data warehouse --}}
                    <tbody>
                        {{-- Jika tidak ada data --}}
                        @if(count($warehouses) == 0)
                            <tr>
                                <td colspan="5" class="text-center">No data available</td>
                            </tr>
                        @else
                            @foreach($warehouses as $key => $warehouse)
                                <tr>
                                    <td>{{ $warehouses->firstItem() + $key }}</td>
                                    <td>{{ $warehouse->product_name }}</td>
                                    <td>{{ $warehouse->total_quantity }}</td>
                                    <td class="prices" data-price="{{ $warehouse->selling_price }}">{{ $warehouse->selling_price }}</td>
                                    <td>
                                        @can('update warehouse')
                                            <a href="{{ route('warehouses.show', $warehouse->product_id) }}" class="btn transparent btn-sm"><i class="fa-solid fa-eye fa-lg"></i></a> 
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
                    {{ $warehouses->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Supplier -->
<div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-labelledby="supplierModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supplierModalLabel">Supplier List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Supplier Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody id="supplierList">
                        <!-- Data akan dimasukkan dengan JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.view-suppliers').click(function () {
            var productId = $(this).data('id'); // Ambil product_id dari tombol
            $('#supplierList').html('<tr><td colspan="3" class="text-center">Loading...</td></tr>'); // Loading indikator

            $.ajax({
                url: '/warehouse/suppliers/' + productId,
                type: 'GET',
                success: function (response) {
                    var rows = '';

                    if (response.length > 0) {
                        response.forEach(function (supplier) {
                            rows += '<tr>' +
                                '<td>' + supplier.supplier.name + '</td>' +
                                '<td>' + supplier.quantity + '</td>' +
                                '<td>' + supplier.price + '</td>' +
                                '</tr>';
                        });
                    } else {
                        rows = '<tr><td colspan="3" class="text-center">No suppliers found</td></tr>';
                    }

                    $('#supplierList').html(rows); // Masukkan data ke dalam modal
                },
                error: function () {
                    $('#supplierList').html('<tr><td colspan="3" class="text-center text-danger">Failed to load data</td></tr>');
                }
            });
        });
    });
</script>

@endsection