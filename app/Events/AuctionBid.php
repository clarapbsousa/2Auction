<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionBid implements ShouldBroadcast
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
    public function __construct($auction, $value, $bidder, $seller, $imagepath, $auctionid, $bidderid, $auctionUrl, $bidderUrl)
    {
        $this->auction = $auction;
        $this->value = $value;
        $this->bidder = $bidder;
        $this->message = $bidder . ' placed a ' . $value . '€ bid on your auction ' . $auction;
        $this->seller = $seller;
        $this->imagepath = $imagepath;
        $this->auctionid = $auctionid;
        $this->bidderid = $bidderid;
        $this->auctionUrl = $auctionUrl;
        $this->bidderUrl = $bidderUrl;
        $this->timestamp = now()->toISOString();
    }
    

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return 'user.' . $this->seller;
    }

    public function broadcastAs() {
        return 'auction-bid';
    }

}
