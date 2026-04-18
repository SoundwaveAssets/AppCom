<div class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-100 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg">
                <span class="text-2xl">🛒</span>
            </div>
            <h1 class="mt-4 text-2xl font-bold text-gray-800">Créer un compte</h1>
            <p class="text-gray-500 text-sm">Rejoignez notre boutique en ligne</p>
        </div>

        {{-- Carte --}}
        <div class="bg-white rounded-2xl shadow-lg p-8 border-t-4 border-purple-500">
            <form wire:submit="register" class="space-y-5">

                {{-- Nom --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                    <input
                        type="text"
                        wire:model.live="name"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 transition"
                        placeholder="Jean Dupont"
                    />
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                    <input
                        type="email"
                        wire:model.live="email"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 transition"
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
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 transition"
                        placeholder="••••••••"
                    />
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirmation --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                    <input
                        type="password"
                        wire:model.live="password_confirmation"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 transition"
                        placeholder="••••••••"
                    />
                </div>

                {{-- Bouton --}}
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-purple-500 to-indigo-600 text-white py-3 rounded-xl font-semibold hover:opacity-90 transition disabled:opacity-50"
                    @disabled($loading)
                >
                    @if($loading)
                        <span class="animate-pulse">Inscription en cours...</span>
                    @else
                        S'inscrire
                    @endif
                </button>

                {{-- Lien connexion --}}
                <p class="text-center text-sm text-gray-500">
                    Déjà un compte ?
                    <a href="{{ route('login') }}" class="text-purple-500 font-medium hover:underline">
                        Se connecter
                    </a>
                </p>

            </form>
        </div>

    </div>
</div>