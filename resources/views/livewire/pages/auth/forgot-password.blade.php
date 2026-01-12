<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
<div>
    <!-- Card Glassmorphism -->
    <div class="w-full sm:max-w-md px-8 py-10 bg-white/10 dark:bg-slate-900/70 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 shadow-2xl rounded-2xl">
        
        <!-- Header con Logo -->
        <div class="flex flex-col items-center mb-6">
            <div class="relative w-14 h-14 mb-4">
                <div class="absolute inset-0 bg-[#E8491B] blur-xl opacity-20 rounded-full"></div>
                <img src="{{ asset('img/logo.webp') }}" alt="Gadium Logo" class="relative w-full h-full object-contain drop-shadow-lg">
            </div>
            <h2 class="text-xl font-bold tracking-tight text-white">
                Recuperar Acceso
            </h2>
        </div>

        <div class="mb-6 text-sm text-slate-400 text-center leading-relaxed">
            {{ __('¿Olvidaste tu contraseña? No hay problema. Simplemente indícanos tu dirección de correo electrónico y te enviaremos un enlace para restablecer la contraseña que te permitirá elegir una nueva.') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="sendPasswordResetLink" class="space-y-5">
            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1">{{ __('Correo Electrónico') }}</label>
                <div class="relative">
                    <input wire:model="email" id="email" type="email" name="email" required autofocus 
                        class="w-full bg-slate-800/50 border border-slate-700 text-white text-sm rounded-xl focus:ring-[#E8491B] focus:border-[#E8491B] block p-2.5 placeholder-slate-500 transition-all duration-200" 
                        placeholder="tu@email.com">
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="pt-2 space-y-3">
                <button type="submit" 
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-orange-900/20 text-sm font-bold text-white bg-[#E8491B] hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-[#E8491B] transition-all duration-200 transform hover:scale-[1.02]">
                    {{ __('Enviar enlace de restablecimiento') }}
                </button>

                <a href="{{ route('login') }}" class="flex justify-center text-sm font-medium text-slate-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver al Login
                </a>
            </div>
        </form>
    </div>

    <!-- Footer Credits -->
    <div class="mt-8 text-center text-xs text-slate-600">
        &copy; {{ date('Y') }} Gadium Industrial
    </div>
</div>
