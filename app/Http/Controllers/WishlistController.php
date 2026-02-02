<?php

namespace App\Http\Controllers;

use App\Events\AuctionWishlist;
use App\Models\Auction;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Storage;

class WishlistController extends Controller
{
    public function toggle($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'You must be logged in to modify your wishlist.'], 401);
        }

        $alreadyInWishlist = Wishlist::where('user_id', $user->id)->where('auction_id', $id)->first();

        if ($alreadyInWishlist) {
            $alreadyInWishlist->delete();
            $user->auctions_followed -= 1;
            $user->save();

            return response()->json(['success' => true, 'message' => 'Removed from wishlist.']);
        }

        // Add the item to the wishlist
        Wishlist::create([
            'user_id' => $user->id,
            'auction_id' => $id,
        ]);
        
        $user->auctions_followed += 1;
        $user->save();

        $auction = Auction::find($id);

        event(new AuctionWishlist($auction->itemname, $user->username, $auction->seller, Storage::disk('public')->exists($auction->imagepath)
        ? asset('storage/' . $auction->imagepath)
        : url($auction->imagepath), $auction->id, $user->id, route('auction.show', ['id' => $auction->id]), route('seller.profile', ['id' => $user->id])));
        
        \App\Models\Notification::create([
            'auctionname' => $auction->itemname,
            'bidvalue' => null,
            'sellerid' => $auction->seller,
            'imagepath' => Storage::disk('public')->exists($auction->imagepath) ? asset('storage/' . $auction->imagepath) : url($auction->imagepath),
            'auctionid' => $auction->id,
            'bidderid' => $user->id,
            'auctionurl' => route('auction.show', ['id' => $auction->id]),
            'bidderurl' => route('seller.profile', ['id' => $user->id]),
            'reporturl' => '',
            'read' => FALSE,
            'creationdate' => now()->toISOString(),
            'type' => 'auctionwishlist'
        ]);

        return response()->json(['success' => true, 'message' => 'Added to wishlist.']);
    }
}
