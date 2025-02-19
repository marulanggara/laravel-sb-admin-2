@extends('layouts.admin')

@section('main-content')

    <!-- Content Column -->
    <div class="mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h2>Unit Log History</h2>
            </div>

            <div class="card-body">
                <a href="{{ route('units.index') }}" class="btn btn-secondary mb-2">Back</a>
                <div class="table-responsive">
                    @if ($logs->isEmpty())
                        <p>No logs found for units.</p>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Action</th>
                                    <th>Changed By</th>
                                    <th>Old Data</th>
                                    <th>New Data</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ $logs->firstItem() + $loop->index }}</td>
                                        <!-- Action dengan Warna -->
                                        <td>
                                            @if ($log->action === 'delete')
                                                <span class="badge badge-danger">Delete</span>
                                            @elseif ($log->action === 'create')
                                                <span class="badge badge-success">Create</span>
                                            @elseif ($log->action === 'update')
                                                <span class="badge badge-warning text-dark">Update</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($log->action) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->user->name }}</td>
                                        <td>
                                            @php
                                                $oldData = json_decode($log->old_data, true);
                                            @endphp
                                            <pre>{{ json_encode($oldData, JSON_PRETTY_PRINT) }}</pre>
                                        </td>
                                        <td>
                                            @php
                                                $newData = json_decode($log->new_data, true);
                                            @endphp
                                            <pre>{{ json_encode($newData, JSON_PRETTY_PRINT) }}</pre>
                                        </td>
                                        <td>{{ $log->created_at->format('d-m-Y, H:i:s') }}</td> <!-- Format Timestamp -->
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="d-flex justify-content-center">
                    {{ $logs->links() }}
                </div>
            </div>   
        </div>
    </div>
@endsection
