<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Auction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;

class ProfileController extends Controller
{   
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'imagepath' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;

        if ($request->hasFile('imagepath')) {

            if ($user->imagepath && Storage::disk('public')->exists($user->imagepath)) {
                Storage::disk('public')->delete($user->imagepath);
            }

            $extension = $request->file('imagepath')->getClientOriginalExtension();

            $filename = "{$id}.{$extension}";

            $imagepath = $request->file('imagepath')->storeAs('profile_pictures', $filename, 'public');

            $user->imagepath = $imagepath;
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Perfil atualizado com sucesso!');
    }

    /*
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'imagepath' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        $user = User::findOrFail($id);
    
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
    
        if ($request->hasFile('imagepath')) {
            // Delete the old image if it exists
            if ($user->imagepath && Storage::disk('public')->exists($user->imagepath)) {
                Storage::disk('public')->delete($user->imagepath);
            }
    
            // Handle the uploaded file
            $extension = $request->file('imagepath')->getClientOriginalExtension();
            $filename = "{$id}.{$extension}";
            $path = "profile_pictures/{$filename}";
    
            $manager = ImageManager::withDriver(new Driver());

            $image = $manager->read('imagepath');
            $image->resize(500, 500);

            // Save the cropped image to the public disk
            Storage::disk('public')->put($path, (string)$image->encode());
    
            $user->imagepath = $path;
        }
    
        $user->save();
    
        return redirect()->route('profile')->with('success', 'Perfil atualizado com sucesso!');
    }
    */
    
    

    public function edit()
    {
        // Obtém o utilizador autenticado
        $user = auth()->user();

        // Retorna a view de edição do perfil com os dados do utilizador
        return view('pages.edits.editUserProfile', compact('user') );
    }

    public function cancelAuction($id)
    {
        $user = Auth::user();
        $auction = Auction::findOrFail($id);
        
        $this->authorize('updateAuctionPermission', $auction); // atenção aqui: autorizações em laravel usam só 1 argumento
    
        $auction->status = "cancelled";
        $auction->save();
    
        return redirect()->route('profile')->with('success', 'Auction successfully deleted!');
    }
    
}
