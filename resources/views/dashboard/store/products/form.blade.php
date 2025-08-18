@extends('layouts.dashboard')

@section('content')
    <div class="container mt-4">
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

        <h3 class="mb-4">
            {{ isset($product) ? 'Update Product' : 'Create New Product' }}
        </h3>
        <form action="{{ isset($product) ? route('storeProducts.update', $product->id) : route('storeProducts.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($product))
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="store_category_id" class="form-label">Category</label>
                <select name="store_category_id" id="store_category_id"
                    class="form-select @error('store_category_id') is-invalid @enderror" required>
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('store_category_id', $product->category->id ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->title }}
                        </option>
                    @endforeach
                </select>
                @error('store_category_id')
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

            <div class="mb-3">
                <label class="form-label">Image</label>
                <div class="">
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif" class="form-control @error('image') is-invalid @enderror">
                    @error('image')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="mb-3 form-check">
                {{-- <label class="form-label">Availability</label> --}}
                <div class="">
                    <input type="hidden" name="is_available" value="0">
                    <input type="checkbox" name="is_available" id="is_available" class="form-check-input" value="1"
                        {{ old('is_available', $product->is_available ?? 0) ? 'checked' : '' }}>
                    <label for="is_available" class="form-check-label">Available</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                {{ isset($product) ? 'Update Product' : 'Create Product' }}
            </button>
            <a href="{{ route('storeProducts.index') }}" class="btn btn-light ms-2">Back to Products</a>
        </form>
    </div>
@endsection
