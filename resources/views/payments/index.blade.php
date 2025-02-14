@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">

    <!-- Project Card Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Payments List</h5>
        </div>

        {{-- Add New Supplier Button and search Button --}}
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    @can('create payment')
                        <a href="{{ route('payments.add') }}" class="btn btn-primary">+ Create Payments</a>
                    @else
                        <a href="#" class="btn btn-primary disabled">+ Create Payments</a>
                    @endcan
                    @can('list payment')
                        <a href="{{ route('payments.logs') }}" class="btn btn-primary">Log Payments</a>
                    @else
                        <a href="#" class="btn btn-primary disabled">Log Payments</a>
                    @endcan
                </div>
                <div class="col-md-6">
                    <form action="{{ route('payments.index') }}" method="get">
                        @can('list payment')
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" name="search" value="{{ request()->search }}" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" name="search" placeholder="Search for..."
                            aria-label="Search" aria-describedby="basic-addon2" disabled>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" disabled>
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                        @endcan
                    </form>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <form action="{{ route('payments.index') }}" method="get">
                        <label for="per_page">Show</label>
                        <select name="per_page" id="per_page" class="form-control d-inline-block w-auto"
                            onchange="this.form.submit()">
                            <option value="25" {{ request()->per_page == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request()->per_page == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request()->per_page == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span>entries</span>
                    </form>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Supplier Name</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    {{-- Create data dummy --}}
                    <tbody>
                        @if(count($payments) == 0)
                            <tr>
                                <td colspan="5" class="text-center">No data available</td>
                            </tr>
                        @else
                            @foreach($payments as $key => $payment)
                                <tr>
                                    <td>{{ $payments->firstItem() + $key }}</td>
                                    <td>{{ $payment->supplier_name }}</td>
                                    <td>{{ $payment->total_quantity }}</td>
                                    <td class="total-price" data-price="{{ $payment->total_price }}">{{ $payment->total_price }}</td> 
                                    <td>
                                        @if ($payment->status === 'lunas')
                                            <span class="badge badge-success">Lunas</span>
                                        @elseif ($payment->status === 'on progress')
                                            <span class="badge badge-warning text-dark">On Progress</span>
                                        @elseif ($payment->status === 'cancelled')
                                            <span class="badge badge-danger">Cancelled</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($payment->status) }}</span>
                                        @endif</td>                                   
                                    <td>
                                        @can('update payment')
                                            <a href="{{ route('payments.show', $payment->id) }}" class="btn btn-sm transparent"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @else
                                            <a href="#" class="btn btn-sm transparent disabled"><i class="fa-solid fa-eye fa-lg"></i></a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript untuk AJAX Request --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
            // Event Listener untuk status change
            $('.status-select').on('change', function () {
                let paymentId = $(this).data('id');
                let status = $(this).val();

                if (status === 'lunas') {
                    // Barang Masuk checkbox terceklist dan disable
                    $('input[data-id="' + paymentId + '"].product-checkbox').prop('checked', true).prop('disabled', true);
                } else if (status === 'on progress') {
                    // Barang Masuk checkbox terceklist tetapi tidak disable
                    $('input[data-id="' + paymentId + '"].product-checkbox').prop('checked', true).prop('disabled', false);
                } else if (status === 'cancelled') {
                    // Barang Masuk checkbox tidak terceklist dan disable
                    $('input[data-id="' + paymentId + '"].product-checkbox').prop('checked', false).prop('disabled', true);
                }
            });

            // Event Listener untuk checkbox Barang Masuk
            $('.product-checkbox').on('change', function () {
                let paymentId = $(this).data('id');
                let isChecked = $(this).prop('checked'); // Cek apakah dicentang

                // Kirim Data ke /warehouse jika barang masuk dicentang
                if (isChecked) {
                    $.ajax({
                        url: "{{ route('payments.process') }}",
                        method: "POST",
                        data: {
                            payment_id: paymentId,
                            _token: "{{ csrf_token() }}" // Laravel CSRF Token
                        },
                        success: function (response) {
                            // Disable checkbox setelah diklik
                            alert('Barang masuk dan quantity ditambahkan ke warehouse');
                            console.log("Data terkirim:", response);
                        },
                        error: function (xhr, status, error) {
                            alert('Gagal memproses barang masuk');
                            console.log("Gagal:", error);
                        }
                    });
                }
            });

        // Fungsi untuk format rupiah
        function formatRupiah(total) {
            // Fungsi untuk memformat rupiah
            return new Intl.NumberFormat('id-ID', { 
                style: 'currency', 
                currency: 'IDR' 
            }).format(total);
        }

        // Format total_price pada table
        $('.total-price').each(function () {
            let price = $(this).data('price');
            $(this).text(formatRupiah(price));
        });
    });
</script>
@endsection