@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Users List</h6>
        </div>


        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="col-md-6">
                        @can('create user')
                            <a href="{{ route('users.create') }}" class="btn btn-primary">+ Create User</a>
                        @else
                            <a href="#" class="btn btn-primary disabled">+ Create User</a>
                        @endcan
                    </div>
                </div>
                <div class="col-md-6">
                    <form action="{{ route('users.index') }}" method="GET">
                        @can('list user')
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search..." name="search">
                            <div class="input-group-append">
                                <button class="btn btn-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search..." name="search" disabled>
                            <div class="input-group-append">
                                <button class="btn btn-secondary" type="submit" disabled>
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        @endcan
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
                            <th>Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    {{-- Tabel Users List --}}
                    <tbody>
                        @if($users->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center">No Data</td>
                        </tr>
                        @else
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->last_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            {{ $role->name }}
                                        @endforeach
                                    </td>
                                    <td>
                                        @can('update user')
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm bg-transparent transparent"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @else
                                            <a href="#" class="btn btn-sm bg-transparent transparent" disabled><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @endcan

                                        <!-- Tombol untuk membuka modal -->
                                        @can('delete user')
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal-{{ $user->id }}">
                                            <i class="fa-solid fa-trash-can fa-lg"></i>
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-danger" disabled>
                                            <i class="fa-solid fa-trash-can fa-lg"></i>
                                        </button>
                                        @endcan

                                        <!-- Modal Konfirmasi Hapus -->
                                        <div class="modal fade" id="deleteModal-{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="modalTitle"
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
                                                        <p>Apakah Anda yakin ingin menghapus data user ini?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST">
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
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection