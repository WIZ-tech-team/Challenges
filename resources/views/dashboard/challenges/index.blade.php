@extends('layouts.dashboard')

@section('content')
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <h2>Challenges</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Address</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($challenges as $index => $challenge)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $challenge->id }}</td>
                            <td>{{ $challenge->title }}</td>
                            <td>{{ $challenge->category }}</td>
                            <td>{{ $challenge->latitude }}</td>
                            <td>{{ $challenge->longitude }}</td>
                            <td>{{ $challenge->address }}</td>
                            <td>{{ $challenge->start_time }}</td>
                            <td>{{ $challenge->end_time }}</td>
                            <td>
                                @if ($challenge->image)
                                    <img src="{{ asset('storage/' . $challenge->image) }}" alt="Image" width="60">
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap align-items-center justify-content-center">
                                    {{-- <a href="{{ route('challenges.setResult', $challenge->id) }}" class="btn btn-sm btn-primary">Set Result</a> --}}
                                    {{-- <a href="#" class="btn btn-sm btn-light-success">Set Result</a> --}}
                                    <form action="{{ route('challenges.destroy', $challenge->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this challenge?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
