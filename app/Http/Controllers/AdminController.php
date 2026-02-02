<?php
namespace App\Http\Controllers;

use App\Events\BidDeleted;
use App\Models\Auction;
use App\Models\Bid;
use DB;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Storage;

class AdminController extends Controller
{   
    public $timestamps = false;

    public function updateAdmin($id)
    {
        $admin = Auth::user();
        $this->authorize('updateAction', $admin);

        
        $user = User::findOrFail($id);

        $user->isadmin = ($user->isadmin) ? FALSE : TRUE;

        $user->save();

        // Redirecionar para a página de perfil com uma mensagem de sucesso
        return redirect()->route('userlist')->with('success', 'Admin status successfully updated!');
    }

    public function updateBan($id)
    {   
        $admin = Auth::user();
        $this->authorize('updateAction', $admin);

        $user = User::findOrFail($id);

        $user->isbanned = ($user->isbanned) ? FALSE : TRUE;

        $user->save();

        return redirect()->route('userlist')->with('success', 'Banned status successfully updated!');
    }

    public function updateStatus($id, Request $request)
    {   
        $admin = Auth::user();
        $this->authorize('updateAction', $admin);

        $status = $request->input("status");

        $auction = Auction::findOrFail($id);

        $auction->status = $status;

        $auction->save();

        return redirect()->route('auctionlist')->with('success', 'Auction status successfully updated!');
    }

    public function DeleteAuction($id) {
        $user = Auth::user();
        $this->authorize('updateAction', $user);


        $auction = Auction::findOrFail($id);

        $auction->delete();

        return redirect()->route('auctionlist')->with('success', 'Auction successfully deleted!');
    }

    public function deleteBid($id, $aid) {
        $admin = Auth::user();
    
        $this->authorize('updateAction', $admin);
    
        $bid = Bid::findOrFail($id);
    
        $auction = Auction::findOrFail($aid);
    
        $lastBid = Bid::where('auctionid', $aid)
                      ->where('id', '!=', $id)
                      ->orderBy('date', 'desc')
                      ->first();
    
        if ($lastBid) {
            $auction->currentprice = $lastBid->value;
        } else {
            $auction->currentprice = $auction->startingprice;
        }
        $auction->save();
    
        $bid->delete();

        event(new BidDeleted($auction->itemname,  $bid->bidder, Storage::disk('public')->exists($auction->imagepath)
        ? asset('storage/' . $auction->imagepath)
        : url($auction->imagepath), $auction->id, route('auction.show', ['id' => $auction->id])));
        
        \App\Models\Notification::create([
            'auctionname' => $auction->itemname,
            'bidvalue' => null,
            'sellerid' => $bid->bidder,
            'imagepath' => Storage::disk('public')->exists($auction->imagepath) ? asset('storage/' . $auction->imagepath) : url($auction->imagepath),
            'auctionid' => $auction->id,
            'bidderid' => $bid->bidder,
            'auctionurl' => route('auction.show', ['id' => $auction->id]),
            'bidderurl' => '',
            'reporturl' => '',
            'read' => FALSE,
            'creationdate' => now()->toISOString(),
            'type' => 'biddeleted'
        ]);
    
        return redirect()->route('bidlist', ['id' => $aid])->with('success', 'Bid successfully deleted!');
    }
    
    public function userList()
    {
        $user = Auth::user();
        $this->authorize('viewAdminPanel', $user); 
        
        $users = User::where('id', '!=', 0)->paginate(8); 
        return view('pages.admin.userlist', ['user' => $user, 'users' => $users]);
    }

    public function auctionList()
    {
        $user = Auth::user();
        $this->authorize('viewAdminPanel', $user);
    
        $users = User::where('id', '!=', 0)->get(); 
        $auctions = Auction::paginate(6);
    
        $statuses = DB::select("SELECT unnest(enum_range(NULL::AuctionStatus)) AS status");
        $statuses = array_map(fn($s) => $s->status, $statuses);
    
        return view('pages.admin.auctionlist', ['user' => $user, 'users' => $users, 'auctions' => $auctions, 'statuses' => $statuses]);
    }

    public function bidList($id)
    {
        $user = Auth::user();
        $this->authorize('viewAdminPanel', $user);

        $auction = $id;
        $bids = Bid::with('user')->where('auctionid', $id)->paginate(10);
    
        return view('pages.admin.bidlist', ['bids' => $bids, 'auction' => $auction]);
    }

    

    
}
