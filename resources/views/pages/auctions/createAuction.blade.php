@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auction.css') }}"> 
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auctionCreate.css') }}">
@endpush

@section('content')
<div class="create-auction-page">
    <h1 class="title">Create a New Auction</h1>
    <form action="{{ route('auction.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="itemname">Item Name</label>
            <input type="text" id="itemname" name="itemname" required value="{{ old('itemname') }}">
        </div>
        <div>
            <label for="startingPrice">Starting Price</label>
            <div style="display: flex; align-items: center;">
                <input type="number" id="startingPrice" name="startingPrice" step="0.01" required value="{{ old('startingPrice') }}">
                <div style="margin-left: 10px;">
                    <input type="checkbox" id="defaultStartingPrice" onclick="setDefaultStartingPrice()">
                    <label for="defaultStartingPrice">Default Value </label>
                </div>
            </div>
        </div>
        <div>
            <label for="increment">Increment</label>
            <input type="number" id="increment" name="increment" step="0.01" value="5.0" required value="{{ old('increment') }}">
        </div>
        <div>
            <label for="deadLine">Deadline</label>
            <input type="datetime-local" id="deadLine" name="deadLine" required value="{{ old('deadLine') }}">
        </div>
        <div>
            <label for="subcategory">Subcategory</label>
            <select id="subcategory" name="subcategory" required>
                @foreach(\App\Models\Subcategory::all() as $subcategory)
                    <option value="{{ $subcategory->id }}" {{ old('subcategory') == $subcategory->id ? 'selected' : '' }}>
                        {{ $subcategory->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description" required>{{ old('description') }}</textarea>
        </div>
        <div class="form-group">
            <label for="imagepath">Item Image</label>
            <input type="file" id="imagepath" name="imagepath" accept="image/*" required>
        </div>

        <div class="buttons-container">
            <button class="medium-button" type="submit">Create Auction</button>
            <button class="medium-button" type="button" onclick="window.location.href='{{ route('profile') }}'">Return</button>
        </div>
    </form>
</div>

@if(session('error'))
    <script>
        alert('{{ session('error') }}');
        window.location.href = '{{ route('auction.create') }}';
    </script>
@endif

<script>
    function setDefaultStartingPrice() {
        const checkbox = document.getElementById('defaultStartingPrice');
        const startingPriceInput = document.getElementById('startingPrice');
        if (checkbox.checked) {
            startingPriceInput.value = "5.00";
        } else {
            startingPriceInput.value = "";
        }
    }
</script>
@endsection
