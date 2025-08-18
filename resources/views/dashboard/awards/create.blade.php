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
        <h2>Create Award</h2>
        <form action="{{ route('awards.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="challenge_id" class="form-label">Challenge</label>
                <select name="challenge_id" id="challenge_id" class="form-select" required>
                    @foreach ($challenges as $challenge)
                        <option value="{{ $challenge->id }}">{{ $challenge->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Award Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="rank" class="form-label">For Rank</label>
                <input type="number" name="rank" id="rank" class="form-control" min="1" required>
            </div>

            <div class="mb-3">
                <label for="details" class="form-label">Details</label>
                <textarea name="details" id="details" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label for="products">Products</label>
                <select name="products[]" id="products" class="form-control tags-input" multiple>
                    @foreach ($storeProducts as $product)
                        <option value="{{ $product->id }}"
                            {{ collect(old('products'))->contains($product->id) ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Add products as tags or select existing ones.</small>
            </div>

            <button type="submit" class="btn btn-primary">Create Award</button>
        </form>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endpush

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(function() {
            function toggleCategoryFields() {
                var cat = $('#category').val();
                $('#football-fields, #football-teams').hide();
                $('#running-fields, #running-teams').hide();
                if (cat === 'football') {
                    $('#football-fields, #football-teams').show();
                } else if (cat === 'running') {
                    $('#running-fields, #running-teams').show();
                }
            }
            $('#category').on('change', toggleCategoryFields);
            toggleCategoryFields();

            // Initialize select2 for tags input
            $('.tags-input').select2({
                tags: true,
                tokenSeparators: [','],
                width: '100%'
            });
        });
    </script>
@endsection
