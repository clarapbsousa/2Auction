<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionReport implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auction;

    public $bidder;
    public $value;
    public $message;
    public $admin;
    public $imagepath;
    
    public $auctionid;
    public $bidderid;
    
    public $auctionUrl;
    public $bidderUrl;
    public $reportUrl;
    public $timestamp;
    /**
     * Create a new event instance.
     */
    public function __construct($auction, $bidder, $admin, $imagepath, $auctionid, $bidderid, $auctionUrl, $bidderUrl, $reportUrl)
    {
        $this->auction = $auction;
        $this->bidder = $bidder;
        $this->admin = $admin;
        $this->imagepath = $imagepath;
        $this->auctionid = $auctionid;
        $this->bidderid = $bidderid;
        $this->auctionUrl = $auctionUrl;
        $this->bidderUrl = $bidderUrl;
        $this->reportUrl = $reportUrl;
        $this->timestamp = now()->toISOString();
    }
    

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return 'user.' . $this->admin;
    }

    public function broadcastAs() {
        return 'auction-report';
    }

}
