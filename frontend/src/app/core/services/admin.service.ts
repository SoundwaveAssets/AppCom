import { Injectable, signal, computed, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';

export interface DashboardStats {
  totalRevenue: number;
  totalOrders: number;
  totalProducts: number;
  totalUsers: number;
  pendingOrders: number;
  processingOrders: number;
  completedOrders: number;
  averageOrderValue: number;
  conversionRate: number;
  growthRate: {
    revenue: number;
    orders: number;
    products: number;
    users: number;
  };
}

export interface RecentOrder {
  id: number;
  order_number: string;
  customer_email: string;
  total_amount: number;
  status: string;
  created_at: string;
  customer_name?: string;
}

export interface TopProduct {
  id: number;
  name: string;
  total_sales: number;
  sales: number; // Ajout de la propriété sales pour compatibilité avec le template
  revenue: number;
  stock: number;
  image?: string;
}

export interface UserStats {
  id: number;
  name: string;
  email: string;
  role: string;
  orders_count: number;
  total_spent: number;
  created_at: string;
}

@Injectable({
  providedIn: 'root'
})
export class AdminService {
  private http = inject(HttpClient);
  private readonly apiUrl = environment.api.baseUrl;

  // État du service
  private readonly isLoadingSignal = signal(false);
  private readonly dashboardStatsSignal = signal<DashboardStats | null>(null);
  private readonly recentOrdersSignal = signal<RecentOrder[]>([]);
  private readonly topProductsSignal = signal<TopProduct[]>([]);
  private readonly userStatsSignal = signal<UserStats[]>([]);

  // Signaux exposés publiquement
  readonly isLoading$ = computed(() => this.isLoadingSignal());
  readonly dashboardStats$ = computed(() => this.dashboardStatsSignal());
  readonly recentOrders$ = computed(() => this.recentOrdersSignal());
  readonly topProducts$ = computed(() => this.topProductsSignal());
  readonly userStats$ = computed(() => this.userStatsSignal());

  // Getters pour compatibilité
  get isLoading() { return this.isLoadingSignal; }
  get dashboardStats() { return this.dashboardStatsSignal; }
  get recentOrders() { return this.recentOrdersSignal; }
  get topProducts() { return this.topProductsSignal; }
  get userStats() { return this.userStatsSignal; }

  /**
   * Charger les statistiques du tableau de bord
   */
  loadDashboardStats(): Observable<DashboardStats> {
    this.isLoadingSignal.set(true);
    
    return this.http.get<DashboardStats>(`${this.apiUrl}/admin/dashboard/stats`).pipe(
      tap(stats => {
        this.dashboardStatsSignal.set(stats);
        this.isLoadingSignal.set(false);
      }),
      catchError(error => {
        console.error('Erreur lors du chargement des statistiques:', error);
        this.isLoadingSignal.set(false);
        // Retourner des statistiques par défaut en cas d'erreur
        const defaultStats: DashboardStats = {
          totalRevenue: 0,
          totalOrders: 0,
          totalProducts: 0,
          totalUsers: 0,
          pendingOrders: 0,
          processingOrders: 0,
          completedOrders: 0,
          averageOrderValue: 0,
          conversionRate: 0,
          growthRate: {
            revenue: 0,
            orders: 0,
            products: 0,
            users: 0
          }
        };
        this.dashboardStatsSignal.set(defaultStats);
        return of(defaultStats);
      })
    );
  }

  /**
   * Charger les commandes récentes
   */
  loadRecentOrders(limit: number = 10): Observable<RecentOrder[]> {
    const params = new HttpParams().set('limit', limit.toString());
    
    return this.http.get<RecentOrder[]>(`${this.apiUrl}/admin/orders/recent`, { params }).pipe(
      tap(orders => {
        this.recentOrdersSignal.set(orders);
      }),
      catchError(error => {
        console.error('Erreur lors du chargement des commandes récentes:', error);
        this.recentOrdersSignal.set([]);
        return of([]);
      })
    );
  }

  /**
   * Charger les produits les plus vendus
   */
  loadTopProducts(limit: number = 5): Observable<TopProduct[]> {
    const params = new HttpParams().set('limit', limit.toString());
    
    return this.http.get<TopProduct[]>(`${this.apiUrl}/admin/products/top-selling`, { params }).pipe(
      tap(products => {
        this.topProductsSignal.set(products);
      }),
      catchError(error => {
        console.error('Erreur lors du chargement des produits populaires:', error);
        this.topProductsSignal.set([]);
        return of([]);
      })
    );
  }

  /**
   * Charger les statistiques des utilisateurs
   */
  loadUserStats(limit: number = 10): Observable<UserStats[]> {
    const params = new HttpParams().set('limit', limit.toString());
    
    return this.http.get<UserStats[]>(`${this.apiUrl}/admin/users/stats`, { params }).pipe(
      tap(users => {
        this.userStatsSignal.set(users);
      }),
      catchError(error => {
        console.error('Erreur lors du chargement des statistiques utilisateurs:', error);
        this.userStatsSignal.set([]);
        return of([]);
      })
    );
  }

  /**
   * Charger toutes les données du tableau de bord
   */
  loadAllDashboardData(): Observable<void> {
    this.isLoading.set(true);
    
    return new Observable(observer => {
      Promise.all([
        this.loadDashboardStats().toPromise(),
        this.loadRecentOrders().toPromise(),
        this.loadTopProducts().toPromise(),
        this.loadUserStats().toPromise()
      ]).then(() => {
        this.isLoadingSignal.set(false);
        observer.next();
        observer.complete();
      }).catch(error => {
        console.error('Erreur lors du chargement des données du tableau de bord:', error);
        this.isLoadingSignal.set(false);
        observer.error(error);
      });
    });
  }

  /**
   * Rafraîchir les données du tableau de bord
   */
  refreshDashboardData(): void {
    this.loadAllDashboardData().subscribe();
  }

  /**
   * Obtenir les statistiques de vente par période
   */
  getSalesStats(period: 'day' | 'week' | 'month' | 'year' = 'month'): Observable<any> {
    return this.http.get(`${this.apiUrl}/admin/dashboard/sales-stats`, {
      params: { period }
    });
  }

  /**
   * Obtenir les statistiques de produits par catégorie
   */
  getCategoryStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/admin/dashboard/category-stats`);
  }

  /**
   * Obtenir les tendances des commandes
   */
  getOrderTrends(days: number = 30): Observable<any> {
    return this.http.get(`${this.apiUrl}/admin/dashboard/order-trends`, {
      params: { days: days.toString() }
    });
  }

  /**
   * Exporter les données du tableau de bord
   */
  exportDashboardData(format: 'csv' | 'excel' = 'csv'): Observable<Blob> {
    return this.http.get(`${this.apiUrl}/admin/dashboard/export`, {
      params: { format },
      responseType: 'blob'
    });
  }

  /**
   * Réinitialiser les données du service
   */
  resetData(): void {
    this.dashboardStatsSignal.set(null);
    this.recentOrdersSignal.set([]);
    this.topProductsSignal.set([]);
    this.userStatsSignal.set([]);
    this.isLoadingSignal.set(false);
  }
}
