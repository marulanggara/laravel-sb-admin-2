@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Edit Product</h5>
        </div>

        {{-- Form Input Edit Supplier --}}
        <div class="card-body">
            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="editProductForm">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name"><b>Item Name</b></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $product->name }}" placeholder="Enter Item Name">
                </div>
                <div class="form-group">
                    <label for="code"><b>Item Code</b></label>
                    <input type="text" class="form-control" id="code" name="code" value="{{ $product->code }}" disabled>
                </div>
                {{-- Dropdown Satuan kg, gram, pcs --}}
                <div class="form-group">
                    <label for="unit_id"><b>Satuan</b></label>
                    <select class="form-control" id="unit_id" name="unit_id">
                        @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" {{ $product->unit_id == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#submitModal">
                    Submit
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="submitModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Edit Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin ingin mengubah data product ini?</p>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="confirmEdit">Save changes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('confirmEdit').addEventListener('click', function () {
            document.getElementById('editProductForm').submit();
        });
    });
</script>
@endsection