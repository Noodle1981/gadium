<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Configura tu contraseña para acceder a Gadium. Debe tener al menos 8 caracteres.') }}
    </div>

    <form method="POST" action="{{ route('password.setup.store', ['email' => $email, 'signature' => request('signature'), 'expires' => request('expires')]) }}">
        @csrf

        <!-- Email (hidden) -->
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="mb-4">
            <x-input-label for="email_display" :value="__('Email')" />
            <x-text-input id="email_display" class="block mt-1 w-full" type="email" :value="$email" disabled />
        </div>

        <!-- Password -->
        <div class="mb-4">
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Configurar Contraseña') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
