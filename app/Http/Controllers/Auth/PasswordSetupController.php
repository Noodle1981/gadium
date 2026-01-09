<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordSetupController extends Controller
{
    /**
     * Show the password setup form.
     */
    public function show(Request $request)
    {
        // Verificar que la URL esté firmada y no haya expirado
        if (! $request->hasValidSignature()) {
            abort(401, 'Este enlace ha expirado o no es válido.');
        }

        $email = $request->email;
        
        return view('auth.setup-password', compact('email'));
    }

    /**
     * Handle the password setup.
     */
    public function store(Request $request)
    {
        // Verificar firma
        if (! $request->hasValidSignature()) {
            abort(401, 'Este enlace ha expirado o no es válido.');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::where('email', $request->email)->firstOrFail();
        
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')
            ->with('status', '¡Contraseña configurada exitosamente! Ya puedes iniciar sesión.');
    }
}
