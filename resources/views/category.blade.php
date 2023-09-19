@extends('layouts.dashboard')

@section('content')
<form action="{{ route('createCategory') }}" method="post"  enctype="multipart/form-data">
  @csrf
  <input type="text" name="name" class ="form-control"  placeholder="Category name"><br>
  <input type="file" name="image" class ="form-control" placeholder="Category image"><br>
  <input type="submit"class="btn btn-primary"  value="Create category">
</form>
@endsection