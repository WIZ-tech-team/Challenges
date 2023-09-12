@extends('layouts.dashboard')

@section('content')
<style>.success-message {
    background-color: #F5F6FA; /* Green background color */
    color: #78829D; /* White text color */
    padding: 10px 20px; /* Padding around the message */
    border-radius: 5px; /* Rounded corners */
    margin: 10px; /* Space from the content above */
}</style>
<?php if (session()->has('success')) :?>
              <div  class="success-message">
                <?= session()->get('success') ?>
              </div>
              <?php endif ?>
<form action="{{ route('createPost') }}" method="post"  enctype="multipart/form-data">
  @csrf
  <input type="text" name="title" placeholder="Post title" class="form-control @error('title') is-invalid @enderror"   ><p class ="invalid-feedback ">@error('title') {{ $message }} @enderror </p><br><br>
  <input type="text" name="descreption" placeholder="Post content" class="form-control @error('descreption') is-invalid @enderror"   ><p class ="invalid-feedback "> @error('descreption') {{ $message }} @enderror </p><br><br>
  <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror"    ><p class ="invalid-feedback ">@error('image') {{ $message }} @enderror </p><br><br>
  <button type="submit" class="btn btn-primary" value="Create Post">Create Post</button></form>
@endsection