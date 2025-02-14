<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_no }}</title>
    <style>
        * {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        .text-center {
            text-align: center;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        span,
        div {
            font-family: DejaVu Sans;
            font-size: 16px;
            font-weight: normal;
        }

        th,
        td {
            font-family: DejaVu Sans;
            font-size: 12px;
        }

        .panel {
            margin-bottom: 20px;
            background-color: #fff;
            border: 1px solid transparent;
            border-radius: 4px;
            -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
            box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
        }

        .panel-default {
            border-color: #ddd;
        }

        .panel-body {
            padding: 15px;
        }

        table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 0px;
            border-spacing: 0;
            border-collapse: collapse;
            background-color: transparent;

        }

        thead {
            text-align: left;
            display: table-header-group;
            vertical-align: middle;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
        }

        .well {
            min-height: 20px;
            padding: 19px;
            margin-bottom: 20px;
            background-color: #f5f5f5;
            border: 1px solid #e3e3e3;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .05);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .05);
        }
    </style>
    @if($invoice->duplicate_header)
        <style>
            @page {
                margin-top: 140px;
            }

            header {
                top: -100px;
                position: fixed;
            }
        </style>
    @endif
</head>

<body>
    <header>
        <div style="position:absolute; left:0pt; width:250pt;">
        </div>
        <div style="margin-left:300pt;">
            @if ($invoice->invoice_no)
                <b>Invoice #: </b> {{ $invoice->invoice_no }}
            @endif
            <br />
        </div>
    </header>
    <main>
        <div style="clear:both; position:relative;">
            <div style="position:absolute; left:0pt; width:250pt;">
            </div>
            <div style="margin-left: 300pt;">
                <h4>Invoice Details:</h4>
                <div class="panel panel-default">
                    <div class="panel-body">
                        Created By :{{ $invoice->created_by }}<br />
                        Tanggal Invoice: {{ \Carbon\Carbon::parse($invoice->invoice_date)->isoFormat('DD-MM-YYYY') }}<br />
                        Tanggal Lunas: {{ \Carbon\Carbon::parse($invoice->due_date)->isoFormat('DD-MM-YYYY') }}<br />
                    </div>
                </div>
            </div>
        </div>
        <h3>Invoice Items:</h3>
        <table class="table table-bordered">
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
                @foreach ($invoice->items as $item)
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
                        <td>{{ $invoice->total_quantity }}</td>
                        <td>{{ 'Rp ' . number_format($invoice->total_price, 0, ',', '.') }}</td>
                    </tr>
            </tbody>
        </table>
        
    </main>

    <!-- Page count -->
    <script type="text/php">
            if (isset($pdf) && $GLOBALS['with_pagination'] && $PAGE_COUNT > 1) {
                $pageText = "{PAGE_NUM} of {PAGE_COUNT}";
                $pdf->page_text(($pdf->get_width()/2) - (strlen($pageText) / 2), $pdf->get_height()-20, $pageText, $fontMetrics->get_font("DejaVu Sans, Arial, Helvetica, sans-serif", "normal"), 7, array(0,0,0));
            }
        </script>
</body>

</html>