<?php

namespace App\Http\Controllers;



use App\Events\AuctionReport;
use App\Events\NewReport;
use App\Events\UserReport;
use App\Models\Auction;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Database\Eloquent\Collection;
use Storage;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAdminPanel', auth()->user());
        
        // Obter os filtros de pesquisa
        $searchTerm = $request->get('x', null);
        $order = $request->get('order', 'desc');
        $solvedFilter = $request->get('solved', null);
    
        // Relatórios de "auction"
        $auctionQuery = Report::where('type', 'auction');
    
        if (!is_null($solvedFilter)) {
            $auctionQuery->where('issolved', $solvedFilter === 'true');
        }
    
        if (!is_null($searchTerm)) {
            $auctionQuery->where('reported_id', 'LIKE', "%$searchTerm%");
        }
    
        $auctionReports = $auctionQuery
            ->with(['auction' => function ($query) {
                $query->select('id', 'itemname');
            }])
            ->orderBy('date', $order)
            ->paginate(8, ['*'], 'auction_page');
    
        // dd($auctionReports->first());
        // Relatórios de "user"
        $userQuery = Report::where('type', 'user');
    
        if (!is_null($solvedFilter)) {
            $userQuery->where('issolved', $solvedFilter === 'true');
        }
    
        if (!is_null($searchTerm)) {
            $userQuery->where('reported_id', 'LIKE', "%$searchTerm%");
        }
    
        $userReports = $userQuery
            ->orderBy('date', $order)
            ->paginate(8, ['*'], 'user_page');
    
        // Retornar a view com os relatórios
        return view('pages.admin.reportlist', compact('auctionReports', 'userReports', 'order', 'solvedFilter'));
    }
    

    public function create()
    {
        return view('pages.auctions.auction');
    }

    public function store(Request $request)
{   
    $validatedData = $request->validate([
        'reasons' => 'required|array|min:1', 
        'description' => 'nullable|string|max:255',
        'reported_id' => 'required|integer',
        'type' => 'required|in:auction,user',
    ]);

    $user = auth()->user(); 
    $reviewerId = 0; 


    $reasons = implode(', ', $validatedData['reasons']);
    //dd($validatedData);
    

     Report::create([
        'reported_id' => $validatedData['reported_id'],
        'reason' => $reasons,
        "issolved" => false,
        'reporter' => $user->id,
        'reviewer' => $reviewerId,
        'description' => $validatedData['description'] ?? null,
        'type' => $validatedData['type'],
        'date' => now(),
    ]);

    if ($validatedData['type'] == "auction") {
        $auction = Auction::find($validatedData["reported_id"]);

        $admins = User::where("isadmin", true)->get();

        $reporturl = route('reports.store');


        
        foreach ($admins as $admin) {
            event(new AuctionReport($auction->itemname, $user->username, $admin->id, Storage::disk('public')->exists($auction->imagepath)
            ? asset('storage/' . $auction->imagepath)
            : url($auction->imagepath), $auction->id, $user->id, route('auction.show', ['id' => $auction->id]), route('seller.profile', ['id' => $user->id]), $reporturl));
            \App\Models\Notification::create([
                'auctionname' => $auction->itemname,
                'bidvalue' => null,
                'sellerid' => $admin->id,
                'imagepath' => Storage::disk('public')->exists($auction->imagepath) ? asset('storage/' . $auction->imagepath) : url($auction->imagepath),
                'auctionid' => $auction->id,
                'bidderid' => $user->id,
                'auctionurl' => route('auction.show', ['id' => $auction->id]),
                'bidderurl' => route('seller.profile', ['id' => $user->id]),
                'reporturl' => $reporturl,
                'read' => FALSE,
                'creationdate' => now()->toISOString(),
                'type' => 'auctionreport'
            ]);
        }
    } else if ($validatedData['type'] == "user") {
        $reportedUser = User::find($validatedData["reported_id"]);
        $reporturl = route('reports.store');
        $admins = User::where("isadmin", true)->get();

        foreach ($admins as $admin) {
            event(new UserReport($reportedUser->username, $user->username, $admin->id, Storage::disk('public')->exists($reportedUser->imagepath)
            ? asset('storage/' . $reportedUser->imagepath)
            : url('images/image.png'), $reportedUser->id, $user->id, route('seller.profile', ['id' => $reportedUser->id]), route('seller.profile', ['id' => $user->id]), $reporturl));
            \App\Models\Notification::create([
                'auctionname' => $reportedUser->username,
                'bidvalue' => null,
                'sellerid' => $admin->id,
                'imagepath' => Storage::disk('public')->exists($reportedUser->imagepath) ? asset('storage/' . $reportedUser->imagepath) : url('images/image.png'),
                'auctionid' => $reportedUser->id,
                'bidderid' => $user->id,
                'auctionurl' => route('seller.profile', ['id' => $reportedUser->id]),
                'bidderurl' => route('seller.profile', ['id' => $user->id]),
                'reporturl' => $reporturl,
                'read' => FALSE,
                'creationdate' => now()->toISOString(),
                'type' => 'userreport'
            ]);
        }
    }



    return redirect()->back()->with('success', 'Report sent successfuly!');
}

public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return redirect()->back()->with('success', 'Report deleted successfuly.');
    }

    public function toggleResolved($id)
    {
        $report = Report::findOrFail($id);

        // Obter o ID do administrador autenticado
        $user = auth()->user();
        if (!$user) {
            return redirect()->back()->with('error', 'You need to be registed to report auctions.');
        }

        $report->issolved = !$report->issolved;
        $report->reviewer = $user->id; // Atualiza com o ID do admin que clicou
        $report->save();

        return redirect()->back()->with('success', 'You report was sent successfuly.');
    }
}


