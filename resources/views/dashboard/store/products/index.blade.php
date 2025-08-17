@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Products</h2>
            <a href="{{ route('storeProducts.create') }}" class="btn btn-primary">Create New Product</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Name</th>
                        <th>Price (Points)</th>
                        <th>Quantity</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product->category->title ?? 'N/A' }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->price_in_points }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>
                                @if($product->is_available)
                                    <span class="badge bg-success text-white">available</span>
                                @else
                                    <span class="badge bg-danger text-white">not-available</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap align-items-center justify-content-center">
                                    <a href="{{ route('storeProducts.edit', $product->id) }}" class="btn btn-sm btn-light-success">Update</a>
                                    <form action="{{ route('storeProducts.destroy', $product->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this product?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection