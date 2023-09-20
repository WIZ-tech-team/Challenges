@extends('layouts.dashboard')

@section('content')
<style>.success-message {
    background-color: #F5F6FA; /* Green background color */
    color: green; /* White text color */
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
<form action="{{ route('createHealthPlace') }}" method="post"  enctype="multipart/form-data">
  @csrf

    <span><h3>Add Healthy Place </h3></span>
  <input type="text"  name="name"        placeholder="Health place name"    class="form-control @error('name') is-invalid @enderror" > 
   <p class ="invalid-feedback ">
           @error('name') 
           {{ $message }} 
           @enderror 
         </p><br>
  <input type="text"  name="latitude"    placeholder="Health place latitude"   class="form-control @error('latitude') is-invalid @enderror"   > <p class ="invalid-feedback ">
           @error('latitude') 
           {{ $message }} 
           @enderror 
         </p><br>
  <input type="text"  name="longitude"   placeholder="Health place longitude"  class="form-control  @error('longitude') is-invalid @enderror"  >
  <p class ="invalid-feedback ">  
      @error('longitude') 
           {{ $message }} 
           @enderror 
         </p><br>
        
  <input type="text"  name="address" placeholder="Health place address" class="form-control  @error('address') is-invalid @enderror" >  
  <p class ="invalid-feedback ">
   @error('address') 
           {{ $message }} 
           @enderror 
         </p><br>
  <input type="file"  name="image"  placeholder="Health place image"   class="form-control @error('image') is-invalid @enderror"  >   
  <p class ="invalid-feedback ">
  @error('image') 
           {{ $message }} 
           @enderror 
         </p><br>


 <input type="submit" class="btn btn-primary" value="Add Healthy Place ">

</form> 
</body>
@endsection