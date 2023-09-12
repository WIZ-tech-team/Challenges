@extends('layouts.dashboard')

@section('content')<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHALLENGE</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
</head>
<style>.success-message {
    background-color: #F5F6FA; /* Green background color */
    color: #78829D; /* White text color */
    padding: 10px 20px; /* Padding around the message */
    border-radius: 5px; /* Rounded corners */
    margin: 10px; /* Space from the content above */
}</style>
<body>
<?php if (session()->has('success')) :?>
              <div  class="success-message">
                <?= session()->get('success') ?>
              </div>
              <?php endif ?>
<form action="{{ route('createChallenge') }}" method="post"  enctype="multipart/form-data">
  @csrf
  <div class="row">
    <span><h3>Create Public Challegne</h3></span>
  <div class="col-6 bg-light p-3">
  <input type="text"  name="title"       placeholder="Challenge title"      class="form-control  @error('title') is-invalid @enderror" >     <p class ="invalid-feedback ">@error('title')      {{ $message }}  @enderror </p><br>
  <input type="text"  name="latitude"    placeholder="Challenge latitude"   class="form-control  @error('latitude') is-invalid @enderror" >   <p class ="invalid-feedback ">@error('latitude')   {{ $message }} @enderror </p><br>
  <input type="text"  name="longitude"   placeholder="Challenge longitude"  class="form-control  @error('longitude') is-invalid @enderror" >  <p class ="invalid-feedback ">@error('longitude')  {{ $message }} @enderror </p><br>
  <input type="text"  name="start_time"  placeholder="Challenge start_time" class="form-control  @error('start_time') is-invalid @enderror" > <p class ="invalid-feedback ">@error('start_time') {{ $message }} @enderror </p><br>
  <input type="text"  name="end_time"    placeholder="Challenge end_time"   class="form-control  @error('end_time') is-invalid @enderror" >   <p class ="invalid-feedback ">@error('end_time')   {{ $message }} @enderror </p><br>

 
  <select name="category_id" class="form-control"class="btn btn-light dropdown-toggle" data-toggle="dropdown"  id="category_id">
  <option value="">Select Category</option>
  @foreach($category1 as $category1 )
    <option value="{{ $category1->id }}">{{ $category1->name }}</option>
 @endforeach
</select><br>
  </div>
<div class="col-6 bg-light p-3"> 
  <input type="text"  name="date"        placeholder="Challenge date"       class="form-control   @error('date') is-invalid @enderror" ><p class ="invalid-feedback "> @error('date') {{ $message }} @enderror </p><br>
  <input type="text"  name="prize"       placeholder="Challenge prize"      class="form-control" ><br>
  <input type="text"  name="users_id"    placeholder="Challenge users"      class="form-control" ><br>
  <input type="text"  name="distance"    placeholder="Challenge distance"   class="form-control"  id="distance"><br>
  <input type="text"  name="stepsNum"    placeholder="Challenge stepsNum"   class="form-control"  id="stepsNum"><br>
  <input type="file"  name="image"       placeholder="Challenge image"      class="form-control"  id="image" ><br>
  </div></div>
  <input type="submit" class="btn btn-primary" value="Create Challenge">


</form> 
</body>

</html>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   
$(document).ready(function() {
  // Get the select input
  var categorySelect = $("#category_id");

  // Get the stepsNum input
  var stepsNumInput = $("#stepsNum");

  // Get the distance input
  var distanceInput = $("#distance");

 
  categorySelect.on("change", function() {
    var categoryName = $(this).val();
    if (categoryName == "1") {
      stepsNumInput.hide();
      distanceInput.hide();
    } else {
      stepsNumInput.show();
      distanceInput.show();
    }
  });
});</script>
@endsection