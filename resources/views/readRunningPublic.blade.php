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
    z-index: 1000;}
    .center-text {
  text-align: center;

}

.td {
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn {
  display: block;
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
  <thead>
    <tr>
      <th class="center-text">Title</th>
      <th class="center-text">Latitude</th>
      <th class="center-text">Longitude</th>
      <th class="center-text">Distance</th>
      <th class="center-text">StepsNum</th>
      <th class="center-text">Start time</th>
      <th class="center-text">End time</th>
      <th class="center-text">Prize</th>
      <th class="center-text">Status</th>
      <th class="center-text">Points</th>
      <th class="center-text">Image</th>
      <th class="center-text">Action</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($Challenge as $Challenge)
      <tr>
        <td class="center-text">{{ $Challenge->title }}</td>
        <td class="center-text">{{ $Challenge->latitude }}</td>
        <td class="center-text">{{ $Challenge->longitude }}</td>
        <td class="center-text">{{ $Challenge->distance }}</td>
        <td class="center-text">{{ $Challenge->stepsNum }}</td>
        <td class="center-text">{{ $Challenge->start_time }}</td>
        <td class="center-text">{{ $Challenge->end_time }}</td>
        <td class="center-text">{{ $Challenge->prize }}</td>
        <td class="center-text">{{ $Challenge->status }}</td>
        <td class="center-text">{{ $Challenge->winner_points }}</td>
        <td class="center-text"><img src="{{ asset('storage/' . $Challenge->image) }}" alt="{{ $Challenge->name }}" width="90" height="70"></td>
        <td class="center-text"><input type="submit"  class="btn btn-danger btn-sm" formaction="{{ route('deleteChallenge', ['id' => $Challenge->id]) }}" value="Delete"></td>
        <td class="center-text">
          <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#usersModal">
            Users
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