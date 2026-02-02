@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('js/forgotpasswords.js') }}"></script>
@endpush

@section('content')

<div class="form-container">
    <div class="auth-headline">
        <h1>Sign in to 2Auction</h1>
        <h3>Not a member? <a href="{{ route('register') }}">Sign up</a></h3>
    </div>

    <form method="POST" action="{{ route('login') }}">
        {{ csrf_field() }}

        <label for="email">E-mail address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>
            @if ($errors->has('password'))
                <div class="error">
                    {{ $errors->first('password') }}
                </div>
            @endif

            @if ($errors->has('email'))
            <div class="error">
                {{ $errors->first('email') }}
            </div>
        @endif
        </div>



        <label class="forgot-password">
            <a href="{{ route('reset.form') }}" style="color: #027782;">Forgot password?</a>
        </label>

        <div class="login-button-container">
            <button class="auth-button" type="submit">Sign in</button>
        </div>

        @if (session('success'))
            <p class="success">
                {{ session('success') }}
            </p>
        @endif

        <div class="divider">
            <hr>
        </div>

        <div class="social-login">
            <a href="{{ route('google.login') }}" class="google-button">
                <img src="{{ asset('images/google_icon.png') }}" alt="Google Icon">
            </a>
        </div>
    </form>
</div>
@endsection
