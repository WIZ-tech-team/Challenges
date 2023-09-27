@extends('layouts.dashboard')

@section('content')<!DOCTYPE html>


<style>.success-message {
    background-color: #F5F6FA; /* Green background color */
    color:green; /* White text color */
    padding: 10px 20px; /* Padding around the message */
    border-radius: 5px; /* Rounded corners */
    margin: 10px; /* Space from the content above */
}</style>

<?php if (session()->has('success')) :?>
              <div  class="success-message">
                <?= session()->get('success') ?>
              </div>
              <?php endif ?>
<form action="{{ route('createChallenge') }}" method="post"  enctype="multipart/form-data">
  @csrf
  
    <span><h3>Create Public Challegne</h3></span>
   
  <select name="attribute" class="form-control @error('attribute') is-invalid @enderror"class="btn btn-light dropdown-toggle" data-toggle="dropdown" id="attribute" >
  <option value="">Select Challenge Type </option>

    <option name="attribute" value="cylic">Cylic Football Challenge</option>
    <option name="attribute" value="non_cylic"> Non Cylic Challenge </option>
   
  
</select>
@error('attribute')
    <span class="invalid-feedback" role="alert">
      {{ $message }}
    </span>
    @enderror
    <br>

<div class="row">
  <div class="col-6 bg-light p-3">
  <input type="text"  name="title"       placeholder="Challenge title"      class="form-control  @error('title') is-invalid @enderror"      value="{{ old('title') }}">     <p class ="invalid-feedback " >@error('title')      {{ $message }}  @enderror </p><br>
  <input type="text"  name="latitude"    placeholder="Challenge latitude"   class="form-control  @error('latitude') is-invalid @enderror"   value="{{ old('latitude') }}" >   <p class ="invalid-feedback " >@error('latitude')   {{ $message }} @enderror </p><br>
  <input type="text"  name="longitude"   placeholder="Challenge longitude"  class="form-control  @error('longitude') is-invalid @enderror"  value="{{ old('longitude') }}">  <p class ="invalid-feedback ">@error('longitude')  {{ $message }} @enderror </p><br>
  <input type="text"  name="start_time"  placeholder="Challenge start_time" class="form-control  @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" > <p class ="invalid-feedback ">@error('start_time') {{ $message }} @enderror </p><br>
  <input type="text"  name="end_time"    placeholder="Challenge end_time"   class="form-control  @error('end_time') is-invalid @enderror"   value="{{ old('end_time') }}" >   <p class ="invalid-feedback " >@error('end_time')   {{ $message }} @enderror </p><br>

  

  <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" class="btn btn-light dropdown-toggle" data-toggle="dropdown"   id="category_id"
  >
  <option value="">Select Category</option>
  @foreach($category1 as $category1 )
    <option value="{{ $category1->id }}">
    @error('category_id')   {{ $message }} @enderror
      {{ $category1->name }}</option>
 @endforeach
</select><br>
  </div>
<div class="col-6 bg-light p-3"> 
  <input type="text"  name="prize"       placeholder="Challenge prize"      class="form-control"value="{{ old('prize') }}" ><br>
  <input type="text"  name="users_id"    placeholder="Challenge users"      class="form-control"  id="users_id"><br>
  <input type="text"  name="distance"    placeholder="Challenge distance"   class="form-control"  id="distance" value="{{ old('distance') }}"><br>
  <input type="text"  name="stepsNum"    placeholder="Challenge stepsNum"   class="form-control"  id="stepsNum" value="{{ old('stepsNum') }}"><br>
  <input type="file"  name="image"       placeholder="Challenge image"      class="form-control"  id="image" value="{{ old('image') }}"><br>
  <input type="text"  name="winner_points"    placeholder="Challenge winner_points"   class="form-control  @error('winner_points') is-invalid @enderror"  value="{{ old('winner_points') }}">   <p class ="invalid-feedback ">@error('winner_points')   {{ $message }} @enderror </p><br>

</div></div>
<input type="hidden" name="category_id" id="hidden_category_id" value="1">
  <input type="submit" class="btn btn-primary" value="Create Challenge">


</form> 

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   
$(document).ready(function() {
  // Get the select input
  var categorySelect = $("#category_id");
  var stepsNumInput = $("#stepsNum");
  var distanceInput = $("#distance");
  var usersInput   = $("#users_id");
  
  categorySelect.on("change", function() {
    var categoryName = $(this).val();
    if (categoryName == "1") {
      stepsNumInput.hide();
      distanceInput.hide();
      usersInput.hide();
    } else {
      stepsNumInput.show();
      distanceInput.show();
      usersInput.show();
    }
  });
});
</script>
<script>
  var attributeSelect = $("#attribute");
  var stepsNumInput   = $("#stepsNum");
  var distanceInput   = $("#distance");
  var usersInput   = $("#users_id");
  var categorySelect = $("#category_id");
    attributeSelect.on("change", function() {
      var attributeName = $(this).val();
      if (attributeName == "cylic") {
          stepsNumInput.hide();
          distanceInput.hide();
          usersInput.hide();
          categorySelect.hide();
      hiddenCategoryIdInput.val(1);
         
          }else {
           stepsNumInput.show();
          distanceInput.show();
          usersInput.show();
          categorySelect.show();
      hiddenCategoryIdInput.val('');
    }})

</script>

@endsection