import { Component, computed, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, Router } from '@angular/router';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatBadgeModule } from '@angular/material/badge';
import { MatDividerModule } from '@angular/material/divider';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-role-based-header',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    MatToolbarModule,
    MatButtonModule,
    MatIconModule,
    MatMenuModule,
    MatBadgeModule,
    MatDividerModule
  ],
  templateUrl: './role-based-header.component.html',
  styleUrls: ['./role-based-header.component.scss']
})
export class RoleBasedHeaderComponent {
  private authService = inject(AuthService);
  private router = inject(Router);

  // Signaux calculés pour l'état d'authentification
  readonly currentUser = computed(() => this.authService.currentUser);
  readonly isAuthenticated = computed(() => this.authService.isAuthenticated);
  readonly isAdmin = computed(() => this.authService.isAdmin);

  // Navigation selon le rôle
  readonly navigationItems = computed(() => {
    const isAdmin = this.isAdmin();
    
    if (isAdmin) {
      return [
        { label: 'Tableau de bord', route: '/admin/dashboard', icon: 'dashboard' },
        { label: 'Produits', route: '/admin/products', icon: 'inventory_2' },
        { label: 'Commandes', route: '/admin/orders', icon: 'shopping_cart' },
        { label: 'Catégories', route: '/admin/categories', icon: 'category' },
        { label: 'Marques', route: '/admin/brands', icon: 'branding_watermark' },
        { label: 'Utilisateurs', route: '/admin/users', icon: 'people' }
      ];
    } else {
      return [
        { label: 'Accueil', route: '/', icon: 'home' },
        { label: 'Catalogue', route: '/catalog', icon: 'shopping_bag' },
        { label: 'Mes commandes', route: '/orders', icon: 'receipt_long' },
        { label: 'Profil', route: '/profile', icon: 'account_circle' }
      ];
    }
  });

  readonly quickActions = computed(() => {
    const isAdmin = this.isAdmin();
    
    if (isAdmin) {
      return [
        { label: 'Ajouter un produit', route: '/admin/products/new', icon: 'add', color: 'primary' },
        { label: 'Voir les statistiques', route: '/admin/dashboard', icon: 'analytics', color: 'accent' }
      ];
    } else {
      return [
        { label: 'Mon panier', route: '/cart', icon: 'shopping_cart', color: 'primary' },
        { label: 'Favoris', route: '/favorites', icon: 'favorite', color: 'accent' }
      ];
    }
  });

  async signOut(): Promise<void> {
    await this.authService.signOut();
  }

  navigateToProfile(): void {
    const user = this.currentUser();
    if (user) {
      this.router.navigate(['/profile']);
    }
  }

  navigateToSettings(): void {
    const user = this.currentUser();
    if (user) {
      this.router.navigate(['/settings']);
    }
  }

  // Informations utilisateur pour l'affichage
  readonly userDisplayName = computed(() => {
    const user = this.currentUser();
    return user?.displayName || user?.email || 'Utilisateur';
  });

  readonly userInitials = computed(() => {
    const displayName = this.userDisplayName();
    const names = displayName.split(' ');
    if (names.length >= 2) {
      return names[0][0] + names[names.length - 1][0];
    }
    return displayName.substring(0, 2).toUpperCase();
  });

  readonly userRoleLabel = computed(() => {
    const user = this.currentUser();
    return user?.role === 'admin' ? 'Administrateur' : 'Client';
  });

  readonly userRoleColor = computed(() => {
    const user = this.currentUser();
    return user?.role === 'admin' ? 'warn' : 'primary';
  });
}
