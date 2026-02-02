<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
        @stack('styles')
        <link href="{{ url('css/app.css') }}" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://js.pusher.com/7.0/pusher.min.js" defer></script>
        @stack('scripts')
        <script type="text/javascript"></script>
        <script type="text/javascript" src={{ url('js/app.js') }} defer></script>
        <script>
            window.pusherAppKey = "{{ env('PUSHER_APP_KEY') }}";
            window.pusherCluster = "{{ env('PUSHER_APP_CLUSTER') }}";
            window.authUser = "{{auth()->id()}}"
        </script>
        <script type="text/javascript" src={{ url('js/pusher.js') }} defer></script>
        <script src="https://js.stripe.com/v3/"></script>
    </head>
    <body>
        <main>
        <header>
   
            <div class="menu">
                <a class="brand" href="{{ route('home') }}">2Auction</a>
                <form method="GET" action="{{ route('auctions.index') }}">
                    <div class="search-wrapper">
                        <span class="magnifying-glass"><i class="fa fa-search"></i></span>
                        <input type="text" name="q" placeholder="Search for auction..." value="{{ request('q') }}">
                    </div>
                </form>
            </div>
 
            <div class="right-section">
                <a href="{{ route('profile') }}#wishlist" class="icon">
                    <i class="fa fa-heart"></i>
                </a>
                <a href="#" class="icon position-relative" id="bell-icon">
                    <i class="fa fa-bell"></i>
                    <span id="notification-badge" class="position-absolute translate-middle badge rounded-pill" 
                        style="top: 27.5px; left: 20px; font-size: 0.75rem; display: none; background-color: #D04555;">
                        0
                    </span>
                </a>
                <a href="{{ auth()->check() ? route('profile') : route('login') }}" class="profile">
                    @if (auth()->check() && auth()->user()->imagepath && Storage::disk('public')->exists(auth()->user()->imagepath))
                        <img src="{{ asset('storage/' . auth()->user()->imagepath) }}" alt="Profile Picture">
                    @else
                        <img src="{{ url('images/image.png') }}" alt="Profile Picture">
                    @endif
                </a>
                @auth
                    @if(!auth()->user()->isadmin)
                    <div class="balance-box">
                        <a href="{{ route('auction.create') }}" class="balance-amount">
                            Create Auction
                        </a>
                    </div>
                    @endif
                @endauth
            </div>
        </header>
        <div id="notification-box" class="notification-box position-absolute bg-white me-5 rounded-4 shadow-xl" 
            style="width: 20%; height: 25%; min-width: 210px; z-index: 100; right: 30px; display: none; overflow-y: auto;">
            <span id="no-notis" class="text-primary fw-bold" style="color:#767676 !important; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">No new notifications!</span>
        </div>
        <section id="content">
            @yield('content')
        </section>
        </main>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const bellIcon = document.getElementById('bell-icon');
                const notificationBox = document.getElementById('notification-box');

                bellIcon.addEventListener('click', function (event) {
                    event.preventDefault();
                    // Toggle the display of the notification box
                    if (notificationBox.style.display === 'none' || notificationBox.style.display === '') {
                        notificationBox.style.display = 'block';
                    } else {
                        notificationBox.style.display = 'none';
                    }
                });
            });
        </script>
    </body>
</html>
