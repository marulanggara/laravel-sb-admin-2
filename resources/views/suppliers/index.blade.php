@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Suppliers List</h5>
        </div>

        {{-- Add New Supplier Button and search Button --}}
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    @can('create supplier')
                        <a href="{{ route('suppliers.add') }}" class="btn btn-primary">+ Create Supplier</a>
                    @else
                        <a href="#" class="btn btn-primary disabled">+ Create Supplier</a>
                    @endcan
                    @can('list supplier')
                        <a href="{{ route('suppliers.logs') }}" class="btn btn-primary">Log History</a>
                    @else
                        <a href="#" class="btn btn-primary disabled">Log History</a>
                    @endcan
                </div>
                <div class="col-md-6">
                    <form action="{{ route('suppliers.index') }}" method="get">
                        <div class="input-group">
                            @can('list supplier')
                            <input type="text" class="form-control bg-light border-0 small" name="search" value="{{ request()->search }}" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                            @else
                            <input type="text" class="form-control bg-light border-0 small" name="search" value="{{ request()->search }}" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2" disabled>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" disabled>
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                            @endcan
                        </div>
                    </form>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <form action="{{ route('suppliers.index') }}" method="get">
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

        {{-- Table --}}
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Supplier Name</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>PIC Name</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    {{-- Create data dummy --}}
                    <tbody>
                        @if (count($suppliers) === 0)
                            <tr>
                                <td colspan="6" class="text-center">No Data Available</td>
                            </tr>
                        @else
                            @foreach ($suppliers as $key => $supplier)
                                <tr>
                                    <td>{{ $suppliers->firstItem() + $key }}</td>
                                    <td>{{ $supplier->name }}</td>
                                    <td>{{ $supplier->address }}</td>
                                    <td>{{ $supplier->contact }}</td>
                                    <td>{{ $supplier->pic_name }}</td>
                                    <td>
                                        @can('update supplier')
                                            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm transparent"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @else
                                            <a href="#" class="btn btn-sm transparent disabled"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @endcan

                                        <!-- Tombol untuk membuka modal -->
                                        @can('delete supplier')
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal-{{ $supplier->id }}">
                                            <i class="fa-solid fa-trash-can fa-lg"></i>
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-danger disabled" >
                                            <i class="fa-solid fa-trash-can fa-lg"></i>
                                        </button>
                                        @endcan

                                        <!-- Modal Konfirmasi Hapus -->
                                        <div class="modal fade" id="deleteModal-{{ $supplier->id }}" tabindex="-1" role="dialog" aria-labelledby="modalTitle"
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
                                                        <p>Apakah Anda yakin ingin menghapus data supplier ini?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST">
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
                    {{ $suppliers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection