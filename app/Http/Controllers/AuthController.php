<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->input('email');
        $nip = $request->input('nip');

        $response = Http::get('https://kebella.bitrix24.pl/rest/37/20vea8075todm8b0/crm.contact.list.json', [
            'filter' => [
                'EMAIL' => $email,
                'UF_CRM_1693998994' => $nip,
            ]
        ]);

        if ($response->successful() && count($response['result']) > 0) {
            session(['user_email' => $email]);
            return redirect()->route('videos.index');
        }

        return back()->withErrors(['login' => 'Błędne dane logowania']);
    }

    public function logout(Request $request)
    {
        session()->forget('user_email');
        return redirect()->route('login');
    }
}

