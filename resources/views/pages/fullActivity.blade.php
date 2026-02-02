@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auction.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"></script>
@endpush

@section('content')
    <div class="container mt-4">
        <h1>Full Activity</h1>
        <p>Below is the list of all your activities (bids and auctions):</p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Details</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities as $activity)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $activity->type }}</td>
                    <td>{{ $activity->itemname }}</td>
                    <td>{{ $activity->details }}</td>
                    <td>{{ $activity->date }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('profile') }}" class="btn btn-secondary">Back to Profile</a>
    </div>
@endsection
