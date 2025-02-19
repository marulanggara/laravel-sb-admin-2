@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">
    <div class="card">
        <div class="card-header">
                        
            <div class="container text-center mt-5">
                <h1 class="text-danger">Product Tidak Tersedia</h1>
                <p>Anda akan diarahkan kembali ke halaman sebelumnya...</p>
            </div>
        </div>
    </div>
</div>

<script>
    setTimeout(function () {
        window.history.back();
    }, 3000); // Redirect setelah 3 detik
</script>

@endsection