@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Payment Form Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Add New Payment</h6>
        </div>

        {{-- Add Payment Form --}}
        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('payments.store') }}" method="POST" id="addPaymentForm">
                @csrf
                <div class="form-group">
                    <label for="supplier_id"><b>Supplier Name</b></label>
                    <select class="form-control" id="supplier_id" name="supplier_id">
                        <option value="" disabled selected>-- Select Supplier --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Item List --}}
                <div class="form-group" id="item-list">
                    <label class="form-label" for="item"><b>Item List</b></label>
                    <div id="products"></div>
                </div>

                {{-- Total Price --}}
                <div class="form-group">
                    <label class="form-label" for="total_price"><b>Total Price</b></label>
                    <input type="text" class="form-control" id="total_price" name="total_price" placeholder="Rp 0,00" readonly>
                </div>

                {{-- Submit with modal confirm and Back Button --}}
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#submitModal">
                    Submit
                </button>
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmation -->
<div class="modal fade" id="submitModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menambahkan pembayaran?</p>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" form="addPaymentForm">Save changes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#supplier_id').change(function () {
            var supplierId = $(this).val();
            if (supplierId) {
                // Ajax Request
                $.ajax({
                    url: "{{ route('payments.getProductsBySupplier') }}",
                    type: "POST",
                    data: {
                        supplier_id: supplierId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        $('#products').empty();
                        if (Array.isArray(response) && response.length > 0) {
                            $('#item-list').show();
                            response.forEach(function (product) {
                                $('#products').append(`
                                    <div class="form-check">
                                        <input class="form-check-input product-checkbox" type="checkbox" 
                                               name="items[${product.product_id}][product_id]" 
                                               value="${product.product_id}" 
                                               data-price="${product.price || 0}"
                                               data-unit="${product.unit_id ?? ''}">
                                        <label class="form-check-label">
                                            ${product.product_name} - ${formatRupiah(product.price)} - ${product.product_code}
                                        </label>
                                        <input type="hidden" name="items[${product.product_id}][product_name]" value="${product.product_name}">
                                        <input type="hidden" name="items[${product.product_id}][product_code]" value="${product.product_code}">
                                        <input type="hidden" name="items[${product.product_id}][unit_id]" value="${product.unit_id}">
                                        <input type="hidden" name="items[${product.product_id}][unit_name]" value="${product.unit_name}">
                                        <input type="number" name="items[${product.product_id}][quantity]" 
                                               class="form-control quantity-input mt-1 mb-2" 
                                               placeholder="Quantity" min="1" step="1"  oninput="this.value = this.value.replace(/[^0-9]/g, '')" disabled>
                                        <input type="hidden" name="items[${product.product_id}][price]" value="${product.price || 0}">
                                    </div>
                                `);
                            });

                            // Toggle quantity field
                            $('.product-checkbox').change(function () {
                                var quantityInput = $(this).closest('.form-check').find('.quantity-input');
                                if ($(this).is(':checked')) {
                                    quantityInput.prop('disabled', false);
                                    quantityInput.val(1);
                                } else {
                                    quantityInput.prop('disabled', true);
                                    quantityInput.val('');
                                }
                                calculateTotal();
                            });

                            $('.quantity-input').on('input', function () {
                                if ($(this).val() < 1) {
                                    $(this).val(1);
                                }
                                calculateTotal();
                            });
                        } else {
                            $('#item-list').hide();
                        }
                    }
                });
            } else {
                $('#item-list').hide();
            }
        });

        $('#addPaymentForm').submit(function (e) {
            // Hapus input yang tidak memiliki product_id atau quantity
            $('.product-checkbox').each(function () {
                var isChecked = $(this).is(':checked');
                var parentDiv = $(this).closest('.form-check');

                if (!isChecked) {
                    parentDiv.find('input').each(function () {
                        $(this).prop('disabled', true); // Matikan semua input terkait produk yang tidak dipilih
                    });
                }
            });
        });

        // Fungsi untuk format angka menjadi rupiah
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', { 
                style: 'currency', 
                currency: 'IDR' 
            }).format(angka);
        }

        // Hitung Total Price
        function calculateTotal() {
            var total = 0;
            $('.product-checkbox:checked').each(function () {
                var price = $(this).data('price');
                var quantity = $(this).closest('.form-check').find('.quantity-input').val() || 0;
                total += price * quantity;
            });
            $('#total_price').val(formatRupiah(total));
        }
    });
</script>
@endsection