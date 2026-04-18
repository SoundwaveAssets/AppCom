<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     * Admin peut voir toutes les commandes, utilisateur ne peut voir que ses commandes
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can view the model.
     * Admin peut voir toutes les commandes, utilisateur ne peut voir que ses commandes
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->id === $order->user_id;
    }

    /**
     * Determine whether the user can create models.
     * Les utilisateurs connectés peuvent créer des commandes
     */
    public function create(User $user): bool
    {
        return $user->isUser() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * Admin peut modifier toutes les commandes, utilisateur ne peut modifier que le statut de ses commandes
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->id === $order->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Admin peut supprimer toutes les commandes, utilisateur peut annuler ses propres commandes
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin() || ($user->id === $order->user_id && $order->status === 'pending');
    }

    /**
     * Determine whether the user can restore the model.
     * Uniquement les administrateurs peuvent restaurer des commandes
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Uniquement les administrateurs peuvent supprimer définitivement des commandes
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
