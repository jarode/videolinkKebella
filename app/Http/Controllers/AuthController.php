<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'nip' => 'required|size:10'
        ]);

        try {
            $response = Http::timeout(5)
                ->get('https://kebella.bitrix24.pl/rest/37/20vea8075todm8b0/crm.contact.list.json', [
                    'filter' => [
                        'EMAIL' => $validated['email'],
                        'UF_CRM_1693998994' => $validated['nip'],
                    ]
                ]);

            if (!$response->successful()) {
                Log::error('Błąd API Bitrix24', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return back()->withErrors(['login' => 'Błąd weryfikacji danych. Spróbuj ponownie później.']);
            }

            if (empty($response['result'])) {
                return back()->withErrors(['login' => 'Nieprawidłowy email lub NIP']);
            }

            session([
                'user_email' => $validated['email'],
                'user_id' => $response['result'][0]['ID'],
                'login_time' => now()->toDateTimeString()
            ]);

            return redirect()->route('videos.index');
        } catch (\Exception $e) {
            Log::error('Błąd logowania', ['error' => $e->getMessage()]);
            return back()->withErrors(['login' => 'Błąd weryfikacji danych. Spróbuj ponownie później.']);
        }
    }

    public function logout(Request $request)
    {
        session()->forget('user_email');
        return redirect()->route('login');
    }
}

