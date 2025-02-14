@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h1>Edit Role: {{ $permission->name }}</h1>

    <form action="{{ route('permissions.update', $permission->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Permission Name</label>
            <input type="text" class="form-control" name="name" value="{{ $permission->name }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Role</button>
    </form>
</div>
@endsection