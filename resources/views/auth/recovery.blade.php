@extends('layouts.app')

@section('content')

<div class="auth-headline">
    <h1>Password recovery</h1>
</div>

<div class="form-container">
<form method="POST" action="{{ route('reset.send') }}">
    {{ csrf_field() }}

    <label for="email">E-mail address</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
    @if ($errors->has('email'))
        <sdiv class="error">
          {{ $errors->first('email') }}
        </div>
    @endif

    <div class="login-button-container">
        <button class="auth-button" type="submit">Request</button>
    </div>

    @if (session('success'))
        <p class="success">
            {{ session('success') }}
        </p>
    @endif
</form>
</div>
@endsection