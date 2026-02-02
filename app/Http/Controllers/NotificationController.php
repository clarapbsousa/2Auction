<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function fetchNotifications()
    {
        $userId = auth()->id();

        // Fetch notifications with bidder's name
        $notifications = \App\Models\Notification::where('sellerid', $userId)
            ->orderBy('creationdate', 'desc')
            ->get()
            ->map(function ($notification) {
                // Add bidder's name dynamically
                $bidder = User::find($notification->bidderid);
                $notification->biddername = $bidder ? $bidder->username : 'Deleted User';
                return $notification;
            });

        return response()->json($notifications);
    }

    public function markAsRead($id)
{
    $notification = \App\Models\Notification::find($id);

    if (!$notification) {
        return response()->json(['message' => 'Notification not found'], 404);
    }

    $notification->read = true;
    $notification->save();

    return response()->json(['message' => 'Notification marked as read']);
}

}
