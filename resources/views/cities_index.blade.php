@extends('layouts.dashboard')
@section('content')
<style>.success-message {
    background-color: #F5F6FA; 
    color:green; 
    padding: 10px 20px; 
    border-radius: 5px; 
    margin: 10px; 
}
</style>
<div style="text-align:right;">
    <a href="{{ route('create_city') }}" class="btn btn-success">Add new City</a>
</div>
<?php if (session()->has('success')) :?>
              <div  class="success-message">
                <?= session()->get('success') ?>
              </div>
              <?php endif ?>
<form action="" method="post" >
<input type= "hidden" name="_method"  value = "delete">
@csrf
@method('delete')
<div class="container mt-4">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>City Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cities as $city)
            <tr>
                <td>{{ $city->id }}</td>
                <td>{{ $city->name }}</td>
                <td>
                
                    <input type="submit"  class="btn btn-danger" formaction="{{ route('delete_city', ['id' => $city->id]) }}" onclick="return confirm('Are you sure you want to delete this city?')" value="Delete">
                  
                    
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    
</div>
</form>
@endsection

