@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@stack('scripts')
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
@section('content')
<div class = "userlistPage">
    <div class = "statistics">
        <div class="subtitle">
            <h1> Users </h1> 
        </div>
        <div class> Banned {{ $users->filter(fn($user) => $user->isbanned)->count() }}</div>
        <div class> Admins {{ $users->filter(fn($user) => $user->isadmin)->count() }}</div> 
    </div>    
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
@endsection

