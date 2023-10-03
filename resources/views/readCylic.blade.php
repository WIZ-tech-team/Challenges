@extends('layouts.dashboard')
@section('content')
<form action="" method="post" id="cylicForm">
    @csrf

    <div class="form-group">
        <label for="citySelect">Select City:</label>
        <select id="citySelect" class="form-control" name="citySelect">
            @foreach ($city as $cityOption)
                <option value="{{ $cityOption->id }}">{{ $cityOption->name }}</option>
            @endforeach
        </select>
    </div>

    <div id="teamsDiv">
        <!-- Teams will be displayed here -->
    </div>

</form>

<!-- JavaScript code goes here -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // JavaScript code for handling city selection and updating the form action
    $(document).ready(function () {
        $('#citySelect').on('change', function () {
            var selectedCityId = $(this).val();
            updateFormAction(selectedCityId);
        });

        function updateFormAction(cityId) {
            var form = $('#cylicForm');
            var newAction = "{{ route('loadTeams', ['cityId' => '']) }}" + cityId;
            form.attr('action', newAction);
        }
    });
</script>
@endsection
