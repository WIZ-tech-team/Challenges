@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <h2>Awards</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>For Rank</th>
                        <th>Details</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($awards as $index => $award)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $award->name }}</td>
                            <td>{{ $award->for_rank }}</td>
                            <td>{{ $award->details }}</td>
                            <td>
                                @if (is_array($award->products))
                                    {{ implode(', ', $award->products) }}
                                @else
                                    {{ $award->products }}
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap align-items-center justify-content-center">
                                    <form action="{{ route('awards.destroy', $award->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this award?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No awards found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
