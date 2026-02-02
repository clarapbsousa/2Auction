<?php

use App\Http\Controllers\BidController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\WishlistController;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BalanceController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\Auth\GoogleController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// M01: Authentication and Individual Profile
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});


// balance
Route::controller(BalanceController::class)->group(function () {
    Route::post('/create-checkout-session', 'createCheckoutSession')->name('stripe.checkout');
    Route::get('/payment-success', 'success')->name('balance.success');
});


Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/contacts', function () {
    return view('pages.contacts');
})->name('contacts');

Route::get('/faq', function () {
    return view('pages.faq');
})->name('faq');



// Home
// Route::get('/categories', [CategoryController::class, 'index'])->name('categories');


Route::get('/profile', [AuctionController::class, 'profile'])->name('profile');


Route::put('/profile/{id}', [ProfileController::class, 'update'])->name('profile.update');

//Edit Profile
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');

Route::get('/admin/userlist', [AdminController::class, 'userList'])->name('userlist');

Route::get('/admin/auctionlist', [AdminController::class, 'auctionList'])->name('auctionlist');
Route::get('/admin/bidlist/{id}', [AdminController::class, 'bidList'])->name('bidlist');

Route::put('/update-ban/{id}', [AdminController::class,'updateBan'])->name('updateban');
Route::put('/update-admin/{id}', [AdminController::class,'updateAdmin'])->name('updateadmin');
Route::put('/update-status/{id}', [AdminController::class,'updateStatus'])->name(name: 'updatestatus');
Route::delete('/delete-auction/{id}', [AdminController::class, 'DeleteAuction'])->name('DeleteAuction');
Route::patch('/cancel-auction/{id}', [AuctionController::class, 'updateStatus'])->name('updateAuctionStatus');
Route::delete('/delete-bid/{id}/{aid}', [AdminController::class,'deleteBid'])->name('deletebid');
Route::get('/auction/create', function () {return view('pages.auctions.createAuction');})->name('auction.create');
Route::post('/auction/store', [AuctionController::class, 'store'])->name('auction.store');


//Profile
Route::get('/profile', function () {
    $user = Auth::user(); 
    if (!$user) {
        return redirect()->route('home')->with('no_permissions', "Sorry, but you can't access this content!");
    }

    if ($user->isadmin) {
        $users = User::all()->count();
        $bannedusers = User::where('isbanned', true)->count();
        $activeauctions = Auction::where('status', 'active')->count();
        return view('pages.profileAdminpage', ['user' => $user, 'users' => $users, 'bannedusers' => $bannedusers, 'activeauctions' => $activeauctions]);
    }
    else {
        return view('pages.profileUserpage', ['user' => $user]);
    }
})->name('profile');

//Browse Auctions
Route::controller(AuctionController::class)->group(function () {
    Route::post('/auction/{id}/follow', [AuctionController::class, 'followAuction'])->middleware('auth');
    Route::delete('/auction/{id}/follow', [AuctionController::class, 'unfollowAuction'])->middleware('auth');
    Route::get('/', 'home')->name('home');
    Route::get('/auctions', 'index')->name('auctions.index');
    Route::get('/auction/{id}', 'show')->name('auction.show');
    Route::put('/auction/update/{id}', 'update')->name('updateAuction');
});

//Google
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);


Route::post('/bid', [BidController::class, 'placeBid'])->name('placeBid');

Route::get('/fetch-subcategories', [CategoryController::class, 'fetchSubcategories'])->name('fetchSubcategories');

Route::get('/view-history', function () {
    $userId = auth()->id(); 

    $activities = DB::table('bids')
        ->join('auctions', 'bids.auctionid', '=', 'auctions.id')
        ->select(DB::raw("'Bid' as type, auctions.itemname as itemname, CONCAT('Bid value: ', bids.value) as details, bids.date"))
        ->where('bids.bidder', $userId)
        ->union(
            DB::table('auctions')
                ->select(DB::raw("'Auction' as type, auctions.itemname as itemname, CONCAT('Auction: ', auctions.itemname) as details, auctions.creationDate as date"))
                ->where('auctions.seller', $userId)
        )
        ->orderBy('date', 'desc')
        ->get();

    $activeAuctions = DB::table('auctions')
        ->join('bids', 'auctions.id', '=', 'bids.auctionid')
        ->select('auctions.itemname as itemname', 'auctions.currentprice as currentprice', 'auctions.deadline as deadline')
        ->where('auctions.status', 'active')
        ->where('bids.bidder', $userId)
        ->distinct()
        ->get();

    return view('pages.viewHistory', [
        'activities' => $activities,
        'activeAuctions' => $activeAuctions,
    ]);
})->name('view-history');
;

Route::get('/full-activity', function () {
    $userId = auth()->id();

    $activities = DB::table('bids')
        ->join('auctions', 'bids.auctionid', '=', 'auctions.id')
        ->select(DB::raw("'Bid' as type, auctions.itemname as itemname, CONCAT('Bid value: ', bids.value) as details, bids.date"))
        ->where('bids.bidder', $userId) 
        ->union(
            DB::table('auctions')
                ->select(DB::raw("'Auction' as type, auctions.itemname as itemname, CONCAT('Auction: ', auctions.itemname) as details, auctions.creationdate as date"))
                ->where('auctions.seller', $userId) 
        )
        ->orderBy('date', 'desc')
        ->get();

    return view('pages.fullActivity', ['activities' => $activities]);
})->name('full-activity');


Route::get('/active-auctions', function () {
    $userId = auth()->id(); 

    $activeAuctions = DB::table('auctions')
        ->join('bids', 'auctions.id', '=', 'bids.auctionid') 
        ->where('auctions.status', 'active') 
        ->where('bids.bidder', $userId) 
        ->select('auctions.itemname', 'auctions.currentprice', 'auctions.deadline')
        ->distinct()
        ->get();

    return view('pages.activeAuctions', ['activeAuctions' => $activeAuctions]);
})->name('active-auctions');

Route::get('/my-active-auctions', function () {
    $userId = auth()->id(); 
    
    $myActiveAuctions = DB::table('auctions')
        ->leftJoin('bids', 'auctions.id', '=', 'bids.auctionid')
        ->select(
            'auctions.id',
            'auctions.itemname as itemname',
            'auctions.currentprice as currentprice',
            DB::raw('COUNT(bids.id) as bid_count'),
            'auctions.deadline as deadline'
        )
        ->where('auctions.seller', $userId)
        ->where('auctions.status', 'active')
        ->groupBy('auctions.id', 'auctions.itemname', 'auctions.currentprice', 'auctions.deadline')
        ->get();

    
    return view('pages.myActiveAuctions', [
        'myActiveAuctions' => $myActiveAuctions
    ]);
})->name('my-active-auctions');


Route::get('/seller/{id}', [User::class, 'showSellerProfile'])->name('seller.profile');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
Route::delete('/reports/{id}', [ReportController::class, 'destroy'])->name('reports.delete');
Route::patch('/reports/{id}/toggle-resolved', [ReportController::class, 'toggleResolved'])->name('reports.toggleResolved');
//Block User
Route::post('/users/{id}/block', [UserController::class, 'blockUser'])->name('user.block');
Route::post('/user/unblock/{id}', [UserController::class, 'unblockUser'])->name('user.unblock');


// Notifications
Route::get('/notifications', [NotificationController::class, 'fetchNotifications']);
Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
Route::get('/notifications/unread-count', function () {
    $unreadCount = App\Models\Notification::where('sellerid', Auth::id())->where('read', false)->count();
    return response()->json(['unreadCount' => $unreadCount]);
});

//Rate
Route::post('/users/{userId}/rate', [RateController::class, 'rateUser'])->name('users.rate');


// Wishlist
Route::post('/wishlist/toggle/{id}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');


// Recover password
Route::get('/password-reset', function () {
    return view('auth.recovery');
})->name('reset.form');

Route::post('/password-reset/send', [MailController::class,'send'])->name('reset.send');

Route::get('/password-reset/set-new', function () {
    return view('auth.newpassword');
})->name('reset.setnew');

Route::post('/password-reset/newpassword', action: [MailController::class,'setNewPassword'])->name('reset.newpass');

Route::put('/delete-account/{id}', [UserController::class,'deleteAccount'])->name('account.delete');