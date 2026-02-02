@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reportlist.css') }}">

@endpush

@section('content')
<div class="title">
    <h1>Bids</h1> 
</div>

<script>
    function confirmDelete(button) {
        if (confirm("Are you sure you want to delete this auction?")) {
            button.closest('form').submit();
        }
    }
</script>

<div class="reports-container">
    <div class="tab-content">
        <div class="tab-pane fade {{ request('type') == 'auction' || !request('type') ? 'show active' : '' }}" id="auction" role="tabpanel" aria-labelledby="auction-tab">

        <table class="simple-table">
            <thead>
                <tr>
                    <th>Bid ID</th>
                    <th>Username</th>
                    <th>Bid Value</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bids as $b)
                    <tr>
                        <td>{{ $b->id }}</td>
                        <td>{{ $b->user->username ?? 'N/A' }}</td>
                        <td>{{ $b->value }}</td>
                        <td>{{ $b->date }}</td>
                        <td>
                            <form action="{{ route('deletebid', ['id' => $b->id, 'aid' => $auction]) }}" method="POST" class="inline-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn delete-btn" onclick="confirmDelete(this)">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="no-reports">No bids found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    
        </div>
    </div>
</div>

@endsection