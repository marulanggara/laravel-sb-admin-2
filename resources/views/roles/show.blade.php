@extends('layouts.admin')

@section('main-content')

<div class="mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Detail Role</h5>
        </div>

        <div class="card-body">
            <div class="form-group">
                <label for="name"><b>Role Name</b></label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $role->name }}" disabled>
            </div>
            <div class="form-group">
                <label for="address"><b>Permission</b></label>
                @if ($permissions->isEmpty())
                    <div class="border p-2 overflow-auto" style="max-height: 150px;">
                        <p class="text-danger">No permissions available</p>
                    </div>                
                @else
                @php
                    // Mengelompokkan berdasarkan nama terakhir
                    $groupedPermissions = $permissions->groupBy(function($permission) {
                        return explode(' ', $permission->name)[count(explode(' ', $permission->name)) - 1];
                    });
                @endphp
                @foreach ($groupedPermissions as $key => $permissions)
                <div class="mt-3">
                    <b>{{ $key }}</b>
                            <div class="border p-2">
                                @foreach ($permissions as $permission)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="permission-{{ $permission->id }}" value="{{ $permission->id }}" checked disabled>
                                        <label class="form-check-label" for="permission-{{ $permission->id }}">
                                            {{ explode(' ', $permission->name)[0] ?? $permission->name }} 
                                            {{-- Hanya ambil bagian pertama (misalnya "create" atau "read") --}}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>

@endsection