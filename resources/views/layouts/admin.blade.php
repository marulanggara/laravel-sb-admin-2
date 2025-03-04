<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Laravel SB Admin 2">
    <meta name="author" content="Alejandro RH">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Favicon -->
    <link href="{{ asset('img/favicon.png') }}" rel="icon" type="image/png">

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <style>
        input[type="checkbox"].larger {
            transform: scale(1.5);
            margin:3px;
        }

        /* Membesarkan ukuran checkbox */
        .form-check-input {
            transform: scale(1.5);
            /* Sesuaikan ukuran */
            margin-right: 10px;
            /* Memberikan jarak */
        }
        #invoiceItemsTable {
            table-layout: fixed; /* Membuat lebar tabel tetap */
            width: 100%;
        }

        #totalTable {
            table-layout: fixed; /* Membuat lebar tabel tetap */
            width: 100%;
        }

        #totalTable th,
        #totalTable td {
            white-space: nowrap; /* Mencegah teks turun ke bawah */
            vertical-align: middle;
        }

        #invoiceItemsTable th,
        #invoiceItemsTable td {
            white-space: nowrap; /* Mencegah teks turun ke bawah */
            vertical-align: middle;
        }

        #invoiceItemsTable .product-select {
            max-width: 200px; /* Membatasi select agar tidak melebar */
        }     
    </style>
</head>
<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/home') }}">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-laugh-wink"></i>
            </div>
            <div class="sidebar-brand-text mx-3">SB Admin <sup>2</sup></div>
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">

        <!-- Nav Item - Dashboard -->
        <li class="nav-item {{ Nav::isRoute('home') }}">
            <a class="nav-link" href="{{ route('home') }}">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>{{ __('Dashboard') }}</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">
        
        <!-- Heading Menu -->
        <div class="sidebar-heading">
            {{ __('Menu') }}
        </div>

        <!-- Nav Item - Supplier, Product, Warehouse Collapse Menu CRUD -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true"
                aria-controls="collapseOne">
                <i class="fa-solid fa-box-open"></i>
                <span>{{ __('Units and Products') }}</span>
            </a>
        
            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @can('list product')
                        <a class="collapse-item" href="{{route('units.index')}}">{{ __('Units') }}</a>
                    @else
                        <a class="collapse-item" href="#" disabled>{{ __('Units') }}</a>
                    @endcan
                    @can('list product')
                        <a class="collapse-item" href="{{route('products.index')}}">{{ __('Products') }}</a>
                    @else
                        <a class="collapse-item" href="#" disabled>{{ __('Products') }}</a>
                    @endcan
                </div>
            </div>
        </li>
        <li class="nav-item {{ Nav::isRoute('suppliers.*') }}">
            <a class="nav-link" href="{{ route('suppliers.index') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>{{ __('Supplier') }}</span>
            </a>
        </li>
        <li class="nav-item {{ Nav::isRoute('payments.*') }}">
            <a class="nav-link" href="{{ route('payments.index') }}">
                <i class="fa-solid fa-credit-card-alt"></i>
                <span>{{ __('Payment') }}</span>
            </a>
        </li>
        <li class="nav-item {{ Nav::isRoute('warehouses.*') }}">
            <a class="nav-link" href="{{ route('warehouses.index') }}">
                <i class="fa-solid fa-warehouse"></i>
                <span>{{ __('Warehouse') }}</span>
            </a>
        </li>
        <li class="nav-item {{ Nav::isRoute('invoice.*') }}">
            <a class="nav-link" href="{{ route('invoice.index') }}">
                <i class="fa-solid fa-file-invoice"></i>
                <span>{{ __('Invoice') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseThree" aria-expanded="true"
                aria-controls="collapseThree">
                <i class="fa-solid fa-wallet"></i>
                <span>{{ __('Finance') }}</span>
            </a>
        
            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @can('list product')
                        <a class="collapse-item" href="{{route('incomes.index')}}">{{ __('Income') }}</a>
                    @else
                        <a class="collapse-item" href="#" disabled>{{ __('Income') }}</a>
                    @endcan
                    @can('list product')
                        <a class="collapse-item" href="{{route('expenses.index')}}">{{ __('Expense') }}</a>
                    @else
                        <a class="collapse-item" href="#" disabled>{{ __('Expense') }}</a>
                    @endcan
                </div>
            </div>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">
        
        <!-- Heading Menu -->
        <div class="sidebar-heading">
            {{ __('Users and Roles') }}
        </div>

        <!-- Nav Item - Role and Permission Collapse Menu CRUD -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true"
                aria-controls="collapseTwo">
                <i class="fas fa-fw fa-cog"></i>
                <span>{{ __('Users and Roles') }}</span>
            </a>

            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @can('list user')
                        <a class="collapse-item" href="{{route('users.index')}}">{{ __('Users') }}</a>
                    @else
                        <a class="collapse-item" href="#" disabled>{{ __('Users') }}</a>
                    @endcan
                    @can('list role')
                        <a class="collapse-item" href="{{route('roles.index')}}">{{ __('Roles') }}</a>
                    @else
                        <a class="collapse-item" href="#" disabled>{{ __('Roles') }}</a>
                    @endcan
                </div>
            </div>
        </li>
        
        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                <!-- Sidebar Toggle (Topbar) -->
                <button id="sidebarToggle" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>

                <!-- Topbar Navbar -->
                <ul class="navbar-nav ml-auto">

                    <div class="topbar-divider d-none d-sm-block"></div>

                    <!-- Nav Item - User Information -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }}</span>
                            <figure class="img-profile rounded-circle avatar font-weight-bold" data-initial="{{ Auth::user()->name[0] }}"></figure>
                        </a>
                        <!-- Dropdown - User Information -->
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="{{ route('profile') }}">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                {{ __('Profile') }}
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                {{ __('Logout') }}
                            </a>
                        </div>
                    </li>

                </ul>

            </nav>
            <!-- End of Topbar -->

            <!-- Begin Page Content -->
            <div class="container-fluid">

                @yield('main-content')

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Maintained by <a href="https://github.com/aleckrh" target="_blank">AleckRH</a>. {{ now()->year }}</span>
                </div>
            </div>
        </footer>
        <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

</div>

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('Ready to Leave?') }}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-link" type="button" data-dismiss="modal">{{ __('Cancel') }}</button>
                <a class="btn btn-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Scripts -->
@stack('scripts')
<script>
    $(document).ready(function () {
        $("#sidebarToggle").on("click", function () {
            $("body").toggleClass("sidebar-toggled");
            $(".sidebar").toggleClass("toggled");
        });
        // Fungsi untuk memformat angka ke format Rupiah
        function formatRupiah(total) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(total);
        }
         // Format price pada table
        $('.prices').each(function () {
            let price = $(this).data('price');
            $(this).text(formatRupiah(price));
        });
    });
</script>
</body>
</html>
