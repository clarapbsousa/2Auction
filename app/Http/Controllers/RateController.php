<?php
namespace App\Http\Controllers;

use App\Events\UserRating;
use App\Models\Rate;
use Illuminate\Http\Request;
use DB;
use App\Models\User;
use Storage;


class RateController extends Controller {

public function rateUser(Request $request, $userId)
{
    $validated = $request->validate([
        'rate' => 'required|numeric|min:1|max:5',
    ]);

    $user = DB::table('users')->where('id', $userId)->first();

    if (!$user) {
        return response()->json(['error' => 'User not found.'], 404);
    }

    // Cálculo da nova média
    $previousAvgRate = $user->avgrating ?? 0;
    $previousCount = $user->ratecount ?? 0;

    $newAvgRate = ($previousAvgRate * $previousCount + $validated['rate']) / ($previousCount + 1);

    // Atualiza o campo avgrate
    DB::table('users')
        ->where('id', $userId)
        ->update(['avgrating' => $newAvgRate]);

    Rate::create([
        'rating' => $validated['rate'],
        'rater' => $user->id,
        'rated' => $userId,
    ]);

    $authid = auth()->id();
    $authUrl = route('seller.profile', ['id' => $authid]);

    event(new UserRating(auth()->user()->username, $validated['rate'], $userId, Storage::disk('public')->exists($user->imagepath)
    ? asset('storage/' . $user->imagepath)
    : url("images/image.png"), $user->id, $authUrl));
    
    \App\Models\Notification::create(attributes: [
        'auctionname' => auth()->user()->username,
        'bidvalue' => $validated['rate'],
        'sellerid' => $userId,
        'imagepath' => Storage::disk('public')->exists($user->imagepath) ? asset('storage/' . $user->imagepath) : url("images/image.png"),
        'auctionid' => null,
        'bidderid' => $user->id,
        'auctionurl' => '',
        'bidderurl' => $authUrl,
        'reporturl' => '',
        'read' => FALSE,
        'creationdate' => now()->toISOString(),
        'type' => 'userrating'
    ]);
    
    return back()->with('success', 'Rating submitted successfully!');
}
}