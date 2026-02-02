@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/homepage.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/homepage.js') }}"></script>
    <script>
    setTimeout(() => {
        const successAlert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');

        if (successAlert) {
            successAlert.style.transition = 'opacity 0.5s';
            successAlert.style.opacity = 0;

            // Remover do DOM após a transição
            setTimeout(() => successAlert.remove(), 300);
        }

        if (errorAlert) {
            errorAlert.style.transition = 'opacity 0.5s';
            errorAlert.style.opacity = 0;

            // Remover do DOM após a transição
            setTimeout(() => errorAlert.remove(), 300);
        }
    }, 3000);
</script>
@endpush
    
@section('content')
<!-- Categories Row -->
<div class="categories-row-container">
    <div class="categories-row">
        @foreach ($categories as $category)
            <a href="{{ route('auctions.index', ['category' => $category->id, 'subcategory' => ""]) }}" class="category-link">
                {{ $category->name }}
            </a>
        @endforeach
        <a href="{{ route('contacts') }}" class="category-link">Contacts</a>
        <a href="{{ route('about') }}" class="category-link">About</a>
        <a href="{{ route('faq') }}" class="category-link">FAQ</a>
    </div>
</div>

<div class="carousel">
    <div class="carousel-content">
        <h1>Shop what’s trending. Sell what’s extra.</h1>
        <h3>Whether you want to buy or sell, 2Auction is the place to bid.</h3>
    </div>
    <div class="carousel-content">
        <h1>Find exclusive deals. Bid now!</h1>
        <h3>Explore the best auctions available on 2Auction.</h3>
    </div>
    <div class="carousel-content">
        <h1>Want to sell? It’s easy!</h1>
        <h3>List your items and start bidding with 2Auction.</h3>
    </div>

    <button class="carousel-arrow left" onclick="moveCarousel('left')">&#10094;</button>
    <button class="carousel-arrow right" onclick="moveCarousel('right')">&#10095;</button>
</div>


<div class="home-auctions-container">
    <div class="home-auctions-title">
        Home Auctions
    </div>
        <div class="auctions">
            @foreach ($auctions as $auction)
            <a href="{{ route('auction.show',['id'=>$auction->id]) }}"class="home-auction">
                @if (Storage::disk('public')->exists($auction->imagepath))
                        <img class="home-auction-image" src="{{ asset('storage/' . $auction->imagepath) }}" alt="Item Image">
                @else
                        <img class="home-auction-image" src="{{ url($auction->imagepath) }}" alt="Item Image">
                @endif
                <div class="home-auction-price">{{$auction->currentprice}}€</div>
                <div class="home-auction-current-bid">current bid</div>
                <div class="home-auction-text">{{$auction->itemname}}</div>
            </a>
            @endforeach
        </div>
</div>

@if($homepageCategory)
<div class="home-categories-container">
    <div class="home-categories-title">
        Immerse yourself in the world of {{$homepageCategory->name}}!
    </div>
        <div class="categories">
            @foreach($homepageCategory->subcategories as $subcategory)
                <a href="{{ route('auctions.index', ['category' => $homepageCategory->id, 'subcategory' => $subcategory->id])}}" class="home-category">
                        <img src="{{$subcategory->imagepath}}">
                <div class="home-category-text">{{$subcategory->name}}</div>
                </a>
            @endforeach
        </div>
</div>

@if(!$wishlistedAuctions->isEmpty())
    <div class="home-auctions-container">
        <div class="home-auctions-title">
            Followed Auctions
        </div>
            <div class="auctions">
                @foreach ($wishlistedAuctions as $auction)
                <a href="{{ route('auction.show',['id'=>$auction->id]) }}"class="home-auction">
                    @if (Storage::disk('public')->exists($auction->imagepath))
                            <img class="home-auction-image" src="{{ asset('storage/' . $auction->imagepath) }}" alt="Item Image">
                    @else
                            <img class="home-auction-image" src="{{ url($auction->imagepath) }}" alt="Item Image">
                    @endif
                    <div class="home-auction-price">{{$auction->currentprice}}€</div>
                    <div class="home-auction-current-bid">current bid</div>
                    <div class="home-auction-text">{{$auction->itemname}}</div>
                </a>
                @endforeach
            </div>
    </div>
@endif

@endif
@endsection

@if(session('success'))
    <script>
        alert('{{ session('success') }}');
    </script>
@endif