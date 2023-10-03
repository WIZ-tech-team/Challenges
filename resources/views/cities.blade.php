@extends('layouts.dashboard')

@section('content')
<style>.success-message {
    background-color: #F5F6FA; /* Green background color */
    color: green; /* White text color */
    padding: 10px 20px; /* Padding around the message */
    border-radius: 5px; /* Rounded corners */
    margin: 10px; /* Space from the content above */
}</style>
<?php if (session()->has('success')) :?>
              <div  class="success-message">
                <?= session()->get('success') ?>
              </div>
              <?php endif ?>
<form action="{{ route('cities') }}" method="post"  enctype="multipart/form-data">
  @csrf
  <input type="text" name="name" placeholder="City name" class="form-control @error('name') is-invalid @enderror"   ><p class ="invalid-feedback ">@error('name') {{ $message }} @enderror </p><br><br>
   <button type="submit" class="btn btn-primary" value="Add City">Add City</button></form>
@endsection