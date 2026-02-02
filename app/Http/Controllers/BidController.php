<?php

namespace App\Http\Controllers;

use App\Events\AuctionBid;
use Illuminate\Http\Request;
use App\Models\Auction;
use App\Models\Bid;
use Notification;
use Storage;

class BidController extends Controller
{
    public function placeBid(Request $request)
    {
        $request->validate([
            'auction_id' => 'required|exists:auctions,id',
            'bid_value' => 'required|numeric|min:5', 
        ]);
        
        
        $auction = Auction::findOrFail($request->auction_id);

        $lastBid = Bid::where('auctionid', $request->auction_id)
        ->orderBy('date', 'desc')
        ->first();

        // Check if the auction deadline has passed
        if ($auction->deadline < now()) {
            $hasBids = Bid::where('auctionid', $auction->id)->exists();
            $auction->status = $hasBids ? 'sold' : 'cancelled';
            $auction->save();

            if ($auction->status === 'sold') {
                $lastBid = Bid::where('auctionid', $auction->id)->orderBy('date', 'desc')->first();
                $winningUser = $lastBid->user;
        
                $winningUser->balance -= $lastBid->value;
                $winningUser->save();
            }

            return redirect()->back()->with('error', 'This auction has ended and is now ' . $auction->status . '.');
        }

        if ($lastBid && $lastBid->bidder == auth()->id()) {
            return redirect()->back()->withInput()->with('error', 'You cannot place consecutive bids!');
        }
    
        $minimumBid = $auction->currentprice + $auction->increment;
        if ($request->bid_value < $minimumBid) {
            return redirect()->back()->withInput()->with('error', 'Bid must be at least ' . $minimumBid . '€');
        }

        $maximumBid = $auction->currentprice * 5;
        if ($request->bid_value >= $maximumBid) {
            return redirect()->back()->withInput()->with('error', 'Bid cannot exceed ' . $maximumBid . '€');
        }



        if ($request->bid_value > auth()->user()->balance) {
            return redirect()->back()->withInput()->with('error', "You do not have enough money for this bid!");
        }

        // check all bids, count unique bidders
        // if bidders > 30, return
        $uniqueBiddersCount = Bid::where('auctionid', $auction->id)->distinct('bidder')->count('bidder');

        if ($uniqueBiddersCount >= 30) {
            return redirect()->back()->withInput()->with('error', 'This auction already has 30 unique bidders. No more bidders can join the auction.');
        }

        $timeRemaining = $auction->deadline->diffInMinutes(now(), false);
        if ($timeRemaining >= -15) {
            $auction->deadline = $auction->deadline->addMinutes(30);
            $auction->save();
        }
        
    
        Bid::create([
            'value' => $request->bid_value,
            'bidder' => auth()->id(),
            'auctionid' => $request->auction_id,
        ]);
        
        $user = auth()->user();
        $user->number_of_bids +=1;
        $user->save();

        $auction->currentprice = $request->bid_value;
        $auction->save();
        
        event(new AuctionBid($auction->itemname, number_format($request->bid_value, 2), $user->username, $auction->seller, Storage::disk('public')->exists($auction->imagepath)
        ? asset('storage/' . $auction->imagepath)
        : url($auction->imagepath), $auction->id, $user->id, route('auction.show', ['id' => $auction->id]), route('seller.profile', ['id' => $user->id])));
        
        \App\Models\Notification::create([
            'auctionname' => $auction->itemname,
            'bidvalue' => $request->bid_value,
            'sellerid' => $auction->seller,
            'imagepath' => Storage::disk('public')->exists($auction->imagepath) ? asset('storage/' . $auction->imagepath) : url($auction->imagepath),
            'auctionid' => $auction->id,
            'bidderid' => $user->id,
            'auctionurl' => route('auction.show', ['id' => $auction->id]),
            'bidderurl' => route('seller.profile', ['id' => $user->id]),
            'reporturl' => '',
            'read' => FALSE,
            'creationdate' => now()->toISOString(),
            'type' => 'auctionbid'
        ]);

        return redirect()->back()->with('success', 'Your bid has been placed successfully!');
    }
    
    function newBid(Request $request) {
        
    }   
}



