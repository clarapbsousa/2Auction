@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auction.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"></script>
@endpush
@section('content')
    <div class="container mt-4">
        <h1>Active Auctions</h1>
        <p>Below is the list of all auctions you are actively bidding on:</p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Auction Name</th>
                    <th>Current Bid</th>
                    <th>Deadline</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activeAuctions as $auction)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $auction->itemname }}</td>
                    <td>{{ $auction->currentprice }}</td>
                    <td>{{ $auction->deadline }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('profile') }}" class="btn btn-secondary">Back to Profile</a>
    </div>

@endsection
