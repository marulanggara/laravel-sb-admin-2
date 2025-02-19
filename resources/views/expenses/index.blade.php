@extends('layouts.admin')

@section('main-content')

<!-- Content Column -->
<div class="mb-4">
    <div class="card">
        <div class="card-header">
            <h4 class="m-0 font-weight-bold text-primary">Expense List</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    {{-- Button range date --}}
                    <div class="form-group">Filter :
                        <input type="text" name="daterange" class="form-control" id="daterange" />
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="income-table">
                    <thead>
                        {{-- Tampilkan Bulan 1-12 --}}
                        <tr>
                            <th>No.</th>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="expense-table-body">
                        @foreach ($monthsAvailable as $month)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $month['month'] }}</td>
                                <td>{{ $month['year'] }}</td>
                                <td class="prices" data-price="{{ $month['total_price'] }}">{{ $month['total_price'] }}</td>
                                <td>
                                    @can('update payment')
                                    <a href="{{ route('expenses.show', ['month' => $month['num'], 'year' => $month['year'], 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                                        class="btn btn-sm transparent"><i class="fa-solid fa-eye fa-lg"></i></a>
                                    @else
                                    <a href="#" class="btn btn-sm transparent disabled"><i class="fa-solid fa-eye fa-lg"></i></a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(function () {
        var start = moment().subtract(1, 'days');
        var end = moment();

        $('input[name="daterange"]').daterangepicker({
            startDate: start,
            endDate: end,
            opens: 'left',
            minYear: 1945,
            maxYear: moment().year(),
            locale: {
                format: 'DD-MM-YYYY'
            }
        }, function (start, end, label) {
            fetchFilteredData(start.format('DD-MM-YYYY'), end.format('DD-MM-YYYY'));
        });

        function fetchFilteredData(startDate, endDate) {
            $.ajax({
                url: `{{ route('expenses.index') }}`,
                type: 'GET',
                data: { start_date: startDate, end_date: endDate },
                success: function (response) {
                    let rows = '';
                    if (response.monthsAvailable.length > 0) {
                        response.monthsAvailable.forEach((month, index) => {
                            rows += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${month.month}</td>
                                    <td>${month.year}</td>
                                    <td>${month.total_price}</td>
                                    <td>
                                        <a href="#" class="btn btn-sm transparent detail-btn" data-month="${month.num}" data-year="${month.year}">
                                            <i class="fa-solid fa-eye fa-lg"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        rows = '<tr><td colspan="4" class="text-center">No data available</td></tr>';
                    }
                    $('#expense-table-body').html(rows);
                },
                error: function () {
                    alert('Failed to fetch data, please try again.');
                }
            });
        }

        // Event klik tombol detail
        $(document).on('click', '.detail-btn', function (e) {
            e.preventDefault();

            var month = $(this).data('month');
            var year = $(this).data('year');
            var daterange = $('#daterange').val();
            var startDate, endDate;

            if (daterange) {
                var dates = daterange.split(" - ");
                var startDate = dates[0];
                var endDate = dates[1];
            } else {
                startDate = moment(`${year}-${month}-01`).startOf('month').format('DD-MM-YYYY');
                endDate = moment(`${year}-${month}-01`).endOf('month').format('DD-MM-YYYY');
            }
            
            window.location.href = `{{ route('expenses.show') }}?month=${month}&year=${year}&start_date=${startDate}&end_date=${endDate}`;
        });
    });
</script>
@endsection