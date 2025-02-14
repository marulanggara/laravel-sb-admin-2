@extends('layouts.admin')

@section('main-content')

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h3 class="m-0 font-weight-bold text-primary">Warehouse Details</h3>
    </div>
    <div class="card-body">
        <h4><b>Product: {{ $warehouses->first()->product_name }}</b></h4>
        <hr>

        {{-- Form save harga jual --}}
        <form action="{{ route('warehouses.update', $warehouses->first()->product_id) }}" method="POST">
            @csrf
            @method('PUT')

            <h6 for="quantity"><b>Total Quantity</b></h6>
            <input type="number" class="form-control mb-4" name="quantity" value="{{ $warehouses->sum('quantity') }}" disabled>

            <h6 for="selling_price"><b>Harga Jual</b></h6>
            <input type="text" 
                    class="form-control" 
                    placeholder="Enter Price" 
                    oninput="formatRupiah(this)"
                    value="{{'Rp ' . number_format(old('selling_price', $warehouses->first()->selling_price ?? 0), 0, ',', '.') }}" required>
            <input type="hidden" name="selling_price" id="selling_price" value="{{ old('selling_price', $warehouses->first()->selling_price ?? 0) }}">
            <input type="hidden" name="product_id" value="{{ $warehouses->first()->product_id }}">

            <button type="submit" class="btn btn-primary mt-3">Save</button>
            <hr>
        </form>

        <h5>Details</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Item Name</th>
                    <th>Supplier Name</th>
                    <th>Unit</th>
                    <th>Quantity</th>
                    <th>Price/Unit</th>
                    <th>Selling Price</th>
                </tr>
            </thead>
            <tbody>
                @if (count($warehouses) == 0)
                    <tr>
                        <td colspan="5" class="text-center">No Data Available</td>
                    </tr>                
                @else
                    @foreach ($warehouses as $key => $warehouse)
                        <tr>
                            <td>{{ $warehouses->firstItem() + $key }}</td>
                            <td>{{ $warehouse->product_name }}</td>
                            <td>{{ $warehouse->supplier_name }}</td>
                            <td>{{ $warehouse->unit_name }}</td>
                            <td>{{ $warehouse->quantity }}</td>
                            <td class="prices" data-price="{{ $warehouse->price }}">{{ $warehouse->price }}</td>
                            <td class="prices" data-price="{{ $warehouse->selling_price }}">{{ $warehouse->selling_price }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
            {{ $warehouses->links() }}
        </div>

        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary mt-3">Back to Warehouse</a>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function formatRupiah(input) {
            let value = input.value.replace(/[^,\d]/g, '');
            let formatted = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);

            input.value = formatted; // Tampilkan hasil format Rupiah
            // Simpan angka asli tanpa format di input hidden
        let hiddenInput = document.getElementById("selling_price");
        hiddenInput.value = value; // Menyimpan angka asli
        }
</script>
@endsection