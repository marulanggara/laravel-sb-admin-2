@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h1>Create New Permission</h1>

    <form action="{{ route('permissions.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Permission Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter permission name" required>
        </div>

        <button type="submit" class="btn btn-primary">Create Permission</button>
    </form>
</div>
@endsection