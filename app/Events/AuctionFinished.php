<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionFinished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auction;
    public $imagepath;
    
    public $auctionid;
    public $auctionUrl;
    public $status;
    public $timestamp;
    public $seller;
    /**
     * Create a new event instance.
     */
    public function __construct($auction, $seller, $imagepath, $auctionid, $auctionUrl, $status)
    {
        $this->auction = $auction;
        $this->seller = $seller;
        $this->imagepath = $imagepath;
        $this->auctionid = $auctionid;
        $this->auctionUrl = $auctionUrl;
        $this->status = $status;
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
        return 'auction-finished';
    }

}
