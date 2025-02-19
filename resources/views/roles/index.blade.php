@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h4 class="m-0 font-weight-bold text-primary">Roles List</h4>
        </div>
        {{-- Show data/pages button with dropdown in left side, Add New Role Button in right side and search button in right side --}}
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="col-md-6">
                        @can('create role')
                            <a href="{{ route('roles.add') }}" class="btn btn-primary">+ Create Role</a>
                        @else
                            <a href="#" class="btn btn-primary disabled">+ Create Role</a>
                        @endcan
                    </div>
                </div>
                <div class="col-md-6">
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Role Name</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    {{-- Create data dummy --}}
                    <tbody>
                        @if ($roles->count() == 0)
                        <tr>
                            <td colspan="4" class="text-center">No data available</td>
                        </tr>
                        @else
                            @foreach ($roles as $role)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        @can('update role')
                                            <a href="{{ route('roles.edit', $role->id) }}" class="btn transparent"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @else
                                            <a href="#" class="btn transparent disabled"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @endcan
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            @can('delete role')
                                                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash-can fa-lg"></i></button>
                                            @else
                                                <button type="button" class="btn btn-danger disabled"><i class="fa-solid fa-trash-can fa-lg"></i></button>
                                            @endcan
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection