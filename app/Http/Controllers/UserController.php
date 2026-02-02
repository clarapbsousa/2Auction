<?php
namespace App\Http\Controllers;


use App\Models\Auction;
use Auth;
use Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserBlock;

class UserController extends Controller{
    public function blockUser($blockedId)
{
    $blockerId = auth()->id();

    // Verificar se o usuário já está bloqueado
    $alreadyBlocked = UserBlock::where('blocker_id', $blockerId)
                               ->where('blocked_id', $blockedId)
                               ->exists();

    if ($alreadyBlocked) {
        return redirect()->back()->with('error', 'User is already blocked.');
    }

    // Bloquear o usuário
    UserBlock::create([
        'blocker_id' => $blockerId,
        'blocked_id' => $blockedId,
    ]);

    return redirect()->back()->with('success', 'User was blocked successfuly.');
}


public function unblockUser($blockedId)
{
    $blockerId = auth()->id();

    // Verificar se o usuário está bloqueado
    $blocked = UserBlock::where('blocker_id', $blockerId)
                        ->where('blocked_id', $blockedId)
                        ->first();

    if (!$blocked) {
        return redirect()->back()->with('error', 'Action failed.');
    }

    // Remover o bloqueio
    $blocked->delete();

    return redirect()->back()->with('success', 'User was unblocked successfuly.');
}

public function deleteAccount($id) {
    $currentUser = auth()->user();
    $user = User::find($id);

    if ($currentUser->id !== $user->id && !$currentUser->isadmin) {
        return redirect()->back()->withInput()->with('error', 'You do not have permissions to delete this account!');
    }

    $auctions = Auction::where('seller', $id)
    ->where('status', 'active')
    ->get();


    if ($auctions->count() > 0) {
        return redirect()->back()->withInput()->with('error', 'You cannot delete your account while having active auctions.');
    }

    $randomNumber = rand( 0,9999);

    $user->name = 'Deleted User';
    $user->username = 'deleteduser' . $randomNumber;
    $user->imagepath = url("images/image.png");
    $user->description = 'This account was deleted.';
    $user->email = $user->username . '@2auction.com';
    $user->password = Hash::make('2auctioniscool');
    $user->isbanned = FALSE;
    $user->isadmin = FALSE;
    
    $user->save();
    
    Auth::logout();

    return redirect()->route('home')->withInput()->with('success','User account deleted!');
}

}