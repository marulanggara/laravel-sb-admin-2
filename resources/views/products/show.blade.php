@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Detail Product</h5>
        </div>

        {{-- Form Input Edit Supplier --}}
        <div class="card-body">
            <div class="form-group">
                <label for="name"><b>Item Name</b></label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $product->name }}" disabled>
            </div>
            <div class="form-group">
                <label for="address"><b>Item Code</b></label>
                <input type="text" class="form-control" id="address" name="address" value="{{ $product->code }}" disabled>
            </div>
            <div class="form-group">
                <label for="unit_id"><b>Satuan</b></label>
                <input type="text" class="form-control" id="unit_id" name="unit_id" value="{{ $product->unit_name }}" disabled>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>

@endsection