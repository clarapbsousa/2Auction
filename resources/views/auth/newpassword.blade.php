@extends('layouts.app')

@section('content')

<div class="auth-headline">
    <h1>Set new password</h1>
</div>
<div class="form-container">
    <form method="POST" action="{{ route('reset.newpass') }}">
        {{ csrf_field() }}

        <input type="hidden" name="token" value="{{ request()->get('token') }}">

        <label for="password">New password</label>
        <input id="password" type="password" name="password" required autofocus>
        @if ($errors->has('password'))
            <div class="error">
                {{ $errors->first('password') }}
            </div>
        @endif

        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>
        @if ($errors->has('password_confirmation'))
            <div class="error">
                {{ $errors->first('password_confirmation') }}
            </div>
        @endif

        @if ($errors->has('token'))
            <div class="error">
                {{ $errors->first('token') }}
            </div>
        @endif

        <div class="login-button-container">
            <button class="auth-button" type="submit">Submit</button>
        </div>

        @if (session('success'))
            <p class="success">
                {{ session('success') }}
            </p>
        @endif
    </form>
</div>
@endsection
