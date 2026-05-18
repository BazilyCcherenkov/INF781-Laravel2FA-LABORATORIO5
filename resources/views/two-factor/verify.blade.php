<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Tu cuenta tiene activada la autenticación en dos factores.
        Ingresa el código de 6 dígitos de tu app autenticadora para continuar.
    </div>

    <form method="POST" action="{{ route('two-factor.verify.post') }}">
        @csrf
        <div class="mb-4">
            <x-input-label for="code" value="Código OTP" />
            <x-text-input
                id="code"
                name="code"
                type="text"
                maxlength="6"
                autocomplete="one-time-code"
                class="block mt-1 w-full text-center tracking-widest text-lg"
                placeholder="000000"
                autofocus
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Verificar
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 pt-6 border-t border-gray-200">
        <p class="text-sm text-gray-600 mb-3">
            ¿No tienes tu dispositivo? Usa un código de respaldo.
        </p>
        <form method="POST" action="{{ route('two-factor.verify.backup') }}">
            @csrf
            <div class="mb-4">
                <x-input-label for="backup_code" value="Código de Respaldo" />
                <x-text-input
                    id="backup_code"
                    name="backup_code"
                    type="text"
                    maxlength="8"
                    class="block mt-1 w-full text-center tracking-widest text-lg uppercase"
                    placeholder="A1B2C3D4"
                />
                <x-input-error :messages="$errors->get('backup_code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button class="bg-yellow-600 hover:bg-yellow-700 text-black">
                    Usar Código de Respaldo
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>