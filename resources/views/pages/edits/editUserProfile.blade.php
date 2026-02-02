@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/editprofile.css') }}">
@endpush

@section('content')

<div class="profile-edit-container">
    <div class="profile-edit-title">
        <h1>Edit profile</h1>
    </div>

    <form method="POST" action="{{ route('profile.update', $user->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <label for="name">Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>

        <label for="username">Username</label>
        <input id="username" type="text" name="username" value="{{ old('username', $user->username) }}" required>
        
        <label for="email">Email</label>
        <input id="email" type="text" name="email" value="{{ old('email', $user->email) }}" required>

        <label for="imagepath">Profile Picture</label>
        <input id="imagepath" type="file" name="imagepath" accept="image/*">

        <div class="profile-edit-button-container">
            <button class="profile-edit-button" type="submit">Save</button>
            <button class="profile-edit-button discard-button" type="button" onclick="window.location.href='{{ route('profile') }}'">Discard</button>
        </div>

        @if (session('success'))
            <p class="profile-edit-success">
                {{ session('success') }}
            </p>
        @endif
    </form>
</div>
@endsection
