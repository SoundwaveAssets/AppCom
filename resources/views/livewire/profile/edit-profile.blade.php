<div class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-100 py-10 px-4">
    <div class="max-w-2xl mx-auto space-y-8">

        {{-- Header --}}
        <div class="text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto shadow-lg">
                <span class="text-3xl text-white font-bold">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
            </div>
            <h1 class="mt-4 text-2xl font-bold text-gray-800">Mon Profil</h1>
            <p class="text-gray-500 text-sm">Gérez vos informations personnelles</p>
        </div>

        {{-- Message de succès --}}
        @if($successMessage)
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2">
                <span>✅</span>
                <span>{{ $successMessage }}</span>
            </div>
        @endif

        {{-- Section infos personnelles --}}
        <div class="bg-white rounded-2xl shadow-md p-6 border-t-4 border-purple-500">
            <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                <span class="text-purple-500">👤</span> Informations personnelles
            </h2>
            <form wire:submit="updateProfile" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                    <input
                        type="text"
                        wire:model.live="name"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 transition"
                        placeholder="Votre nom"
                    />
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                    <input
                        type="email"
                        wire:model.live="email"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-400 transition"
                        placeholder="votre@email.com"
                    />
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-purple-500 to-indigo-600 text-white py-3 rounded-xl font-semibold hover:opacity-90 transition disabled:opacity-50"
                    @disabled($loading)
                >
                    @if($loading)
                        <span class="animate-pulse">Mise à jour...</span>
                    @else
                        Mettre à jour le profil
                    @endif
                </button>
            </form>
        </div>

        {{-- Section changement mot de passe --}}
        <div class="bg-white rounded-2xl shadow-md p-6 border-t-4 border-indigo-500">
            <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                <span class="text-indigo-500">🔒</span> Changer le mot de passe
            </h2>
            <form wire:submit="updatePassword" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                    <input
                        type="password"
                        wire:model.live="current_password"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
                        placeholder="••••••••"
                    />
                    @error('current_password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                    <input
                        type="password"
                        wire:model.live="new_password"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
                        placeholder="••••••••"
                    />
                    @error('new_password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                    <input
                        type="password"
                        wire:model.live="new_password_confirmation"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
                        placeholder="••••••••"
                    />
                </div>
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-indigo-500 to-blue-600 text-white py-3 rounded-xl font-semibold hover:opacity-90 transition"
                >
                    Changer le mot de passe
                </button>
            </form>
        </div>

    </div>
</div>