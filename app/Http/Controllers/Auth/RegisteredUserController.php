<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use GuzzleHttp\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'string', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Assuming phone number is included in the request
        $phoneNumber = $request->phone;

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phoneNumber,
            'password' => Hash::make($request->password),
        ]);

        // Send a welcome message via WhatsApp
        $message = "Welcome to the platform! You've successfully registered.";
        $client = new Client();

        $response = $client->request('POST', 'https://graph.facebook.com/v15.0/' . env('390956987069286') . '/messages', [
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'text',
                'text' => [
                    'body' => $message,
                ],
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . env('EAAFjksRWM2YBOyUl48tZBH1DmAacAPmZC50A7s2MeZBZCvbMFQZBpqroTnEuVPkWbmL82SPomEsHO0cTzVXF0XYHOFYzrbgfFu6w1fIIcSvptGWx7FZBZCbhizvFrDrRrppTqvZAoXGUhMZAn1IiXpwkJRmiWPxZCVCs9v033GjWfqga9xRaevDbIspmKW3mlY3aQoQ1iJhOI6ZB7hMdg858qtd'),
            ],
        ]);

        // Fire the Registered event
        event(new Registered($user));

        // Log in the user
        Auth::login($user);

        // Redirect to home page
        return redirect(RouteServiceProvider::HOME);
    }
}
