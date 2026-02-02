<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auction;

    public $bidder;
    public $value;
    public $message;
    public $seller;
    public $imagepath;
    
    public $auctionid;
    public $bidderid;
    
    public $auctionUrl;
    public $bidderUrl;
    public $timestamp;
    /**
     * Create a new event instance.
     */
    public function __construct($auction, $bidder, $imagepath, $auctionid, $auctionUrl)
    {
        $this->auction = $auction;
        $this->bidder = $bidder;
        $this->imagepath = $imagepath;
        $this->auctionid = $auctionid;
        $this->auctionUrl = $auctionUrl;
        $this->timestamp = now()->toISOString();
    }
    

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return 'user.' . $this->bidder;
    }

    public function broadcastAs() {
        return 'bid-deleted';
    }

}
