<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg">
                <span class="text-2xl">🛒</span>
            </div>
            <h1 class="mt-4 text-2xl font-bold text-gray-800">Bon retour !</h1>
            <p class="text-gray-500 text-sm">Connectez-vous à votre compte</p>
        </div>

        {{-- Carte --}}
        <div class="bg-white rounded-2xl shadow-lg p-8 border-t-4 border-blue-500">
            <form wire:submit="login" class="space-y-5">

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                    <input
                        type="email"
                        wire:model.live="email"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                        placeholder="jean@exemple.com"
                    />
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mot de passe --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <input
                        type="password"
                        wire:model.live="password"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                        placeholder="••••••••"
                    />
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Se souvenir + mot de passe oublié --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" wire:model="remember" class="rounded" />
                        Se souvenir de moi
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-500 hover:underline">
                        Mot de passe oublié ?
                    </a>
                </div>

                {{-- Bouton --}}
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-3 rounded-xl font-semibold hover:opacity-90 transition disabled:opacity-50"
                    @disabled($loading)
                >
                    @if($loading)
                        <span class="animate-pulse">Connexion en cours...</span>
                    @else
                        Se connecter
                    @endif
                </button>

                {{-- Lien inscription --}}
                <p class="text-center text-sm text-gray-500">
                    Pas encore de compte ?
                    <a href="{{ route('register') }}" class="text-blue-500 font-medium hover:underline">
                        S'inscrire
                    </a>
                </p>

            </form>
        </div>

    </div>
</div>