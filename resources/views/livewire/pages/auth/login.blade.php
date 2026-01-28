<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // Redirect to unified dashboard - PermissionRedirect middleware will handle proper landing page
        $this->redirect(route('app.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Card Glassmorphism Login -->
    <div class="w-full sm:max-w-md px-8 py-10 bg-white/10 dark:bg-slate-900/70 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 shadow-2xl rounded-2xl">
        
        <!-- Header con Logo -->
        <div class="flex flex-col items-center mb-8">
            <div class="relative w-32 h-32 mb-4">
                <div class="absolute inset-0 bg-[#E8491B] blur-xl opacity-20 rounded-full"></div>
                <!-- Asegúrate de tener el logo en public/img/logo.webp -->
                <img src="{{ asset('img/logo.webp') }}" alt="Gadium Logo" class="relative w-full h-full object-contain drop-shadow-lg">
            </div>
            <p class="text-slate-400 text-sm mt-1">Industrial Intelligence Dashboard</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login" class="space-y-5">
            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1">{{ __('Email') }}</label>
                <div class="relative">
                    <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" 
                        class="w-full bg-slate-800/50 border border-slate-700 text-white text-sm rounded-xl focus:ring-[#E8491B] focus:border-[#E8491B] block p-2.5 placeholder-slate-500 transition-all duration-200" 
                        placeholder="user@gadium.com">
                </div>
                <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1">{{ __('Password') }}</label>
                <div class="relative">
                    <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password"
                        class="w-full bg-slate-800/50 border border-slate-700 text-white text-sm rounded-xl focus:ring-[#E8491B] focus:border-[#E8491B] block p-2.5 placeholder-slate-500 transition-all duration-200" 
                        placeholder="••••••••">
                </div>
                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label for="remember" class="flex items-center">
                    <input wire:model="form.remember" id="remember" type="checkbox" 
                        class="w-4 h-4 rounded border-slate-600 text-[#E8491B] focus:ring-[#E8491B] focus:ring-offset-slate-900 bg-slate-700">
                    <span class="ml-2 text-sm text-slate-400">Recordarme</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-[#E8491B] hover:text-orange-400 transition-colors" href="{{ route('password.request') }}" wire:navigate>
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <div class="pt-2">
                <button type="submit" 
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-orange-900/20 text-sm font-bold text-white bg-[#E8491B] hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-[#E8491B] transition-all duration-200 transform hover:scale-[1.02]">
                    Iniciar Sesión
                </button>
            </div>
        </form>
    </div>
    
    <!-- Footer Credits -->
    <div class="mt-8 text-center text-xs text-slate-600">
        &copy; {{ date('Y') }} Industrial Intelligence Dashboard. All rights reserved.
    </div>
</div>

