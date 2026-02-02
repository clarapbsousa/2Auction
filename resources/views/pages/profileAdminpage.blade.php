@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/homepage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link href="{{ url('css/reportlist.css') }}" rel="stylesheet">
@endpush

@section('content')

<script>
   function openBanModal(id) {
    const modal = document.getElementById(`banModal-${id}`);
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden'; 
}

function closeBanModal(id) {
    const modal = document.getElementById(`banModal-${id}`);
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = 'auto'; 
}

   function openAdminModal(id) {
    const modal = document.getElementById(`adminModal-${id}`);
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden'; 
}

function closeAdminModal(id) {
    const modal = document.getElementById(`adminModal-${id}`);
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = 'auto'; 
}

window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach((modal) => {
        if (event.target === modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = 'auto';
        }
    });
};

window.onkeydown = function(event) {
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach((modal) => {
            if (modal.style.display === 'flex') {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = 'auto';
            }
        });
    }
};

</script>
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
        </div>

        <div class="profile-info">
            <div>
                <h2>{{ $user->name }}</h2>
                <p>{{ '@' . $user->username }}</p>
            </div>
            <!-- Tabs para My Auctions, My Bids e Balance -->
            <div class="user-tabs">
                    <div class="tab-navigation">
                        <button class="tab-button active" onclick="openTab(event, 'users')">User List</button>
                        <button class="tab-button" onclick="openTab(event, 'auctions')">Auction List</button>
                        <a href="{{route("reports.store")}}" style="text-decoration: none;"><button class="tab-button">Report List</button></a>
                    </div>

                    <div id="users" class="tab-content" style="display: block;">
                        @php
                            $users = App\Models\User::where('id', '!=', 0)->paginate(6);
                        @endphp
                        <p>Banned {{ $users->filter(fn($user) => $user->isbanned)->count() }}, Admins {{ $users->filter(fn($user) => $user->isadmin)->count() }}</p>
                        <div class = "userlistPage">
                        <div class = "userlist">
                            <div class="two-column-list">
                            @foreach($users as $u)
                            <div class="user-cards-container">
                                <div class="user-card">
                                    <div class="user-card-image">
                                        @if ($u->imagepath && Storage::disk('public')->exists($u->imagepath))
                                            <img src="{{ asset('storage/' . $u->imagepath) }}" alt="Profile Picture">
                                        @else
                                            <img src="{{ url('images/image.png') }}" alt="Profile Picture">
                                        @endif
                                    </div>
                                    <div class="user-card-content">
                                        <div class="user-card-text">
                                            <h1>{{$u->name}}</h1>
                                            <h2>{{$u->username}}</h2>
                                        </div>
                                        <div class="user-card-buttons-container">
                                            <button class="medium-button" onclick="openBanModal({{ $u->id }})">
                                                @if ($u->isbanned)
                                                    Unban User
                                                @else
                                                    Ban User
                                                @endif
                                            </button>

                                            <button class="medium-button" onclick="openAdminModal({{ $u->id }})">
                                                @if ($u->isadmin)
                                                    Demote Admin
                                                @else
                                                    Promote to Admin
                                                @endif
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="banModal-{{ $u->id }}" class="modal">
                                <div class="modal-content">
                                    @if ($u->isbanned)
                                        <h2>Confirm Unban</h2>
                                        <p>Are you sure you want to unban this user?</p>
                                    @else
                                        <h2>Confirm Ban</h2>
                                        <p>Are you sure you want to ban this user?</p>
                                    @endif
                                    <div class="modal-content-buttons">
                                        <form action="{{ route('updateban', $u->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button class="delete-button" type="submit">
                                                @if ($u->isbanned)
                                                    Unban User
                                                @else
                                                    Ban User
                                                @endif
                                            </button>
                                        </form>
                                        <button class="delete-button" type="button" onclick="closeBanModal({{ $u->id }})">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            <div id="adminModal-{{ $u->id }}" class="modal">
                                <div class="modal-content">
                                    @if ($u->isadmin)
                                        <h2>Confirm Demotion</h2>
                                        <p>Are you sure you want to demote this admin?</p>
                                    @else
                                        <h2>Confirm Promotion</h2>
                                        <p>Are you sure you want to promote this user?</p>
                                        <p>Both will have the same privileges.</p>
                                    @endif
                                    <div class="modal-content-buttons">
                                        <form action="{{ route('updateadmin', $u->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button class="delete-button" type="submit">
                                                @if ($u->isadmin)
                                                    Demote
                                                @else
                                                    Promote 
                                                @endif
                                            </button>
                                        </form>
                                        <button class="delete-button" type="button" onclick="closeAdminModal({{ $u->id }})">Cancel</button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            </div>
                            <div class= "pagination">
                            {{ $users->links()}} 
                            </div>
                        </div>    
                        </div>
                    </div>

                    <div id="auctions" class="tab-content" style="display: none;">
                    <script>
                        function openDeleteModal(id) {
                            const modal = document.getElementById(`deleteModal-${id}`);
                            modal.style.display = 'flex';
                            modal.setAttribute('aria-hidden', 'false');
                            document.body.style.overflow = 'hidden'; 
                        }

                        function closeDeleteModal(id) {
                            const modal = document.getElementById(`deleteModal-${id}`);
                            modal.style.display = 'none';
                            modal.setAttribute('aria-hidden', 'true');
                            document.body.style.overflow = 'auto'; 
                        }
                        </script>
                        @php
                            $auctions = App\Models\Auction::paginate(6);
                            $statuses = DB::select("SELECT unnest(enum_range(NULL::AuctionStatus)) AS status");
                            $statuses = array_map(fn($s) => $s->status, $statuses);
                        @endphp
                            <div class="two-column-list">
                                @foreach ($auctions as $a)
                                    <div class="auction-card">
                                        <div class="auction-card-image">
                                            <a href="{{ route('auction.show', ['id' => $a->id]) }}" class="auction-link">
                                                @if (Storage::disk('public')->exists($a->imagepath))
                                                    <img src="{{ asset('storage/' . $a->imagepath) }}" alt="Auction Image">
                                                @else
                                                    <img src="{{ url($a->imagepath) }}" alt="Auction Image">
                                                @endif
                                            </a>
                                        </div>
                                        <div class="auction-card-text">
                                            <h1>{{$a->itemname}}</h1>
                                            <p>{{$a->currentprice}}€ | started at {{$a->startingprice}}€</p>
                                            <p>ends at {{$a->deadline}}</p>
                                            <form method="POST" action="{{ route('updatestatus', $a->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <select class="selector" name="status" onchange="this.form.submit()">
                                                    @foreach ($statuses as $status)
                                                        <option value="{{ $status }}" {{ $status === $a->status ? 'selected' : '' }}>
                                                            {{ ucfirst($status) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </form>

                                            <div class="auction-card-buttons-container">
                                                <button class="medium-button" onclick="openDeleteModal({{ $a->id }})">Delete Auction</button>

                                                <a href="{{route('bidlist', $a->id)}}">
                                                    <button class="medium-button" type="submit">
                                                        Bid List
                                                    </button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>  

                                    
                                    <div id="deleteModal-{{ $a->id }}" class="modal">
                                        <div class="modal-content">
                                            <h2>Confirm Deletion</h2>
                                            <p>Are you sure you want to delete this auction? This action is irreversible.</p>
                                            <div class="modal-content-buttons">
                                                <form action="{{ route('DeleteAuction', $a->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE') 
                                                    <button class="delete-button" type="submit">Delete Auction</button>
                                                </form>
                                                <button class="delete-button" type="button" onclick="closeDeleteModal({{ $a->id }})">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="pagination">
                                {{ $auctions->appends(['tab' => 'auctions'])->links() }}
                            </div>
                    </div>

                    <div id="reports" class="tab-content" style="display: none;">
                        <p>Balance: <strong>{{ $user->balance }}</strong>€</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script>
function openTab(evt, tabName) {
    const tabContents = document.querySelectorAll('.tab-content');
    const tabButtons = document.querySelectorAll('.tab-button');

    // Hide all tab contents
    tabContents.forEach(tab => tab.style.display = "none");

    // Remove "active" class from all buttons
    tabButtons.forEach(btn => btn.classList.remove("active"));

    // Show the selected tab and add "active" class to its button
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("active");

    // Update the URL without reloading the page
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState(null, '', url);
}

// Check the URL for the active tab when the page loads
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'users'; // Default to 'users' tab

    // Simulate a click to open the active tab
    const activeButton = document.querySelector(`.tab-button[onclick*="${activeTab}"]`);
    if (activeButton) {
        activeButton.click();
    }
});


    </script>
        <!--
        <div class="profile-info">
            <div>
                <h2>{{ $user->name }}</h2>
                <p>{{ '@' . $user->username }}</p>
            </div>
            <hr class="profile-stats-divider">

            <div class="numbers-division" id="stats">
                <p>{{ $users }} <strong>Users</strong></p>
                <p>{{ $bannedusers }} <strong>Banned Users</strong></p>
                <p>{{ $activeauctions }} <strong>Active Auctions</strong></p>
            </div>

            <hr class="profile-stats-divider">
            <div class="buttons-rating-container" id="profile">
                <button onclick="window.location.href='{{ route('userlist') }}'">User List</button>
                <button onclick="window.location.href='{{ route('auctionlist') }}'">Auction List</button>
                <button onclick="window.location.href='{{ route('reports.store') }}'">Report List</button>
            </div>
        </div>
        -->
    </div>
</div>
@endsection
