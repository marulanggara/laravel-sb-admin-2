@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Units List</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    @can('create product')
                        <button class="btn btn-primary" data-toggle="modal" data-target="#createUnitModal">+ Create Unit</button>
                    @else
                        <button class="btn btn-primary disabled" >+ Create Unit</button>
                    @endcan
                    @can('list product')
                        <a href="{{ route('units.logs') }}" class="btn btn-primary">Log History</a>
                    @else
                        <a href="#" class="btn btn-primary disabled">Log History</a>
                    @endcan
                </div>
                <div class="col-md-6">
                    <form action="{{ route('units.index') }}" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" name="search" value="{{ request()->search }}" placeholder="Search for Unit Name"
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <form action="{{ route('units.index') }}" method="get">
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
            <div class="table-responsive mt-4">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Unit Name</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    {{-- Integrate product data --}}
                    <tbody>
                        @if ($units->count() === 0)
                            <tr>
                                <td colspan="3" class="text-center">No Data Available</td>
                            </tr>
                        @else
                            @foreach ($units as $unit)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $unit->name }}</td>
                                    <td>
                                        @can('update product')
                                        <button class="btn btn-sm transparent btn-edit"
                                            data-id="{{ $unit->id }}"
                                            data-name="{{ $unit->name }}"
                                            data-toggle="modal"
                                            data-target="#updateUnitModal">
                                            <i class="fa-solid fa-eye fa-lg"></i>
                                        </button>
                                        @else
                                            <a href="#" class="btn btn-sm transparent disabled"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @endcan
                                        @can('delete product')
                                        <button class="btn btn-sm btn-danger btn-delete" 
                                                data-id="{{ $unit->id }}"
                                                data-toggle="modal"
                                                data-target="#deleteUnitModal">
                                            <i class="fa-solid fa-trash-can fa-lg"></i>
                                        </button>
                                        @else
                                            <a href="#" class="btn btn-sm transparent disabled"><i class="fa-solid fa-trash-can fa-lg"></i></a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                <div class="d-flex justify-content-center">
                    {{ $units->appends(['per_page' => request()->per_page])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add -->
<div class="modal fade" id="createUnitModal" tabindex="-1" role="dialog" aria-labelledby="createUnitModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUnitModalLabel">Add New Unit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="unit_name">Unit Name</label>
                        <input type="text" class="form-control" id="unit_name" name="unit_name" placeholder="Enter unit name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Update -->
<div class="modal fade" id="updateUnitModal" tabindex="-1" role="dialog" aria-labelledby="updateUnitModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateUnitModalLabel">Edit Unit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('units.update', $unit->id) }}" method="POST" id="updateUnitForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="unit_name">Unit Name</label>
                        <input type="text" class="form-control" id="unit_name" name="unit_name" placeholder="Enter unit name" value="{{ $unit->name }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteUnitModal" tabindex="-1" role="dialog" aria-labelledby="deleteUnitModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUnitModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus unit ini?
            </div>
            <div class="modal-footer">
                <form id="deleteUnitForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('.btn-edit').on('click', function () {
            let unitId = $(this).data('id');
            let unitName = $(this).data('name');

            // Isi nilai input dalam modal update
            $('#updateUnitModal input[name="unit_name"]').val(unitName);
            $('#updateUnitForm').attr('action', '/units/' + unitId);
        });

        $('.btn-delete').on('click', function () {
            let unitId = $(this).data('id');
            let formAction = "/units/" + unitId;
            $('#deleteUnitForm').attr('action', formAction);
        });
    });
</script>

@endsection