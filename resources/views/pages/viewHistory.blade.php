@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auction.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
@endpush

@section('content')
    <div class="container mt-4">
        <!-- Conteúdo principal da página -->
        <div>
            <h1>History</h1>
            <p>Welcome to the history page. Select one of the options above to proceed.</p>
        </div>
        <div class="d-flex justify-content-start mb-3">
            <!-- Botão View Full Activity -->
            <a href="{{ route('full-activity') }}" class="btn btn-primary me-2">View Full Activity</a>
            <br>
            <!-- Botão View Active Auctions -->
            <a href="{{ route('active-auctions') }}" class="btn btn-secondary">My Bids in Active Auctions</a>
            <br>
            <a href="{{ route('my-active-auctions') }}" class="btn btn-secondary">View My Active Auctions</a>

        </div>
    </div>

@endsection