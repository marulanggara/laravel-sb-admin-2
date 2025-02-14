@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Create Invoice</h6>
        </div>

        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('invoice.store') }}" method="POST" id="addInvoiceForm">
                @csrf
                <h4 class="mb-4">Invoice Information</h4>
                <table class="table table-bordered">
                    <tr>
                        <th>Invoice No.</th>
                        <td><input type="text" class="form-control" name="invoice_no" id="invoice_no" placeholder="Invoice No" readonly></td>
                    </tr>
                    <tr>
                        <th>Created By</th>
                        <td><input type="text" class="form-control" name="created_by" value="{{ Auth::user()->name }}"
                                readonly></td>
                    </tr>
                    <tr>
                        <th>Tanggal Invoice</th>
                        <td><input type="date" class="form-control" name="invoice_date" value="{{ date('Y-m-d') }}"
                                readonly></td>
                    </tr>
                    <tr>
                        <th>Tanggal Lunas</th>
                        <td><input type="date" class="form-control" id="due_date" name="due_date" value="{{ date('Y-m-d') }}"></td>
                    </tr>
                </table>

                <h4 class="mb-4">Invoice Items</h4>
                <div class="form-group d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" id="addItemButton"> + </button>
                </div>
                <table class="table table-bordered" id="invoiceItemsTable">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select class="form-control product-select" name="items[0][product_id]">
                                    <option value="" disabled selected>Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="warehouse-id" name="items[0][warehouse_id]">
                            </td>
                            <td><input type="text" class="form-control code" name="items[0][product_code]" placeholder="Code" readonly></td>
                            <input type="hidden" class="form-control unit" name="items[0][unit_name]" readonly>
                            <td><input type="number" class="form-control qty" name="items[0][quantity]" placeholder="Quantity" min="1"></td>
                            <td><input type="text" class="form-control price" name="items[0][unit_price]" placeholder="Rp 0,00" readonly></td>
                            <td>
                                <input type="text" class="form-control total_display" placeholder="Rp 0,00" readonly>
                                <input type="hidden" class="form-control total_price_hidden" name="items[0][total_price]" placeholder="Rp 0,00" value="0" readonly>
                            </td>
                            <td><button type="button" class="btn btn-danger remove-row">-</button></td>
                        </tr>
                    </tbody>
                </table>
                <div class="form-group">
                <h4 class="mb-4">Total Price: <span id="grandTotal">0</span></h4>
                <input type="text" class="form-control" id="total_price_display" name="total_price_display" placeholder="Rp 0,00" readonly>
                <input type="hidden" class="form-control" id="total_price" name="total_price" value="0">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('invoice.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Fungsi untuk format angka menjadi rupiah
        function formatRupiah(angka) {
            let rupiah = '';
            let angkarev = angka.toString().split('').reverse().join('');
            for (let i = 0; i < angkarev.length; i++) {
                if (i % 3 === 0 && i !== 0) {
                    rupiah += '.';
                }
                rupiah += angkarev[i];
            }
            return 'Rp ' + rupiah.split('').reverse().join('');
        }

        // Fungsi untuk menghapus format rupiah dan mengembalikan ke angka
        function removeRupiah(rupiah) {
            return rupiah.replace(/[^0-9]/g, '');
        } 

        // Ambil invoice_no dari api saat halaman dibuat
        $.get("{{ url('/generate-invoice-code')}}", function (data) {
            $('#invoice_no').val(data.invoice_no);
        });

        // Datepicker
        $("#due_date").on("focus", function (){
            this.showPicker();
        });
        $('.product-select').select2();

        $(document).on('change', '.product-select', function () {
            
            let productId = $(this).val();
            let row = $(this).closest('tr');

            if (productId) {
                $.ajax({
                    url: `/get-product-stock/${productId}`,
                    type: 'GET',
                    success: function (data) {
                        row.find('.warehouse-id').val(data.warehouse_id);
                        row.find('.code').val(data.product_code);
                        row.find('.unit').val(data.unit_name);
                        row.find('.price').val(formatRupiah(data.selling_price));
                        row.find('.qty').val(1);
                        row.find('.qty').attr('max', data.available_stock);

                        let total = 1 * data.selling_price;
                        row.find('.total_display').val(formatRupiah(total));
                        row.find('.total_price_hidden').val(total);

                        calculateGrandTotal();
                    },
                    error: function () {
                        alert('Stock not available!');
                        row.find('.warehouse-id, .code, .unit, .price, .total_display, .total_price_hidden').val('');
                        row.find('.qty').attr('max', 0);
                        calculateGrandTotal();
                    }
                });
            }
        });

        $(document).on('input', '.qty', function () {
            let row = $(this).closest('tr');
            let qty = $(this).val();
            let price = removeRupiah(row.find('.price').val());
            let total = qty * price;
            row.find('.total_display').val(formatRupiah(total));
            row.find('.total_price_hidden').val(total);
            calculateGrandTotal();
        });

        $(document).on('click', '#addItemButton', function () {
            let newRow = $('#invoiceItemsTable tbody tr:first').clone();
            let index = $('#invoiceItemsTable tbody tr').length;
            
            newRow.find('input, select').each(function () {
                let name = $(this).attr('name');
                if (name) {
                    name = name.replace(/items\[\d+\]/, 'items[' + index + ']');
                    $(this).attr('name', name).val('');
                }
            });
            // Hapus atribut select2 yang sudah ada agar tidak bermasalah
            newRow.find('.product-select').removeClass('select2-hidden-accessible').removeAttr('data-select2-id').next('.select2-container').remove();
            
            newRow.find('.warehouse-id, .code, .unit, .price, .total_display, .total_price_hidden').val('');
            newRow.find('.qty').attr('max', 0);
            newRow.find('.qty').val(1);
            $('#invoiceItemsTable tbody').append(newRow);
            $('.product-select').select2();
            
        });

        $(document).on('click', '.remove-row', function () {
            if ($('#invoiceItemsTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
                calculateGrandTotal();
            }
        });

        $(document).on('focus', '.price', function () {
            $(this).val(removeRupiah($(this).val())); // Hapus format saat fokus
        });

        $(document).on('blur', '.price', function () {
            let value = $(this).val();
            if (value) {
                $(this).val(formatRupiah(value)); // Format kembali saat blur
            }
        });

        function calculateGrandTotal() {
            let total = 0;
            $('.total_price_hidden').each(function () {
                total += parseFloat($(this).val()) || 0;
            });
            $('#grandTotal').text(formatRupiah(total));
            $('#total_price_display').val(formatRupiah(total));
            $('#total_price').val(total);
        }
    });
</script>
@endsection