import { Component, OnInit, computed, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatChipsModule } from '@angular/material/chips';
import { MatTableModule } from '@angular/material/table';
import { MatTooltipModule } from '@angular/material/tooltip';
import { DashboardStats, AdminService } from '../../../../core/services/admin.service';
import { AuthService } from '../../../../core/services/auth.service';

@Component({
  selector: 'app-admin-dashboard',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatChipsModule,
    MatTableModule,
    MatTooltipModule
  ],
  templateUrl: './admin-dashboard.component.html',
  styleUrls: ['./admin-dashboard.component.scss']
})
export class AdminDashboardComponent implements OnInit {
  private adminService = inject(AdminService);
  private authService = inject(AuthService);
  private router = inject(Router);

  // État du composant
  readonly isLoading = computed(() => this.adminService.isLoading$());
  readonly stats = computed(() => this.adminService.dashboardStats$());
  readonly recentOrders = computed(() => this.adminService.recentOrders$());
  readonly topProducts = computed(() => this.adminService.topProducts$());
  readonly currentUser = computed(() => this.authService.currentUser);

  // Configuration du tableau des commandes récentes
  readonly displayedColumns = ['order_number', 'customer', 'total', 'status', 'created_at', 'actions'];

  ngOnInit(): void {
    this.loadDashboardData();
  }

  private loadDashboardData(): void {
    this.adminService.loadDashboardStats().subscribe({
      error: (error: any) => {
        console.error('Erreur lors du chargement du tableau de bord:', error);
      }
    });
  }

  // Navigation vers les différentes sections
  navigateToProducts(): void {
    this.router.navigate(['/admin/products']);
  }

  navigateToOrders(): void {
    this.router.navigate(['/admin/orders']);
  }

  navigateToUsers(): void {
    this.router.navigate(['/admin/users']);
  }

  navigateToCategories(): void {
    this.router.navigate(['/admin/categories']);
  }

  navigateToBrands(): void {
    this.router.navigate(['/admin/brands']);
  }

  // Actions sur les commandes
  viewOrderDetails(orderId: number): void {
    this.router.navigate(['/admin/orders', orderId]);
  }

  updateOrderStatus(orderId: number): void {
    this.router.navigate(['/admin/orders', orderId, 'edit']);
  }

  // Statistiques formatées
  get formattedStats() {
    const statsValue = this.stats();
    if (!statsValue) return null;

    return {
      totalRevenue: this.formatCurrency(statsValue.totalRevenue),
      totalOrders: statsValue.totalOrders.toLocaleString('fr-FR'),
      totalProducts: statsValue.totalProducts.toLocaleString('fr-FR'),
      totalUsers: statsValue.totalUsers.toLocaleString('fr-FR'),
      pendingOrders: statsValue.pendingOrders.toLocaleString('fr-FR'),
      processingOrders: statsValue.processingOrders.toLocaleString('fr-FR'),
      completedOrders: statsValue.completedOrders.toLocaleString('fr-FR'),
      averageOrderValue: this.formatCurrency(statsValue.averageOrderValue),
      conversionRate: `${statsValue.conversionRate.toFixed(1)}%`,
      growthRate: {
        revenue: statsValue.growthRate.revenue > 0 ? `+${statsValue.growthRate.revenue.toFixed(1)}%` : `${statsValue.growthRate.revenue.toFixed(1)}%`,
        orders: statsValue.growthRate.orders > 0 ? `+${statsValue.growthRate.orders.toFixed(1)}%` : `${statsValue.growthRate.orders.toFixed(1)}%`,
        products: statsValue.growthRate.products > 0 ? `+${statsValue.growthRate.products.toFixed(1)}%` : `${statsValue.growthRate.products.toFixed(1)}%`,
        users: statsValue.growthRate.users > 0 ? `+${statsValue.growthRate.users.toFixed(1)}%` : `${statsValue.growthRate.users.toFixed(1)}%`
      }
    };
  }

  private formatCurrency(amount: number): string {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'XAF',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount);
  }

  // Actions rapides
  refreshData(): void {
    this.loadDashboardData();
  }

  createNewProduct(): void {
    this.router.navigate(['/admin/products/new']);
  }

  viewAllOrders(): void {
    this.navigateToOrders();
  }

  // Gestion des statuts de commande
  getStatusColor(status: string): string {
    const statusColors: Record<string, string> = {
      'pending_payment': 'warn',
      'paid': 'primary',
      'processing': 'accent',
      'shipped': 'success',
      'delivered': 'success',
      'cancelled': 'error',
      'payment_failed': 'error'
    };
    return statusColors[status] || 'default';
  }

  getStatusLabel(status: string): string {
    const statusLabels: Record<string, string> = {
      'pending_payment': 'En attente de paiement',
      'paid': 'Payée',
      'processing': 'En cours de traitement',
      'shipped': 'Expédiée',
      'delivered': 'Livrée',
      'cancelled': 'Annulée',
      'payment_failed': 'Paiement échoué'
    };
    return statusLabels[status] || status;
  }

  // Informations utilisateur
  get userDisplayName(): string {
    const user = this.currentUser();
    return user?.displayName || user?.email || 'Administrateur';
  }

  get userInitials(): string {
    const displayName = this.userDisplayName;
    const names = displayName.split(' ');
    if (names.length >= 2) {
      return names[0][0] + names[names.length - 1][0];
    }
    return displayName.substring(0, 2).toUpperCase();
  }

  // Vérification des permissions
  get canManageUsers(): boolean {
    return this.authService.isAdmin;
  }

  get canViewAnalytics(): boolean {
    return this.authService.isAdmin;
  }
}
