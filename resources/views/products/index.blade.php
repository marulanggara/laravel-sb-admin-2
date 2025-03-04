@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Products List</h5>
        </div>

        {{-- Add New Products Button and search Button --}}
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    @can('create product')
                        <a href="{{ route('products.add') }}" class="btn btn-primary">+ Create Products</a>
                    @else
                        <a href="#" class="btn btn-primary disabled">+ Create Products</a>
                    @endcan
                    @can('list product')
                        <a href="{{ route('products.logs') }}" class="btn btn-primary">Log History</a>
                    @else
                        <a href="#" class="btn btn-primary disabled">Log History</a>
                    @endcan
                </div>
                <div class="col-md-6">
                    <form action="{{ route('products.index') }}" method="get">
                        @can('list product')
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" name="search" value="{{ request()->search }}" placeholder="Search by Item Name or Code" aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" name="search" placeholder="Search by Item Name or Code" aria-label="Search" aria-describedby="basic-addon2" disabled>
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
                    <form action="{{ route('products.index') }}" method="get">
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
                            <th>Item Code</th>
                            <th>Unit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    {{-- Integrate product data --}}
                    <tbody>
                        {{-- Jika tidak ada data --}}
                        @if(count($products) == 0)
                            <tr>
                                <td colspan="5" class="text-center">No data available</td>
                            </tr>
                        @else
                            @foreach($products as $key => $product)
                                <tr>
                                    <td>{{ $products->firstItem() + $key }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->code }}</td>
                                    <td>{{ $product->unit->name }}</td>
                                    <td>
                                        @can('update product')
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm transparent"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @else
                                            <a href="#" class="btn btn-sm transparent disabled"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @endcan

                                        <!-- Tombol untuk membuka modal -->
                                        @can('delete product')
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal-{{ $product->id }}">
                                            <i class="fa-solid fa-trash-can fa-lg"></i>
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-danger disabled"><i class="fa-solid fa-trash-can fa-lg"></i></button>
                                        @endcan

                                        <!-- Modal Konfirmasi Hapus -->
                                        <div class="modal fade" id="deleteModal-{{ $product->id }}" tabindex="-1" role="dialog" aria-labelledby="modalTitle"
                                            aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header alert-danger">
                                                        <h5 class="modal-title"><b>Confirm Delete</b></h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Apakah Anda yakin ingin menghapus produk ini?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Modal -->
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $products->appends(['per_page' => request()->per_page])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection