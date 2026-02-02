<?php

namespace App\Http\Controllers;

use App\Mail\MailModel;
use Carbon\Carbon;
use DB;
use Hash;
use Illuminate\Http\Request;
use Mail;
use Str;

class MailController extends Controller
{
    /*
    function send(Request $request) {
        $mailData = [
            'email' => $request->email,
        ];

        Mail::to($request->email)->send(new MailModel($mailData));
        return redirect()->route('login')
            ->withSuccess('Check your inbox. Password request sent successfully!');
    }
    */

    function send(Request $request) {
        $request->validate(['email' => 'required|email|exists:users,email']);
    
        $token = Str::random(60);
    
        // Store the token in the password_resets table
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    
        $resetLink = url('/password-reset/set-new?token=' . $token);
    
        $mailData = [
            'email' => $request->email,
            'resetLink' => $resetLink,
        ];
    
        Mail::to($request->email)->send(new MailModel($mailData));
    
        return redirect()->route('login')
            ->withSuccess('Check your inbox. Password request sent successfully!');
    }

    function setNewPassword(Request $request) {
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);
    
        // Retrieve the record from the password_resets table
        $reset = DB::table('password_resets')->where('token', $request->token)->first();
    
        if (!$reset || Carbon::parse($reset->created_at)->addMinutes(60)->isPast()) {
            return redirect()->back()->withErrors(['token' => 'This token is invalid or has expired.']);
        }
    
        // Update the user's password
        $user = \App\Models\User::where('email', $reset->email)->first();
    
        if (!$user) {
            return redirect()->back()->withErrors(['email' => 'No user found with this email.']);
        }
    
        $user->password = Hash::make($request->password);
        $user->save();
    
        // Delete the reset token
        DB::table('password_resets')->where('email', $reset->email)->delete();
    
        return redirect()->route('login')->with('success', 'Your password has been reset successfully!');
    }
}
