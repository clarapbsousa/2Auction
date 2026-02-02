@extends('layouts.app')

@section('content')

<div class="form-container">
  <div class="auth-headline">
      <h1>Sign up to 2Auction</h1>
      <h3>Already a member? <a href="{{ route('login') }}">Sign in</a> </h3>
  </div>
<form id="registration-form" method="POST" action="{{ route('register') }}">
    {{ csrf_field() }}

    <label for="name">Name</label>
    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
    @if ($errors->has('name'))
      <div class="error">
          {{ $errors->first('name') }}
      </span>
    @endif
    
    <label for="email">E-mail address</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required>
    @if ($errors->has('email'))
      <div class="error">
          {{ $errors->first('email') }}
      </span>
    @endif

    <label for="password">Password</label>
    <input id="password" type="password" name="password" required>
    @if ($errors->has('password'))
      <div class="error">
          {{ $errors->first('password') }}
      </span>
    @endif

    <label for="password-confirm">Confirm Password</label>
    <input id="password-confirm" type="password" name="password_confirmation" required>

    <div class="login-button-container">
      <button class="auth-button" type="submit">
        Sign up
      </button>
    </div>
    <div class="divider">
        <hr>
    </div>

    <div class="social-login">
        <a href="{{ route('google.login') }}" class="google-button" style="display: flex; align-items: center; text-decoration: none; font-size:2em">
            <img src="{{ asset('images/google_icon.png') }}" alt="Google Icon" style="width: 1.5em; height: 1.5em;">
        </a>
    </div>
</form>


<script>
  document.getElementById('registration-form').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const userConfirmed = confirm('Are you, at least, 18 years old? You must be at least 18 years old to take part in auctions.');

    if (userConfirmed) {
      this.submit();
    }
  });
</script>

</div>
@endsection