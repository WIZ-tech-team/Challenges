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
<h1>Callenges</h1>
  <div class="mb-3">
    <form action="{{ route('searchChallenges') }}" method="GET" class="form-inline">
  
    <div class = "row">
        <div class="col-4">
    <div class="form-group">
            <select name="filter" class="form-control">
                <option value="title">Search by title</option>
                <option value="category_id">Search by category</option>
                <option value="prize">Search by prize</option>
              
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


  <table class="table table-bordered">
  <thead class="p-3 mb-2 bg-light text-dark"> 
      <tr  style="font-size:15px; font-weight:bold;">
        <th>Title</th>
        <th>Type</th>
        <th>Latitude</th>
        <th>Longitude</th>
        <th>Team_id</th>
        <th>Category</th>
        <th>Date</th>
        <th>Distance</th>
        <th>StepsNum</th>
        <th>Start time</th>
        <th>End time</th>
        <th>Prize</th>
        <th>Status</th>
        <th>Points</th>
        <th>Image</th>
        <th>Action</th>
     </tr>
  </thead>
      <tbody>
    @foreach ($Challenge as $Challenge)
        <tr>
            <td>{{ $Challenge->title }}</td>
            <td>{{ $Challenge->type }}</td>
            <td>{{ $Challenge->latitude }}</td>
            <td>{{ $Challenge->longitude }}</td>
            <td>{{ $Challenge->team_id }}</td>
            <td>{{ $Challenge->category->name }}</td>
            <td>{{ $Challenge->date }}</td>
            <td>{{ $Challenge->distance }}</td>
            <td>{{ $Challenge->stepsNum }}</td>
            <td>{{ $Challenge->start_time }}</td>
            <td>{{ $Challenge->end_time }}</td>
            <td>{{ $Challenge->prize }}</td>
            <td>{{ $Challenge->status }}</td>
            <td>{{ $Challenge->winner_points }}</td>
            <td><img src="{{ asset('storage/' . $Challenge->image) }}" alt="{{ $Challenge->name }}" width="100"></td>
            <td><input type="submit"  class="btn btn-danger" formaction="{{ route('deleteChallenge', ['id' => $Challenge->id]) }}"  value="Delete"></td>
           
              

        </tr>
    @endforeach
    
    </tbody>
</table>

</form>

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


@endsection