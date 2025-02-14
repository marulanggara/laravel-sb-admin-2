@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Add New Supplier</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('suppliers.store') }}" method="POST" enctype="multipart/form-data" id="addSupplierForm">
                @csrf
                <div class="form-group">
                    <label for="name"><b>Supplier Name</b></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter Supplier Name" required>
                </div>
                <div class="form-group">
                    <label for="address"><b>Address</b></label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Enter Address" required>
                </div>
                <div class="form-group">
                    <label for="contact"><b>Contact</b></label>
                    <input type="number" class="form-control" id="contact" name="contact" placeholder="Enter Contact" required>
                </div>
                <div class="form-group">
                    <label for="pic_name"><b>PIC Name</b></label>
                    <input type="text" class="form-control" id="pic_name" name="pic_name" placeholder="Enter PIC Name" required>
                </div>

                {{-- Pilihan Produk + Harga --}}
                <div class="form-group">
                    <label for="product"><b>Item Name</b></label>
                    <div class="overflow-auto" style="max-height: 400px; border: 1px solid #ddd; padding: 10px;">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
                            @if ($products->isEmpty())
                                <p>No item available</p>                    
                            @else
                                @foreach ($products as $product)
                                <div class="col mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input product-checkbox" type="checkbox"
                                                id="product_{{ $product->id }}" name="products[{{ $product->id }}][id]" 
                                                value="{{ $product->id }}">
                                        <label class="form-check-label" for="product_{{ $product->id }}">
                                            {{ $product->name }} - {{ $product->unit->name }}
                                        </label>
                                    </div>
                                    <input type="text" class="form-control price-input d-none" 
                                        id="price_{{ $product->id }}" 
                                        name="products[{{ $product->id }}][price]"
                                        value="{{ $product->price }}"
                                        placeholder="Enter Price" disabled
                                        oninput="formatRupiah(this)">
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                {{-- Submit Button dengan Modal Konfirmasi --}}
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#submitModal">
                    Submit
                </button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="submitModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Add Supplier</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin ingin menambahkan supplier?</p>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" form="addSupplierForm">Save changes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript untuk Menampilkan Dropdown Satuan saat Checkbox Dicentang --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".product-checkbox").forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                let priceInput = document.getElementById("price_" + this.value);
                if (this.checked) {
                    priceInput.classList.remove("d-none"); // Tampilkan input harga
                    priceInput.removeAttribute("disabled"); // Aktifkan input
                } else {
                    priceInput.classList.add("d-none"); // Sembunyikan input harga
                    priceInput.setAttribute("disabled", "true"); // Nonaktifkan input
                    priceInput.value = "";
                }
            });
        });
    });
    function formatRupiah(input) {
            let value = input.value.replace(/[^,\d]/g, '');
            let formatted = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(value);

        input.value = formatted; // Tampilkan hasil format Rupiah
        // Simpan angka asli tanpa format di input hidden
        let hiddenInputId = "hidden_" + input.id;
        let hiddenInput = document.getElementById(hiddenInputId);
        if (!hiddenInput) {
            hiddenInput = document.createElement("input");
            hiddenInput.type = "hidden";
            hiddenInput.id = hiddenInputId;
            hiddenInput.name = input.name; // Gunakan nama yang sama agar dikirim ke backend
            input.parentNode.appendChild(hiddenInput);
        }
        hiddenInput.value = value; // Simpan angka asli tanpa format
    }
</script>
@endsection