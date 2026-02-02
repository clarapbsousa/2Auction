<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRating implements ShouldBroadcast
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
    public $rating;
    public $username;
    /**
     * Create a new event instance.
     */
    public function __construct($username, $rating, $seller, $imagepath, $bidderid, $bidderUrl)
    {   
        $this->username = $username;
        $this->rating = $rating;
        $this->seller = $seller;
        $this->imagepath = $imagepath;
        $this->bidderid = $bidderid;
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
        return 'user-rating';
    }

}
