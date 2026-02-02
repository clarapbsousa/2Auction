@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@stack('scripts')
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
    <div class="title">
        <h1>Auctions</h1>
    </div>

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
    {{ $auctions->links() }} 
</div>
@endsection



