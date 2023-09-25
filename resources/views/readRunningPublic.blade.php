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
<h1>Public Running Challenges</h1>
  <div class="mb-3">
    <form action="{{ route('searchRunning') }}" method="GET" class="form-inline">
  
    <div class = "row">
        <div class="col-4">
    <div class="form-group">
            <select name="filter" class="form-control">
                <option value="title">Search by title</option>
                <option value="latitude">Search by latitude</option>
                <option value="longitude">Search by longitude</option>
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
      
        <th>Latitude</th>
        <th>Longitude</th>
      
      
      
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
            
            <td>{{ $Challenge->latitude }}</td>
            <td>{{ $Challenge->longitude }}</td>
           
            <td>{{ $Challenge->distance }}</td>
            <td>{{ $Challenge->stepsNum }}</td>
            <td>{{ $Challenge->start_time }}</td>
            <td>{{ $Challenge->end_time }}</td>
            <td>{{ $Challenge->prize }}</td>
            <td>{{ $Challenge->status }}</td>
            <td>{{ $Challenge->winner_points }}</td>
            <td><img src="{{ asset('storage/' . $Challenge->image) }}" alt="{{ $Challenge->name }}" width="100"></td>
            <td><input type="submit"  class="btn btn-danger" formaction="{{ route('deleteChallenge', ['id' => $Challenge->id]) }}"  value="Delete"></td>
            <td>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#usersModal">
        View Users
    </button>
</td>

              

        </tr>
    @endforeach
    
    </tbody>
</table>


</form>
<!-- <div class="modal fade" id="usersModal" tabindex="-1" role="dialog" aria-labelledby="usersModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="usersModalLabel">Users of Challenge</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul>
                    @foreach ($users as $user)
                        <li>{{ $user->id }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div> -->
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <script>
    $(document).ready(function () {
        $('#usersModal').modal('show');
    });
</script> -->


@endsection