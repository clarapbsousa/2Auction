<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\Auction;
use App\Models\Category;
use App\Models\Subcategory;
use App\Events\AuctionBid;
use Illuminate\Support\Facades\Storage;

class AuctionController extends Controller
{   
    public function profile()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Precisa de estar autenticado.');
        }
    
        $user = auth()->user();
    
        return view('pages.profileUserpage', compact('user'));
    }    

    public function show($id)
    {
        $auction = Auction::with('seller')->findOrFail($id);
        $seller = User::findOrFail($auction->seller); 
    

        $bids = Bid::with('user')->where('auctionid',$auction->id)->get();

    
        if (auth()->check()) {
            $authId = auth()->id();
    

            $blockedBySeller = UserBlock::where('blocker_id', $seller->id)
                                        ->where('blocked_id', $authId)
                                        ->exists();
    
    
            if ($blockedBySeller) {
                return redirect()->route('home')->with('error', 'You are blocked by the auction seller. You can not access his auctions.');
            }
        }
    
        $bidsCount = Bid::where('auctionid', $auction->id)->count();
    
        return view('pages.auctions.auction', compact('auction', 'seller', 'bidsCount','bids'));
    }

    public function index(Request $request)
    {
        $categories = Category::all(); 
        $subcategories = Subcategory::all(); 
    
        $query = Auction::query();
    
        if ($request->has('q') && $request->input('q') !== null) {
            $searchTerm = $request->input('q');
            $tsQuery = str_replace(' ', ' & ', $searchTerm);
            $query->whereRaw('LOWER(itemname) LIKE ?' , ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereRaw("to_tsvector('english', \"itemname\") @@ to_tsquery('english', ?)", [$tsQuery]);
        }
    
        if ($request->has('category') && $request->input('category') !== null) {
            $categoryId = $request->input('category');
            $query->whereHas('subcategory', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }
    
        if ($request->has('subcategory') && $request->input('subcategory') !== null) {
            $subcategoryId = $request->input('subcategory');
            $query->whereHas('subcategory', function ($q) use ($subcategoryId) {
                $q->where('id', $subcategoryId);
            });
        }
    
        $auctions = $query->paginate(6)->appends($request->query());
    
        return view('pages.auctions.index', compact('categories', 'subcategories', 'auctions'));
    }
    
    
    public function destroy($id)
    {
        $auction = Auction::findOrFail($id);
        
        if (auth()->user()->id == $auction->seller) {
            $auction->delete();
            return redirect()->route('auction.index')->with('success', 'Auction deleted successfully.');
        }
        else {
            return redirect()->route('auction.show', $id)->with('failure','You cannot delete this auction.');
        }
    }

    public function update(Request $request, $id)
    {
    $validated = $request->validate(['currentprice' => 'required|numeric|min:0', 'description' => 'required|string|max:1000',]);

    $auction = Auction::findOrFail($id);
    $auction->currentprice = $validated['currentprice'];
    $auction->description = $validated['description'];
    $auction->save();

    return redirect()->route('auction.show', $id)->with('success', 'Auction updated successfully!');
    }

    public function updateStatus(Request $request, $id)
{
    $auction = Auction::findOrFail($id);

    $validStatuses = ['active', 'sold', 'requestCancellation', 'cancelled'];
    if (!in_array($request->input('status'), $validStatuses)) {
        return redirect()->back()->with('error', 'Invalid status.');
    }

    $auction->status = $request->input('status');
    $auction->save();

    return redirect()->route('profile', ['#auctions'])->with('success', 'Auction status updated successfully.');
}


    public function store(Request $request)
    {
        
        $request->merge([
            'deadLine' => \Carbon\Carbon::parse($request->deadLine)->format('Y-m-d H:i:s')
        ]);

        $deadline = \Carbon\Carbon::parse($request->deadLine);

        if ($deadline->isBefore(\Carbon\Carbon::today()) || $deadline->diffInDays(\Carbon\Carbon::now()) > 30) {
            return redirect()->back()->withInput()->with('error', 'Deadline must be today or later, and cannot exceed 30 days.');
        }
        
        $restrictedTerms = [
            'firearms',
            'guns',
            'ammunition',
            'explosives',
            'drugs',
            'narcotics',
            'marijuana',
            'cocaine',
            'heroin',
            'methamphetamine',
            'illegal substances',
            'counterfeit',
            'pirated',
            'stolen',
            'fraudulent',
            'pornography',
            'explicit material',
            'hate speech',
            'racism',
            'violence',
            'weapons',
            'knives',
            'blades',
            'tobacco',
            'alcohol',
            'lottery',
            'gambling',
            'poison',
            'hazardous materials',
            'human trafficking',
            'endangered species',
            'fake documents',
            'bribery',
            'money laundering',
        ];
        
        $inputs = [
            'itemname' => $request->itemname,
            'description' => $request->description,
        ];
        
        foreach ($inputs as $field => $value) {
            foreach ($restrictedTerms as $term) {
                if (stripos($value, $term) !== false) {
                    return redirect()->back()->withInput()->with('error', "The $field contains restricted content: $term. Please modify your submission.");
                }
            }
        }
        
        $auction = Auction::create([
            'itemname' => $request->itemname,
            'startingprice' => $request->startingPrice,
            'currentprice' => $request->startingPrice,
            'creationdate' => now(),
            'increment' => $request->increment,
            'deadline' => $request->deadLine,
            'subcategory' => $request->subcategory,
            'description' => $request->description,
            'imagepath' => '',
            'seller' => auth()->id(),
        ]);

        
    

        if ($request->hasFile('imagepath')) {
            $extension = $request->file('imagepath')->getClientOriginalExtension();
            $filename = "{$auction->id}.{$extension}";
    
            $imagepath = $request->file('imagepath')->storeAs('auction_pictures', $filename, 'public');
            $auction->imagepath = $imagepath;
        }
    
        $auction->save();
        
        $user = User::find(auth()->id());
        $user->number_of_auctions += 1;
        $user->save();
    
        return redirect()->route('profile')->with('success', 'Auction created successfully!');
    }
    
    public function home()
    {
        $auctions = Auction::inRandomOrder()->take(8)->get();
        $categories = Category::all();

        $homepageCategory = Category::with('subcategories')->inRandomOrder()->first();

        $userid = auth()->id();

        $wishlistedAuctionIds = Wishlist::where('user_id', $userid)->pluck('auction_id');

        $wishlistedAuctions = Auction::whereIn('id', $wishlistedAuctionIds)->get();
        return view('layouts.home', compact('auctions', 'categories', 'wishlistedAuctions', 'homepageCategory'));
    }
    

}

