@extends('layouts.admin')

@section('main-content')

<div class="mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Detail Supplier</h5>
        </div>

        <div class="card-body">
            <div class="form-group">
                <label for="name"><b>Supplier Name</b></label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $supplier->name }}" disabled>
            </div>
            <div class="form-group">
                <label for="address"><b>Address</b></label>
                <input type="text" class="form-control" id="address" name="address" value="{{ $supplier->address }}"
                    disabled>
            </div>
            <div class="form-group">
                <label for="contact"><b>Contact</b></label>
                <input type="number" class="form-control" id="contact" name="contact" value="{{ $supplier->contact }}"
                    disabled>
            </div>
            <div class="form-group">
                <label for="pic_name"><b>PIC Name</b></label>
                <input type="text" class="form-control" id="pic_name" name="pic_name" value="{{ $supplier->pic_name }}"
                    disabled>
            </div>
            <div class="form-group">
                <label for="product_name"><b>Item Name</b></label>
                <div class="border p-2 overflow-auto" style="max-height: 150px;">
                    @if ($supplier->products->isNotEmpty())
                        <ul class="list-group">
                            @foreach ($supplier->products as $product)
                                <li class="list-group-item">
                                    <input type="checkbox" checked disabled>
                                    {{ $product->name }} - {{ $product->unit->name ?? "No unit" }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-danger">No products available</p>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label for="price"><b>Item Price</b></label>
                <div class="border p-2 overflow-auto" style="max-height: 150px;">
                    @if ($supplier->products->isNotEmpty())
                        <ul class="list-group">
                            @foreach ($supplier->products as $product)
                                <li class="list-group-item">
                                    {{ $product->name }} - Rp {{ number_format($product->pivot->price, 0, ',', '.') }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-danger">No products available</p>
                    @endif
                </div>
            </div>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>

@endsection