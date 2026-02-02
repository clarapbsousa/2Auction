<?php

namespace App\Console;

use App\Events\AuctionEnding;
use App\Events\AuctionFinished;
use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Storage;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        $schedule->call(function () {
            $expiredAuctions = Auction::where('deadline', '<', now())
                                      ->where('status', '=', 'active')
                                      ->get();
    
            foreach ($expiredAuctions as $auction) {
                $hasBids = Bid::where('auctionid', $auction->id)->exists();
    
                $auction->status = $hasBids ? 'sold' : 'cancelled';
                $auction->save();
                
                event(new AuctionFinished($auction->itemname, $auction->seller, Storage::disk('public')->exists($auction->imagepath) ? asset('storage/' . $auction->imagepath) : url($auction->imagepath), $auction->id, route('auction.show', ['id' => $auction->id]), $hasBids ? 'sold' : 'cancelled'));

                \App\Models\Notification::create([
                    'auctionname' => $auction->itemname,
                    'bidvalue' => null,
                    'sellerid' => $auction->seller,
                    'imagepath' => Storage::disk('public')->exists($auction->imagepath) ? asset('storage/' . $auction->imagepath) : url($auction->imagepath),
                    'auctionid' => $auction->id,
                    'bidderid' => null,
                    'auctionurl' => route('auction.show', ['id' => $auction->id]), route('auction.show', ['id' => $auction->id]),
                    'bidderurl' => '',
                    'reporturl' => $hasBids ? 'sold' : 'cancelled',
                    'read' => FALSE,
                    'creationdate' => now()->toISOString(),
                    'type' => 'auctionended'
                ]);
                
                $bidders = array_unique(Bid::where('auctionid', $auction->id)->pluck('userid')->toArray());
                if (!empty($bidders)) {
                    foreach ($bidders as $bidder) {
                        event(new AuctionFinished($auction->itemname, $bidder, Storage::disk('public')->exists($auction->imagepath) ? asset('storage/' . $auction->imagepath) : url($auction->imagepath), $auction->id, route('auction.show', ['id' => $auction->id]), 'sold'));

                        \App\Models\Notification::create([
                            'auctionname' => $auction->itemname,
                            'bidvalue' => null,
                            'sellerid' => $bidder,
                            'imagepath' => Storage::disk('public')->exists($auction->imagepath) ? asset('storage/' . $auction->imagepath) : url($auction->imagepath),
                            'auctionid' => $auction->id,
                            'bidderid' => null,
                            'auctionurl' => route('auction.show', ['id' => $auction->id]), route('auction.show', ['id' => $auction->id]),
                            'bidderurl' => '',
                            'reporturl' => 'sold',
                            'read' => FALSE,
                            'creationdate' => now()->toISOString(),
                            'type' => 'auctionended'
                        ]);
                    }
                }

            }
        })->everyMinute();

        $schedule->call(function () {
            $approachingDeadlines = Auction::where('deadline', '>', now())
                                           ->where('deadline', '<=', now()->addMinutes(30))
                                           ->where('status', '=', 'active')
                                           ->get();
            
            $auctionIds = $approachingDeadlines->pluck('id');

            $bids = Bid::whereIn('auctionid', $auctionIds)->get();
                                       
            $notifiedBidders = [];
                                       
            foreach ($bids as $bid) {
                $bidderId = $bid->userid;
                $auction = $approachingDeadlines->firstWhere('id', $bid->auctionid);
                // Hurry up! 30 minutes left until auction "macbook pro" finishes!
                if (!in_array($bidderId, $notifiedBidders)) {
                    event(new AuctionEnding($auction->itemname, $auction->auctionid, Storage::disk('public')->exists($auction->imagepath) ? asset('storage/' . $auction->imagepath) : url($auction->imagepath), route('auction.show', ['id' => $auction->id]), $bidderId));
                    \App\Models\Notification::create([
                        'auctionname' => $auction->itemname,
                        'bidvalue' => '',
                        'sellerid' => '',
                        'imagepath' => Storage::disk('public')->exists($auction->imagepath) ? asset('storage/' . $auction->imagepath) : url($auction->imagepath),
                        'auctionid' => $auction->id,
                        'bidderid' => $bidderId,
                        'auctionurl' => route('auction.show', ['id' => $auction->id]),
                        'bidderurl' => '',
                        'reporturl' => '',
                        'read' => FALSE,
                        'creationdate' => now()->toISOString(),
                        'type' => 'auctionending'
                    ]);

                    $notifiedBidders[] = $bidderId;
                }
            }
        })->everyThirtyMinutes();
    }

    

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
