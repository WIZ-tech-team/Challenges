{{-- filepath: d:\Work\Wiz-Tech\Global Challenges\Challenges\resources\views\dashboard\store\categories\create.blade.php --}}
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
            {{ isset($category) ? 'Update Category' : 'Create New Category' }}
        </h3>
        <form
            action="{{ isset($category) ? route('storeCategories.update', $category->id) : route('storeCategories.store') }}"
            method="POST">
            @csrf
            @if (isset($category))
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="title" class="form-label">Category Title</label>
                <input type="text" name="title" id="title"
                    class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title', $category->title ?? '') }}" required>
                @error('title')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                {{ isset($category) ? 'Update Category' : 'Create Category' }}
            </button>
            <a href="{{ route('storeCategories.index') }}" class="btn btn-light ms-2">Back to Categories</a>
        </form>
    </div>
@endsection
