@extends('layouts.app')

@push('styles')
    <link href="{{ url('css/auctionIndex.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class = "title">
        <h1>Auctions</h1>
    </div>
    

    @if($auctions->filter(fn($auction) => $auction->status === 'active')->count())
    <div class= two-column-list>
        @foreach($auctions->filter(fn($auction) => $auction->status === 'active') as $auction)
        <a href="{{ route('auction.show', ['id' => $auction->id]) }}" class="auction-link">
            <div class="auction-container">
                @if (Storage::disk('public')->exists($auction->imagepath))
                            <img src="{{ asset('storage/' . $auction->imagepath) }}" alt="Auction Image">
                @else
                            <img src="{{ url($auction->imagepath) }}" alt="Auction Image">
                @endif
                <div>
                    <div class="auction-info">
                        <p>{{ $auction->itemname }} </p>
                        {{ $auction->description }}
                    </div>
                     <div class="deadline">Deadline: {{ $auction->deadline }}</div>
                </div>
             </div>
        </a>
        @endforeach
    </div>
   <div style="margin-top:1em;"class= "pagination">
      {{ $auctions->appends(request()->query())->links() }}
    </div>
    @else
        <p class = "no-search">No auctions found.</p>
    @endif
@endsection
