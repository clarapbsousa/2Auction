@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/homepage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link href="{{ url('css/reportlist.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="profile-page">
    <div class="profile-container">
        <div class="profile-picture">
                @if (auth()->check() && auth()->user()->imagepath && Storage::disk('public')->exists(auth()->user()->imagepath))
                    <div class="big-picture">
                        <img class="big-picture" src="{{ asset('storage/' . auth()->user()->imagepath) }}" alt="Profile Picture">
                    </div>
                @else
                    <div class="big-picture">
                        <img class="big-picture" src="{{ url('images/image.png') }}" alt="Profile Picture">
                    </div>
                @endif
            
                @if (Auth::check())
                    <form action="{{ route('logout') }}" method="GET">
                        @csrf
                        <button class="button-profile-picture" type="submit">Logout</button>
                    </form>
                @endif
                <a href="{{ route('profile.edit') }}">
                    <button class="button-profile-picture">Edit Profile</button>
                </a>
                <button type="submit" class="button-profile-picture" onclick="openDeleteModal()">Delete Account</button>
        </div>

        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <h2>Confirm Deletion</h2>
                <p>Are you sure you want to delete your account? This action is irreversible.</p>
                <div class="modal-content-buttons">
                    <form action="{{ route('account.delete', ['id' => auth()->id()]) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="delete-button">Delete Account</button>
                    </form>
                    <button class="delete-button" onclick="closeDeleteModal()">Cancel</button>
                </div>
            </div>
        </div>

        <div class="profile-info">
            <div>
                <h2>{{ $user->name }}</h2>
                <p>{{ '@' . $user->username }}</p>
                <div class="rating-container">
                    <p>Rating: <strong id="avgrating">{{ number_format($user->avgrating, 1) }}</strong></p>
                </div>
            </div>
            <!-- Tabs para My Auctions, My Bids e Balance -->
            <div class="user-tabs">
                <div class="tab-navigation">
                    <button class="tab-button active" onclick="openTab(event, 'auctions')">My Auctions</button>
                    <button class="tab-button" onclick="openTab(event, 'bids')">My Bids</button>
                    <button class="tab-button" onclick="openTab(event, 'balance')">Balance</button>
                    <button class="tab-button" onclick="openTab(event, 'wishlist')">Wishlist</button>
                </div>
                    <div id="auctions" class="tab-content" style="display: block;">
                        <p>{{ $user->number_of_auctions}} Auctions</p>

                        @php
                            $myAuctions = \App\Models\Auction::where('seller', auth()->id())->get();
                        @endphp

                        @if ($myAuctions->isEmpty())
                            <p>Não tem nenhuma auction criada.</p>
                        @else
                            <div class="auctions">
                                @foreach ($myAuctions as $auction)
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
                        @endif
                    </div>

                   <div id="bids" class="tab-content" style="display: none;">
                        <p>{{ $user->number_of_bids }} Bids</p>
                        @php
                            $bids = App\Models\Bid::where('bidder', $user->id)->orderBy('date', 'desc')->get();
                        @endphp
                            <table class="simple-table">
                                <thead>
                                    <tr>
                                        <th>Auction</th>
                                        <th>Bid Value</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bids as $b)
                                        <tr>
                                            @php
                                                $auction = \App\Models\Auction::find($b->auctionid);
                                            @endphp
                                            <td><a style="text-decoration: none; color: #027782; "href="{{route('auction.show', ['id' => $auction->id])}}">{{ $auction->itemname }}</a></td>
                                            <td>{{ $b->value }}</td>
                                            <td>{{ $b->date }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="no-reports">No Bids found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            
                            <div class="pagination-links">
                            </div>
                    </div>

                    </div>

                    <div id="balance" class="tab-content" style="display: none;">
                        <p>Balance: <strong>{{ $user->balance }}</strong>€</p>
                        <form action="{{ route('stripe.checkout') }}" method="POST" style="display: flex; align-items: flex-start; gap: 10px;">
                            <button 
                                type="submit" 
                                class="btn-primary" 
                                style="padding: 5px 10px; background-color: #027782; color: white; border-radius: 5px; border: none; cursor: pointer;">
                                Add Money
                            </button>
                            @csrf
                            <input 
                                type="number" 
                                name="amount" 
                                placeholder="Insert a Value" 
                                min="1" 
                                required 
                                style="padding: 5px; border: 1px solid #ccc; border-radius: 5px; width: 8em!important;">
                        </form>
                    </div>

                    <div id="wishlist" class="tab-content" style="display: none;">
                        <p>{{ $user->auctions_followed}} Followed Auctions</p>
                            <div class="\">
                            @php
                                $wishlistedAuctions = \App\Models\Auction::whereIn('id', \App\Models\Wishlist::where('user_id', auth()->id())->pluck('auction_id'))->get();
                            @endphp

                            @if ($wishlistedAuctions->isEmpty())
                                <p>A sua wishlist está vazia.</p>
                            @else
                                <div class="auctions">
                                    @foreach ($wishlistedAuctions as $auction)
                                        <a href="{{ route('auction.show', ['id' => $auction->id]) }}" class="home-auction">
                                            @if (Storage::disk('public')->exists($auction->imagepath))
                                                <img class="home-auction-image" src="{{ asset('storage/' . $auction->imagepath) }}" alt="Item Image">
                                            @else
                                                <img class="home-auction-image" src="{{ url($auction->imagepath) }}" alt="Item Image">
                                            @endif
                                            <div class="home-auction-price">{{ $auction->currentprice }}€</div>
                                            <div class="home-auction-current-bid">current bid</div>
                                            <div class="home-auction-text">{{ $auction->itemname }}</div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <script>
        function openTab(evt, tabName) {
            const tabContents = document.querySelectorAll('.tab-content');
            const tabButtons = document.querySelectorAll('.tab-button');

            tabContents.forEach(tab => tab.style.display = "none");

            tabButtons.forEach(btn => btn.classList.remove("active"));

            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.classList.add("active");
        }

    </script>


    <script>
     document.getElementById('rateForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const rate = document.getElementById('rate').value;

        try {
            const response = await fetch('{{ route('users.rate', $user->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ rate: rate })
            });

            if (!response.ok) {
                throw new Error('Failed to submit the rating.');
            }

            const data = await response.json();

            // Atualiza o valor de avgrating na página
            document.getElementById('avgrating').textContent = parseFloat(data.newAvgRate).toFixed(1);
            alert(data.message);
        } catch (error) {
            alert('Failed to submit the rating. Please try again.');
            console.error(error);
        }
    });
    </script>
</div>
@endsection

@if(session('error'))
    <script>
        alert('{{ session('error') }}');
    </script>
@endif

<script>

function openDeleteModal(){
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    if (window.location.hash === '#wishlist') {
        const tabButton = document.querySelector('button[onclick="openTab(event, \'wishlist\')"]');
        if (tabButton) {
            tabButton.click();
        }
    }
});

</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const hash = window.location.hash;

    if (hash) {
        const tabButton = document.querySelector(`button[onclick="openTab(event, '${hash.substring(1)}')"]`);
        if (tabButton) {
            tabButton.click();
        }
    }
});
</script>

<script>
function openDeleteModal() {
    const modal = document.getElementById(`deleteModal`);
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden'; 
}

function closeDeleteModal() {
    const modal = document.getElementById(`deleteModal`);
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = 'auto'; 
}
</script>