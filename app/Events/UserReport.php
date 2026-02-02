<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserReport implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reportedUser;

    public $reporter;
    public $admin;
    public $imagepath;
    
    public $reportedid;
    public $reporterid;
    
    public $reportedUrl;
    public $reporterUrl;
    public $reportUrl;
    public $timestamp;
    /**
     * Create a new event instance.
     */
    public function __construct($reportedUser, $reporter, $admin, $imagepath, $reportedid, $reporterid, $reportedUrl, $reporterUrl, $reportUrl)
    {
        $this->reportedUser = $reportedUser;
        $this->reporter = $reporter;
        $this->admin = $admin;
        $this->imagepath = $imagepath;
        $this->reportedid = $reportedid;
        $this->reporterid = $reporterid;
        $this->reportedUrl = $reportedUrl;
        $this->reporterUrl = $reporterUrl;
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
        return 'user-report';
    }

}
