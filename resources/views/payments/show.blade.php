@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Payment Details</h5>
        </div>

        {{-- Payment Details --}}
        <div class="card-body">
            <h4 class="mb-4">Payment Information</h4>
            @if($payment)
                    <form action="{{ route('payments.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="payment_id" value="{{ $payment->payment_id }}">

                    <!-- Supplier & Payment Details Table -->
                    <table class="table table-bordered">
                        <tr>
                            <th>Supplier Name</th>
                            <td>{{ $payment->supplier_name }}</td>
                        </tr>
                        <tr>
                            <th>Total Quantity</th>
                            <td>{{ $payment->total_quantity }}</td>
                        </tr>
                        <tr>
                            <th>Total Price</th>
                            <td class="total-price" data-price="{{ $payment->total_price }}">{{ $payment->total_price }}</td>
                        </tr>
                    </table>

                    <!-- Payment Items Table -->
                    <h4 class="mt-4">Payment Items</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Unit</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->unit_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="price" data-price="{{ $item->price }}">{{ $item->price }}</td>
                                    <td class="total-price" data-price="{{ $item->quantity * $item->price }}">{{ $item->quantity * $item->price }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Dropdown for Payment Status -->
                    <h4 class="mt-4">Payment Status</h4>
                    <div class="form-group">
                        <label for="payment-status">Payment Status</label>
                        <select name="payment_status" id="payment-status" class="form-control" @if($payment->is_received) disabled @endif>
                            <option value="lunas" {{ $payment->status == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="on progress" {{ $payment->status == 'on progress' ? 'selected' : '' }}>On Progress</option>
                            <option value="cancelled" {{ $payment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Checkbox for Barang Masuk (Received) -->
                    <div class="form-group">
                        <label for="is_received">Barang Masuk</label>
                        <input type="checkbox" name="is_received" id="is_received" 
                               {{ $payment->is_received ? 'checked' : '' }} 
                               @if($payment->status == 'lunas' || $payment->status == 'cancelled') disabled @endif
                               onchange="toggleFields(this)">
                    </div>

                    <!-- Save Button -->
                    <button type="submit" class="btn btn-primary" id="save-btn">Save Changes</button>

                    @if(!$payment)
                        <div class="alert alert-danger" role="alert">
                            No payment found.
                        </div>
                    @endif
                    <a href="{{ route('payments.index') }}" class="btn btn-secondary">Back</a>
                </form>
            @endif
        </div>
    </div>
</div>

{{-- JavaScript untuk format Rupiah --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        const paymentStatus = "{{ $payment->status }}";
        const isReceived = {{ $payment->is_received ? 'true' : 'false' }};
        const saveButton = $('#save-btn');
        const isReceivedCheckbox = $('#is_received');
        const paymentStatusDropdown = $('#payment-status');

        // Menonaktifkan elemen berdasarkan status
        if (paymentStatus == 'lunas' || paymentStatus == 'cancelled') {
            // Untuk status lunas atau cancelled, disable semua elemen
            isReceivedCheckbox.prop('disabled', true);
            saveButton.prop('disabled', true);
            paymentStatusDropdown.prop('disabled', true);
        } else if (paymentStatus == 'on progress' && isReceived) {
            // Untuk status On Progress dan is_received true, enable dropdown, disable checkbox, enable tombol save
            paymentStatusDropdown.prop('disabled', false);
            isReceivedCheckbox.prop('disabled', true);
            saveButton.prop('disabled', false);
        } else {
            // Untuk status On Progress dan is_received false, enable semua elemen
            paymentStatusDropdown.prop('disabled', false);
            isReceivedCheckbox.prop('disabled', false);
            saveButton.prop('disabled', false);
        }
        // Fungsi untuk memformat angka ke format Rupiah
        function formatRupiah(total) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(total);
        }

        // Format total_price pada table
        $('.total-price, .price').each(function () {
            let price = $(this).data('price');
            $(this).text(formatRupiah(price));
        });
    });
</script>
@endsection
