@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auction.css') }}"> 
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reportAuction.css') }}">
@endpush

@stack('scripts')
<script type="text/javascript"></script>
<script type="text/javascript" src={{ url('js/reportauction.js') }} defer></script>

<script>function openDeleteModal() {
        document.getElementById('deleteModal').style.display = 'flex';
    }</script>

<script>function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }</script>

<script>function openCancelModal(){
        document.getElementById('cancelModal').style.display = 'flex';
    }</script>

<script>function closeCancelModal() {
        document.getElementById('cancelModal').style.display = 'none';
    }</script>

<script>function openBidModal() {
document.getElementById('bidModal').style.display = 'flex';
}</script>

<script>function closeBidModal() {
document.getElementById('bidModal').style.display = 'none';
}</script>




@section('content')
<section class="auctionPage">
        <div class="item-image" style="position: relative;"> 
            <div class="heart-container">
                <i 
                    id="heart-icon" 
                    class="fa fa-heart heart-icon {{ auth()->check() && DB::table('wishlists')->where('user_id', auth()->id())->where('auction_id', $auction->id)->exists() ? 'active' : '' }}" 
                    onclick="toggleHeart({{ $auction->id }})"
                ></i>
            </div>

            @if (Storage::disk('public')->exists($auction->imagepath))
                <img id="image" src="{{ asset('storage/' . $auction->imagepath) }}" alt="Item Image">
            @else
                <img id="image" src="{{ url($auction->imagepath) }}" alt="Item Image">
            @endif
        </div>
        
        <div class="item-info">
            <div class="item-name">
                <h2>{{ $auction->itemname ?? 'Unknown Item' }}</h2>
            </div>
            <div class="item-price">
                <h3>{{ $auction->currentprice ?? '0.0' }}€</h3>
            </div>
            <div class="item-description">
                <p>{{ $auction->description ?? 'No description available.' }}</p>
            </div>
            
            <div class="seller-info">
                <div class="seller">
                    <a href="{{ route('seller.profile', ['id' => $seller->id]) }}">
                        @if (Storage::disk('public')->exists($seller->imagepath))
                            <img id="seller-image" src="{{ asset('storage/' . $seller->imagepath) }}" alt="Seller Image">
                        @else
                            <img id="seller-image" src="{{ url('images/image.png') }}" alt="Seller Image">
                        @endif
                    </a>
                </div>
                <div class="info">
                    <p>Item auctioned by:</p>
                    <div class="username">
                        <p>{{ $seller->username ?? 'Unknown Seller' }} ({{ $seller->number_of_auctions ?? '0' }} items sold)</p>
                    </div>
                    <p>{{ $seller->avgrating ?? '0' }} of rating</p>
                </div>
            </div>    
            <div class="bid-section">
                @if (auth()->check() && auth()->user()->isadmin)
                    <button class="large-button" onclick="openDeleteModal()">Delete Auction</button>
                @elseif ($auction->status == "active")
                    @if (auth()->check()&& auth()->user()->id!= $seller->id)
                        <button class="large-button" onclick="openBidModal()">Bid Now</button>
                        <button id="report-button" class="large-button" data-auction-id="{{ $auction->id }}" data-auction-name="{{ $auction->itemName }}">Report Auction</button>
                    @elseif (auth()->check()&& auth()->user()->id== $seller->id)
                        @if($bidsCount == 0)
                        <button class="large-button" onclick="openCancelModal()">Cancel Auction</button>
                        @else
                        <button class="large-button">Not available to bid</button>
                        @endif
                    @else
                        <button class="large-button" onclick="redirectToRegister()">Bid Now</button>
                    @endif
                @else
                    <button class="large-button">Not available</button>
                @endif
                </div>
                <div class="bid-info">
                    <div class= "bid-left-side">
                        <p>{{ $bidsCount ?? '0' }} bids, {{ $auction->watchers_count ?? '0' }} watchers</p>
                        <p>Ends in {{ $auction->deadline ?? 'Unknown Deadline' }}</p>
                    </div>
                    <div class= "bid-right-side">
                        <p></p>
                        <p> Created in {{ $auction->creationdate ?? 'Unknown Date' }}</p>
                    </div>
                </div>    
            </div>                  
        </div>
    @stack('scripts')
</section>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this auction? This action is irreversible.</p>
        <div class="modal-content-buttons">
            <form action="{{ route('DeleteAuction', $auction->id) }}" method="POST">
                @csrf
                @method('DELETE') 
                <button class="delete-button" type="submit">Delete Auction</button>
            </form>
            <button class="delete-button" onclick="closeDeleteModal()">Cancel</button>
        </div>
    </div>
</div>

<div id="cancelModal" class="modal">
    <div class="modal-content">
        <h2>Confirm Cancelation</h2>
        <p>Are you sure you want to cancel your auction? This action is irreversible.</p>
        <div class="modal-content-buttons">
            <form action="{{ route('updateAuctionStatus', $auction->id) }}" method="POST">
                @csrf
                @method('PATCH') 
                <input type="hidden" name="status" value="cancelled">
                <button class="delete-button" type="submit">Cancel Auction</button>
            </form>
            <button class="delete-button" onclick="closeCancelModal()">Cancel</button>
        </div>
    </div>
</div>

<div id="bidModal" class="modal">
    <div class="modal-content">
        <h2>Place Your Bid</h2>
            <form action="{{ route('placeBid') }}" method="POST">
                @csrf
                <input type="hidden" name="auction_id" value="{{ $auction->id }}">
                <div>
                    <label for="bid_value">Your Bid (€):</label>
                    <input type="number" step="0.01" id="bid_value" name="bid_value" required>
                    <label class="min-incr">Minimum increment: {{$auction->increment}}€</label>
                </div>
                <div class="modal-content-buttons">
                    <button class="modal-button" type="submit">Submit Bid</button>
                    <button class="modal-button" onclick="closeBidModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="reportform" class="reportform">
    <div class="reportform-content">
        <span id="closePopup" class="close">&times;</span>
        <h2>Report Auction</h2>
        <form action="{{ route('reports.store') }}" method="POST">
            @csrf
            <input type="hidden" name="reported_id" value="{{ $auction->id }}">
            <input type="hidden" name="reported_name" value="{{ $auction->itemname }}">
            <input type="hidden" name="type" value="auction">
            
            <div>
            <label>
                <input type="checkbox" name="reasons[]" value="Fradulent Behavior">
                Fradulent Behavior
            </label>
            </div>

            <div>
                <label>
                    <input type="checkbox" name="reasons[]" value="Prohibited or Illegal Items">
                    Prohibited or Illegal Items
                </label>
            </div>

            <div>
                <label>
                    <input type="checkbox" name="reasons[]" value="Price Manipulation">
                    Price Manipulation
                </label>
            </div>

            <div>
                <label>
                    <input type="checkbox" name="reasons[]" value="Inappropriate Listings">
                    Inappropriate Listings
                </label>
            </div>
            <div>
                <label>
                    <input type="checkbox" name="reasons[]" value="Other">
                    Other
                </label>
            </div>

            <label for="description">Additional Information:</label>
            <textarea id="description" name="description"></textarea>

            <div class="report-button-content">
                <button class="modal-button" type="submit">Submit</button>
            </div>
        </form>
    </div>
</div>


<script>
    function redirectToRegister() {
        window.location.href = "{{ route('login') }}";
    }
</script>

<script>
    async function toggleHeart(auctionId) {
        const heartIcon = document.getElementById('heart-icon');
        console.log(`my auctionId is ${auctionId}`);

        try {
            const response = await fetch(`/wishlist/toggle/${auctionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            });

            const result = await response.json();

            if (response.ok) {
            
                if (heartIcon.classList.contains('active')) {
                    heartIcon.classList.remove('active'); 
                } else {
                    heartIcon.classList.add('active'); 
                }
            } else {
                console.error(result.message || 'An error occurred while updating the wishlist.');
            }
        } catch (error) {
            console.error('An error occurred:', error);
        }
    }

    document.getElementById('heart-icon').onclick = () => toggleHeart({{$auction->id}});
</script>



@if(session('error'))
    <script>
        alert('{{ session('error') }}');
        openBidModal(); 
    </script>
@endif

@endsection
