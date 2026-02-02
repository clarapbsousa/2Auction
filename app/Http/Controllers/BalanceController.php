<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class BalanceController extends Controller
{
    //Criar uma sessão de checkout do Stripe.
    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $amount = $request->amount * 100;

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Adicionar Saldo',
                        ],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('balance.success', ['amount' => $request->amount]),
                'cancel_url' => route('profile'),
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            Log::error('Erro Stripe: ' . $e->getMessage());
            return back()->with('error', 'Erro ao processar o pagamento.');
        }
    }

    //Atualizar o balance depois e se o pagamento for bem-sucedido
    public function success(Request $request)
    {
        $amount = $request->query('amount'); // Captura o valor da query string
        $user = Auth::user();

        if ($user && $amount) {
            $user->balance += $amount; // Atualizar o saldo
            $user->save();

        return redirect()->route('profile', ['#balance'])->with('success', 'Saldo atualizado com sucesso!');
    }

    return redirect()->route('profile', ['#balance'])->with('error', 'Erro ao atualizar o saldo.');
    }
    
}
