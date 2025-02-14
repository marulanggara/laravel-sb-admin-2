@extends('layouts.admin')

@section('main-content')

<div class="mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Detail User</h5>
        </div>

        <div class="card-body">
            <div class="form-group">
                <label for="name"><b>Name</b></label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" disabled>
            </div>
            <div class="form-group">
                <label for="email"><b>Last Name</b></label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ $user->last_name }}" disabled>
            </div>
            <div class="form-group">
                <label for="email"><b>Email</b></label>
                <input type="text" class="form-control" id="email" name="email" value="{{ $user->email }}" disabled>
            </div>
            <div class="form-group">
                <label for="role"><b>Role</b></label>
                @if ($user->roles->isEmpty())
                <input type="text" class="form-control" id="role" name="role" value="No roles available" disabled>
                @else
                <select class="form-control" id="role" name="role" disabled>
                    @foreach ($user->roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                @endif
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

@endsection