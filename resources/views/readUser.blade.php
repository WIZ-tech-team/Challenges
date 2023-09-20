@extends('layouts.dashboard')
@section('content')
<style>

.user-info {
  margin-bottom: 20px;
}

.user-info h3 {
  font-size: 20px;
  font-weight: Bold;
  color: #333;
}
.user-info h2 {
  font-size: 20px;
  font-weight: normal;
  color:gray;
}



.edit-link {
  text-align: right;
}

.edit-link a {
  font-size: 16px;
  color: #333;
}

.edit-link a:hover {
  color: #555;
}
</style>
<div class="user-info">
    <table >
        <tr>
            <td><h2>Username :</h2></td>
            <td><h3>{{ Auth::user()->name }}</h3></td>
        </tr>
        <tr>
            <td><h2>Email :</h2></td>
            <td><h3>{{ Auth::user()->email }}</h3></td>
        </tr>
    </table>


 
    <div class="edit-link">
    <button type="button" class="btn btn-primary" onclick="window.location.href = '{{ route('profile.edit') }}'">Edit Profile</button>

    </div>
</div>

@endsection