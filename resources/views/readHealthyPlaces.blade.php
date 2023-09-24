@extends('layouts.dashboard')
@section('content')

<style>
.confirmation-popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    border: 1px solid #ccc;
    padding: 20px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
    z-index: 1000;
}
</style>
<body>


  <h1>Health Places</h1>
  <div class="mb-3">
    <form action="{{ route('searchHealthPlaces') }}" method="GET" class="form-inline">
  
    <div class = "row">
        <div class="col-4">
    <div class="form-group">
            <select name="filter" class="form-control">
                <option value="name">Search by name</option>
                <option value="address">Search by address</option>
                <option value="latitude">Search by latitude</option>
                <option value="latitude">Search by longitude</option>
            </select>
        </div></div>
        <div class="col-6">
        <div class="form-group">
            <input type="text" name="query" class="form-control" placeholder="Enter your search query">
        </div></div>
        <div class="col-2">
        <button type="submit" class="btn btn-primary">Search</button>
        </div></div>
    </form>
</div>
<br>
<form action="" method ="post" >
<input type= "hidden" name="_method"  value = "delete">
@csrf
  <table class="table">
  <thead class="p-3 mb-2 bg-light text-dark"> 
      <tr style="font-size:15px; font-weight:bold;">
        <th>Name</th>
        <th>Longitude</th>
        <th>Latitude</th>
        <th>Address</th>
        <th>Image</th>
        <th>Action</th>
     </tr>
  </thead>
      <tbody>
    @foreach ($health as $place)
        <tr>
            <td>{{ $place->name }}</td>
            <td>{{ $place->longitude }}</td>
            <td>{{ $place->latitude }}</td>
            <td>{{ $place->address }}</td>
            <td><img src="{{ asset('storage/' . $place->image) }}" alt="{{ $place->name }}" width="100"></td>
            <td><input type="submit"  class="btn btn-danger" formaction="{{ route('deleteHealthPlace', ['id' => $place->id]) }}"  value="Delete"></td>
           
              
      <!-- <td>  <form action="{{ route('deleteHealthPlace', ['id' => $place->id]) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"  onclick="confirmDelete('{{ $place->id }}')">Delete</button>
            </form></td> -->
        </tr>
    @endforeach
    
    </tbody>
</table>

</form>

</body>
<script>
    
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this item?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endsection