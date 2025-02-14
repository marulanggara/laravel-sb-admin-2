@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Add New Product</h5>
        </div>

        {{-- Add Form Input Product --}}
        <div class="card-body">
            @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
            @endif

            <form action="{{ route('products.store') }}" method="POST" id="addProductForm">
                @csrf
                <div class="form-group">
                    <label for="name"><b>Item Name</b></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter Item Name" required>
                </div>
                <div class="form-group">
                    <label for="code"><b>Item Code</b></label>
                    <input type="text" class="form-control" id="code" name="code" placeholder="Item Code" readonly>
                </div>
                {{-- Dropdown Satuan kg, gram, pcs --}}
                <div class="form-group">
                    <label for="unit_id"><b>Satuan</b></label>
                    <select class="form-control" id="unit_id" name="unit_id">
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Submit with modal confirm and Back Button --}}
                <!-- Button trigger modal -->
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
                <h5 class="modal-title" id="exampleModalLongTitle">Add Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin ingin menambahkan product?</p>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="confirmSubmit">Save changes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Add Form Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () 
    {
        // fetch kode unik
        fetch('/generate-product-code')
        .then(response => response.json())
        .then(data => {
            document.getElementById('code').value = data.code;
        })
        .catch(error => console.error('Error:', error));

        // submit form
        document.getElementById('confirmSubmit').addEventListener('click', function() {
            document.getElementById('addProductForm').submit();
        });
    });
    
</script>

@endsection