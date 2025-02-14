@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Edit Supplier</h5>
        </div>

        {{-- Form Input Edit Supplier --}}
        <div class="card-body">
            <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" enctype="multipart/form-data" id="editSupplierForm">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name"><b>Supplier Name</b></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $supplier->name) }}" placeholder="Enter Supplier Name" required>
                </div>
                <div class="form-group">
                    <label for="address"><b>Address</b></label>
                    <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $supplier->address) }}" placeholder="Enter Address" required>
                </div>
                <div class="form-group">
                    <label for="contact"><b>Contact</b></label>
                    <input type="number" class="form-control" id="contact" name="contact" value="{{ old('contact', $supplier->contact) }}" placeholder="Enter Contact" required>
                </div>
                <div class="form-group">
                    <label for="pic_name"><b>PIC Name</b></label>
                    <input type="text" class="form-control" id="pic_name" name="pic_name" value="{{ old('pic_name', $supplier->pic_name) }}" placeholder="Enter PIC Name" required>
                </div>

                {{-- Product List --}}
                <div class="form-group">
                    <label for="product"><b>Item Name</b></label>
                    <div class="overflow-auto" style="max-height: 400px; border: 1px solid #ddd; padding: 10px;">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
                            @if ($products->isEmpty())
                                <p>No item available</p>                    
                            @else
                                @php
                                $selectedProducts = is_array($selectedProducts) ? $selectedProducts : [];
                                @endphp
                                @foreach ($products as $product)
                                <div class="col mb-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input product-checkbox" type="checkbox"
                                                id="product_{{ $product->id }}" name="products[{{ $product->id }}][id]" 
                                                value="{{ $product->id }}"
                                                @if (in_array($product->id, $selectedProducts)) checked @endif>
                                        <label class="form-check-label" for="product_{{ $product->id }}">
                                            {{ $product->name }} - {{ $product->unit->name }}
                                        </label>
                                    </div>
                                    <input type="text" class="form-control price-input" 
                                        id="price_{{ $product->id }}" 
                                        name="products[{{ $product->id }}][price]" 
                                        placeholder="Enter Price"
                                        value="{{ 'Rp '.number_format((float)old('products.' . $product->id . '.price',  $productPrices[$product->id] ?? 0), 0, ',', '.') }}"
                                        oninput="formatRupiah(this)"
                                        @if (!in_array($product->id, $selectedProducts)) disabled @endif>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                    
                {{-- Submit with modal confirm and Back Button --}}
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#submitModal">
                    Submit
                </button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="submitModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin ingin mengubah data supplier?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirmSubmit">Save changes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi untuk mengambil data form sebagai objek
    function getFormData(form) {
        let data = {};
        Array.from(form.elements).forEach(function(element) {
            if (element.name) {
                if(element.type === "checkbox"){
                    data[element.name] = element.checked;
                } else {
                    data[element.name] = element.value;
                }
            }
        });
        return data;
    }
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("editSupplierForm");
        const initialData = getFormData(form);

        document.querySelectorAll(".product-checkbox").forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                let priceInput = this.closest(".col").querySelector(".price-input");
                priceInput.disabled = !this.checked;
            });
        });

        document.getElementById("confirmSubmit").addEventListener("click", function () {
            const currenData = getFormData(form);

            if (JSON.stringify(currenData) === JSON.stringify(initialData)) {
                alert("Tidak ada perubahan yang dilakukan.");
            } else {
                document.getElementById("editSupplierForm").submit();
            }
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
