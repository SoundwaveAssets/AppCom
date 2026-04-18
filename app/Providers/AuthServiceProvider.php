<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Models\Brand;
use App\Policies\ProductPolicy;
use App\Policies\OrderPolicy;
use App\Policies\UserPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\BrandPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        User::class => UserPolicy::class,
        Category::class => CategoryPolicy::class,
        Brand::class => BrandPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Définir les gates pour les rôles
        $this->defineRoleGates();
        
        // Définir les gates pour les permissions spécifiques
        $this->definePermissionGates();
    }

    /**
     * Définir les gates basés sur les rôles
     */
    private function defineRoleGates(): void
    {
        // Gate pour vérifier si l'utilisateur est administrateur
        Gate::define('is-admin', function (User $user) {
            return $user->isAdmin();
        });

        // Gate pour vérifier si l'utilisateur est un utilisateur normal
        Gate::define('is-user', function (User $user) {
            return $user->isUser();
        });

        // Gate pour vérifier si l'utilisateur peut gérer les produits
        Gate::define('manage-products', function (User $user) {
            return $user->isAdmin();
        });

        // Gate pour vérifier si l'utilisateur peut gérer les commandes
        Gate::define('manage-orders', function (User $user) {
            return $user->isAdmin();
        });

        // Gate pour vérifier si l'utilisateur peut gérer les utilisateurs
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        // Gate pour vérifier si l'utilisateur peut voir le tableau de bord admin
        Gate::define('access-dashboard', function (User $user) {
            return $user->isAdmin();
        });

        // Gate pour vérifier si l'utilisateur peut gérer les catégories
        Gate::define('manage-categories', function (User $user) {
            return $user->isAdmin();
        });

        // Gate pour vérifier si l'utilisateur peut gérer les marques
        Gate::define('manage-brands', function (User $user) {
            return $user->isAdmin();
        });
    }

    /**
     * Définir les gates pour les permissions spécifiques
     */
    private function definePermissionGates(): void
    {
        // Permissions pour les produits
        Gate::define('create-product', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('update-product', function (User $user, Product $product) {
            return $user->isAdmin();
        });

        Gate::define('delete-product', function (User $user, Product $product) {
            return $user->isAdmin();
        });

        // Permissions pour les commandes
        Gate::define('view-order', function (User $user, Order $order) {
            // Admin peut voir toutes les commandes
            if ($user->isAdmin()) {
                return true;
            }
            
            // Utilisateur peut voir ses propres commandes
            return $user->id === $order->user_id;
        });

        Gate::define('update-order-status', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('cancel-order', function (User $user, Order $order) {
            // Admin peut annuler toutes les commandes
            if ($user->isAdmin()) {
                return true;
            }
            
            // Utilisateur peut annuler ses propres commandes en attente
            return $user->id === $order->user_id && $order->status === 'pending';
        });

        // Permissions pour les utilisateurs
        Gate::define('update-user', function (User $user, User $targetUser) {
            // Admin peut modifier tous les utilisateurs
            if ($user->isAdmin()) {
                return true;
            }
            
            // Utilisateur peut modifier son propre profil
            return $user->id === $targetUser->id;
        });

        Gate::define('view-user-profile', function (User $user, User $targetUser) {
            // Admin peut voir tous les profils
            if ($user->isAdmin()) {
                return true;
            }
            
            // Utilisateur peut voir son propre profil
            return $user->id === $targetUser->id;
        });

        Gate::define('delete-user', function (User $user, User $targetUser) {
            // Admin peut supprimer tous les utilisateurs sauf lui-même
            return $user->isAdmin() && $user->id !== $targetUser->id;
        });

        // Permissions pour les catégories
        Gate::define('create-category', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('update-category', function (User $user, Category $category) {
            return $user->isAdmin();
        });

        Gate::define('delete-category', function (User $user, Category $category) {
            return $user->isAdmin();
        });

        // Permissions pour les marques
        Gate::define('create-brand', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('update-brand', function (User $user, Brand $brand) {
            return $user->isAdmin();
        });

        Gate::define('delete-brand', function (User $user, Brand $brand) {
            return $user->isAdmin();
        });
    }
}
