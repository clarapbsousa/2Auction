@extends('layouts.app')

@push('styles')
    <link href="{{ url('css/auctionIndex.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link href="{{ url('css/reportAuction.css') }}" rel="stylesheet">
    <link href="{{ url('css/rate.css') }}" rel="stylesheet">
    <link href="{{ url('css/admin.css') }}" rel="stylesheet">
    
@endpush

@stack('scripts')
<script type="text/javascript"></script>
<script type="text/javascript" src={{ url('js/rate.js') }} defer></script>
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
<script>
document.getElementById('rateForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const rate = document.getElementById('rate').value;

        try {
            const response = await fetch('{{ route('users.rate', $seller->id) }}', {
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

<script>


</script>


@section('content')
<div class="seller-profile">
    <h1 class="title text-center">User Profile</h1>
    <div class="seller-details">
        <img class="seller-details-img" src="{{ Storage::disk('public')->exists($seller->imagepath) ? asset('storage/' . $seller->imagepath) : url('images/image.png') }}" alt="Seller Image">
        <h2>{{ $seller->username }}</h2>
        <p>Number of auctions: {{ $seller->number_of_auctions ?? '0' }}</p>
        <p>Average rating: <strong id="seller-avgrating">{{ number_format($seller->avgrating, 1) }}</strong>/5</p>
        <div class="p-2 pb-4 d-flex gap-3 flex-wrap justify-content-center align-center">
            @auth
                @if(!auth()->user()->isadmin)
                    @php
                        $isBlocked = \App\Models\UserBlock::where('blocker_id', auth()->id())
                                                        ->where('blocked_id', $seller->id)
                                                        ->exists();
                    @endphp

                    <form style="margin:0; padding:0;" method="POST" action="{{ $isBlocked ? route('user.unblock', $seller->id) : route('user.block', $seller->id) }}">
                        @csrf
                        <div class="bid">
                            <button type="submit" class="button-profile-picture">{{ $isBlocked ? 'Unblock User' : 'Block User' }}</button>
                        </div>
                    </form>
                @endif
            @endauth
            @auth
                @if(!auth()->user()->isadmin)
                <div class="bid">
                    <button class="button-profile-picture" id="rateButton" data-seller-id="{{ $seller->id }}" data-seller-name="{{ $seller->username }}">Rate User</button>
                </div>
                @endif
            @endauth
            @auth
                @if(!auth()->user()->isadmin || auth()->user() !== $seller->id)
                <div class="bid">
                    <button class="button-profile-picture" id="report-button" data-seller-id="{{ $seller->id }}" data-seller-name="{{ $seller->username }}">Report User</button>
                </div>
                @endif
            @endauth
        </div>
        <h2>{{ $seller->username }}'s auctions</h2>
        @if($seller->auctions->count())
            <div class="align-center justify-content-center text-left">
                @foreach($seller->auctions as $auction)
                    <a href="{{ route('auction.show', ['id' => $auction->id]) }}" class="auction-link">
                        <div class="auction-container d-flex align-center text-left">
                            @if (Storage::disk('public')->exists($auction->imagepath))
                                <img src="{{ asset('storage/' . $auction->imagepath) }}" alt="Auction Image">
                            @else
                                <img src="{{ url($auction->imagepath) }}" alt="Auction Image">
                            @endif
                            <div>
                                <div class="auction-info text-left justify-content-left">
                                    <p>{{ $auction->itemname }}</p>
                                    {{ $auction->description }}
                                </div>
                                <div class="deadline">Deadline: {{ $auction->deadline }}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <p class="no-search">No auctions found.</p>
        @endif

        <!-- Botão Block/Unblock -->


        
        @if(session('success'))
            <div id="success-alert" class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div id="error-alert" class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <!--Botão Rate -->

        <div id="ratePopup" class="popup hidden">
            <div class="popup-content">
                <span id="closeRatePopup" class="close">&times;</span>
                <h2>Rate this user</h2>
                <form id="rateForm" action="{{ route('users.rate', $seller->id) }}" method="POST">
                    @csrf
                    <label for="rate">Rate (1-5):</label>
                    <input type="number" id="rate" name="rate" min="1" max="5" required>
                    <button class="modal-button" type="submit">Submit</button>
                </form>
            </div>
        </div>

        <div id="reportform" class="reportform">
            <div class="reportform-content">
                <span id="closePopup" class="close">&times;</span>
                <h2>Report User</h2>
                <form action="{{ route('reports.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="reported_id" value="{{ $seller->id }}">
                    <input type="hidden" name="type" value="user">
                    <div>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Item not as described">
                        Item not as described
                    </label>
                    </div>

                    <div>
                        <label>
                            <input type="checkbox" name="reasons[]" value="Inapropriated Content">
                            Inapropriated Content
                        </label>
                    </div>

                    <div>
                        <label>
                            <input type="checkbox" name="reasons[]" value="Fake product">
                            Fake product
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
@stack('scripts')
@endsection
