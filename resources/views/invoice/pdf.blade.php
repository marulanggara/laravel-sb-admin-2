<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_no }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        .text-center {
            text-align: center;
        }

        body {
            font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            /* Font lebih besar */
        }

        h3 {
            font-size: 20px;
            /* Perbesar ukuran teks utama */
            font-weight: normal;
        }

        th,
        td {
            font-size: 16px;
            /* Perbesar font dalam tabel */
        }

        .panel {
            margin-bottom: 20px;
            font-size: 18px;
            /* Perbesar font dalam panel */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        thead {
            text-align: left;
            display: table-header-group;
            vertical-align: middle;
            font-size: 16px;
        }

        th,
        td {
            border: 2px solid #000;
            /* Border lebih tebal */
            padding: 12px;
            /* Padding lebih besar */
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }

        @page {
            margin: 40px 60px;
            /* Margin lebih besar */
        }
    </style>
</head>

<body>
    <header>
        <div style="margin-left: 300pt; text-align: right; margin-bottom: 20px; font-size: 18px;">
            @if ($invoice->invoice_no)
                <h2><b>Invoice #: </b> {{ $invoice->invoice_no }}</h2>
            @endif
        </div>
    </header>

    <main>
        <h3><strong>Invoice Information:</strong></h3>
        <div class="panel">
            <div class="panel-body">
                Created By      : {{ $invoice->created_by }}<br>
                Tanggal Invoice : {{ \Carbon\Carbon::parse($invoice->invoice_date)->isoFormat('DD-MM-YYYY') }}<br>
                Tanggal Lunas   : {{ \Carbon\Carbon::parse($invoice->due_date)->isoFormat('DD-MM-YYYY') }}<br>
            </div>
        </div>

        <h3 style="margin-top: 20px"><strong>Invoice Items:</strong></h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Item Code</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Qty.</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->product_code }}</td>
                        <td>{{ $item->unit_name }}</td>
                        <td>{{ 'Rp ' . number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ 'Rp ' . number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="5" class="text-center"><strong>Total</strong></td>
                    <td>{{ $items->sum('quantity') }}</td>
                    <td>{{ 'Rp ' . number_format($items->sum('total_price'), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </main>

    <!-- Script untuk nomor halaman -->
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(255, 810, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", null, 10, array(0, 0, 0));
        }
    </script>
</body>

</html>