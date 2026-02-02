@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auction.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
@endpush

@section('content')
    <div class="container mt-4">
        <h1>My Active Auctions</h1>
        <p>Below is the list of your active auctions, showing the number of bids and current value:</p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Auction Name</th>
                    <th>Current Value</th>
                    <th>Number of Bids</th>
                    <th>Deadline</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myActiveAuctions as $auction)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $auction->itemname }}</td>
                    <td>{{ $auction->currentprice }}</td>
                    <td>{{ $auction->bid_count }}</td>
                    <td>{{ $auction->deadline }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No active auctions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <a href="{{ route('view-history') }}" class="btn btn-secondary">Back to History</a>
    </div>
@endsection
