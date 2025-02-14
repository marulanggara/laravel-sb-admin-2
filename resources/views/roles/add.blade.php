@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Add New Role</h5>
        </div>

        {{-- Add Form Input Product --}}
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name"><b>Role Name :</b></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter role name" required>
                </div>

                <div class="form-group">
                    <label for="permissions"><b>Permissions :</b></label>
                    @if ($permissions->isEmpty())
                        <p>No permissions available.</p>
                    @else
                        @php
                        // Group permissions by their last word (e.g., 'view', 'create', etc.)
                        $groupedPermissions = $permissions->groupBy(function ($permission) {
                            return explode(' ', $permission->name)[count(explode(' ', $permission->name)) - 1];
                        });
                        @endphp

                        @foreach ($groupedPermissions as $key => $group)
                            <div class="mt-3">
                                <h7><b>{{ ucfirst($key) }}</b></h7> <!-- Capitalize first letter of the group name -->
                                <div class="p-2">
                                    {{-- Cekbox untuk checkAll --}}
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input check-all" type="checkbox" data-group="{{ $key }}">
                                        <label class="form-check-label">Select All</label>
                                    </div>
                                    <hr>
                                    @foreach ($group as $permission)  <!-- Loop over the grouped permissions -->
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->id }}" data-group="{{ $key }}">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                {{ explode(' ', $permission->name)[0] ?? $permission->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Create Role</button>
            </form>
        </div>
    </div>
</div>

{{-- Script untuk check/uncheck semua permission dalam kategori --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Ketika checkbox "Check All" diklik
        $('.check-all').on('change', function () {
            let group = $(this).data('group');
            let isChecked = $(this).is(':checked');

            // Centang semua checkbox dalam kategori yang sama
            $('input.permission-checkbox[data-group="' + group + '"]').prop('checked', isChecked);
        });

        // Ketika salah satu checkbox di kategori diklik
        $('.permission-checkbox').on('change', function () {
            let group = $(this).data('group');
            let allChecked = $('input.permission-checkbox[data-group="' + group + '"]').length === $('input.permission-checkbox[data-group="' + group + '"]:checked').length;

            // Jika semua checkbox dalam kategori tercentang, "Check All" juga ikut tercentang
            $('input.check-all[data-group="' + group + '"]').prop('checked', allChecked);
        });
    });
</script>
@endsection
