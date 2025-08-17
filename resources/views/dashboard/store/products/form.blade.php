@extends('layouts.dashboard')

@section('content')
    <div class="container mt-4">
        <h3 class="mb-4">
            {{ isset($product) ? 'Update Product' : 'Create New Product' }}
        </h3>
        <form action="{{ isset($product) ? route('storeProducts.update', $product->id) : route('storeProducts.store') }}"
            method="POST">
            @csrf
            @if (isset($product))
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror"
                    required>
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->title }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $product->name ?? '') }}" required>
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="price_in_points" class="form-label">Price (Points)</label>
                <input type="number" name="price_in_points" id="price_in_points"
                    class="form-control @error('price_in_points') is-invalid @enderror"
                    value="{{ old('price_in_points', $product->price_in_points ?? '') }}" min="0" required>
                @error('price_in_points')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity"
                    class="form-control @error('quantity') is-invalid @enderror"
                    value="{{ old('quantity', $product->quantity ?? '') }}" min="0" required>
                @error('quantity')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_available" id="is_available" class="form-check-input"
                    {{ old('is_available', $product->is_available ?? false) ? 'checked' : '' }}>
                <label for="is_available" class="form-check-label">Available</label>
            </div>

            <button type="submit" class="btn btn-primary">
                {{ isset($product) ? 'Update Product' : 'Create Product' }}
            </button>
            <a href="{{ route('storeProducts.index') }}" class="btn btn-light ms-2">Back to Products</a>
        </form>
    </div>
@endsection
