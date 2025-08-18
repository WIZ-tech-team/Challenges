@extends('layouts.dashboard')

@section('content')
    <div class="container mt-4">
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

        <h3 class="mb-4">Create New Challenge</h3>

        <form action="{{ route('challenges.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Challenge Info --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="card-title">
                        Challenge Info
                    </div>
                </div>
                <div class="card-body row">
                    <div class="col-md-6 mb-3">
                        <label for="title">Title</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') }}" placeholder="Challenge Title">
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="category">Category</label>
                        <select name="category" id="category" class="form-control @error('category') is-invalid @enderror">
                            <option value="">Select Category</option>
                            <option value="football" {{ old('category') == 'football' ? 'selected' : '' }}>Football</option>
                            <option value="running" {{ old('category') == 'running' ? 'selected' : '' }}>Running</option>
                        </select>
                        @error('category')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Location --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="card-title">
                        Location
                    </div>
                </div>
                <div class="card-body row">
                    <div class="col-md-4 mb-3">
                        <label for="latitude">Latitude</label>
                        <input type="text" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
                            value="{{ old('latitude') }}" placeholder="Latitude">
                        @error('latitude')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="longitude">Longitude</label>
                        <input type="text" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
                            value="{{ old('longitude') }}" placeholder="Longitude">
                        @error('longitude')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="address">Address</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                            value="{{ old('address') }}" placeholder="Address">
                        @error('address')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Participants --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="card-title">Participants</div>
                </div>
                <div class="card-body row">
                    <div class="col-md-6 mb-3">
                        <label for="participant_type">Participant Type</label>
                        <select name="participant_type" id="participant_type"
                            class="form-control @error('participant_type') is-invalid @enderror">
                            <option value="">Select Type</option>
                            <option value="invite" {{ old('participant_type') == 'invite' ? 'selected' : '' }}>Invite
                            </option>
                            <option value="participate" {{ old('participant_type') == 'participate' ? 'selected' : '' }}>
                                Participate</option>
                        </select>
                        @error('participant_type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    {{-- Football Teams Tag Input --}}
                    <div class="col-md-6 mb-3" id="football-teams" style="display: none;">
                        <label for="teams">Teams</label>
                        <select name="teams[]" id="teams" class="form-control tags-input" multiple>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}"
                                    {{ collect(old('teams'))->contains($team) ? 'selected' : '' }}>{{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            For "Invite": select at least 2 teams.<br>
                            For "Participate": select exactly 2 teams.
                        </small>
                    </div>
                    {{-- Running Teams --}}
                    <div class="col-md-6 mb-3" id="running-teams" style="display: none;">
                        <label for="teams_running">Teams</label>
                        <select name="teams_running[]" id="teams_running" class="form-control tags-input" multiple>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}"
                                    {{ collect(old('teams'))->contains($team) ? 'selected' : '' }}>
                                    {{ $team->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Add teams for running challenge.
                        </small>
                    </div>
                </div>
            </div>

            {{-- Timing --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="card-title">Timing</div>
                </div>
                <div class="card-body row">
                    <div class="col-md-6 mb-3">
                        <label for="start_time">Start Time</label>
                        <input type="datetime-local" name="start_time"
                            class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}">
                        @error('start_time')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_time">End Time</label>
                        <input type="datetime-local" name="end_time"
                            class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}">
                        @error('end_time')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Image --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="card-title">Challenge Image</div>
                </div>
                <div class="card-body">
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
                    @error('image')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Custom Fields --}}
            {{-- <div class="card mb-3" id="football-fields" style="display: none;">
                <div class="card-header">
                    <div class="card-title">
                        Football Challenge Details
                    </div>
                </div>
                <div class="card-body row">
                    No additional details.
                </div>
            </div> --}}

            <div class="card mb-3" id="running-fields" style="display: none;">
                <div class="card-header">
                    <div class="card-title">
                        Running Challenge Details
                    </div>
                </div>
                <div class="card-body row">
                    <div class="col-md-6 mb-3">
                        <label for="distance">Distance (km)</label>
                        <input type="number" step="0.01" name="distance" class="form-control"
                            value="{{ old('distance') }}" placeholder="Distance">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create Challenge</button>
        </form>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endpush

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(function() {
            function toggleCategoryFields() {
                var cat = $('#category').val();
                $('#football-teams').hide();
                // $('#football-fields, #football-teams').hide();
                $('#running-fields, #running-teams').hide();
                if (cat === 'football') {
                    $('#football-teams').show();
                    // $('#football-fields, #football-teams').show();
                    $('#running-teams').hide();
                } else if (cat === 'running') {
                    $('#running-fields, #running-teams').show();
                    $('#football-teams').hide();
                    // $('#football-fields, #football-teams').hide();
                }
            }
            $('#category').on('change', toggleCategoryFields);
            toggleCategoryFields();

            // Select2 for tags input
            $('.tags-input').select2({
                tags: true,
                tokenSeparators: [','],
                width: '100%'
            });

            // Teams validation for football
            function validateFootballTeams() {
                var cat = $('#category').val();
                var participantType = $('#participant_type').val();
                var teams = $('#teams').val() || [];
                if (cat === 'football') {
                    if (participantType === 'invite' && teams.length < 2) {
                        $('#teams').addClass('is-invalid');
                    } else if (participantType === 'participate' && teams.length !== 2) {
                        $('#teams').addClass('is-invalid');
                    } else {
                        $('#teams').removeClass('is-invalid');
                    }
                } else {
                    $('#teams').removeClass('is-invalid');
                }
            }
            $('#participant_type, #teams').on('change', validateFootballTeams);
            $('#category').on('change', validateFootballTeams);
        });
    </script>
@endsection
