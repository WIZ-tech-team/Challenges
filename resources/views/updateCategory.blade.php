@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <h2>Edit Category</h2>

        <form action="{{ route('updateCategory', ['id' => $category->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label for="name">Category Name:</label>
            <input type="text" name="name" value="{{ $category->name }}" required>
            <br>

            <label for="image">Category Image:</label>
            <input type="file" name="image">
            <br>

            <button type="submit">Update Category</button>
        </form>
    </div>
@endsection
