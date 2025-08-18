@extends('layouts.dashboard')

@section('content')
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Store Orders</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Product</th>
                        <th>Points</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $index => $order)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $order->apiUser->name ?? 'N/A' }}</td>
                            <td>{{ $order->product->name ?? 'N/A' }}</td>
                            <td>{{ $order->points }}</td>
                            <td>
                                @if ($order->status === 'pending')
                                    <span class="badge bg-warning text-light-warning">pending</span>
                                @elseif($order->status === 'approved')
                                    <span class="badge bg-success text-light-success">approved</span>
                                @elseif($order->status === 'not-approved')
                                    <span class="badge bg-danger text-light-danger">not-approved</span>
                                @elseif($order->status === 'completed')
                                    <span class="badge bg-light-success text-success">completed</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="badge bg-light-danger text-danger">cancelled</span>
                                @endif
                            </td>
                            <td>
                                @if ($order->status === 'pending')
                                    <div class="d-flex gap-2 flex-wrap align-items-center justify-content-center">
                                        <form action="{{ route('storeOrders.approve', [$order->id]) }}"
                                            method="POST" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success"
                                                onclick="return confirm('Approve this order?')">Approve</button>
                                        </form>
                                        <form action="{{ route('storeOrders.notApprove', [$order->id]) }}"
                                            method="POST" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Mark as not approved?')">Not Approve</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted">No actions</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
