<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionEnding implements ShouldBroadcast
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
    public function __construct($auction, $auctionid, $imagepath, $auctionUrl, $bidderid)
    {
        $this->auction = $auction;
        $this->imagepath = $imagepath;
        $this->auctionid = $auctionid;
        $this->auctionUrl = $auctionUrl;
        $this->bidderid = $bidderid;
        $this->timestamp = now()->toISOString();
    }
    

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return 'user.' . $this->bidderid;
    }

    public function broadcastAs() {
        return 'auction-ending';
    }

}
