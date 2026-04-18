import { inject } from '@angular/core';
import { CanActivate, Router, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { Observable, of } from 'rxjs';
import { map, take, switchMap } from 'rxjs/operators';

export interface RoleData {
  roles: string[];
  redirectTo?: string;
}

export class RoleGuard implements CanActivate {
  private authService = inject(AuthService);
  private router = inject(Router);

  canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): Observable<boolean> {
    return this.authService.authState$.pipe(
      take(1),
      switchMap(authState => {
        // Si l'utilisateur n'est pas authentifié
        if (!authState.isAuthenticated) {
          this.router.navigate(['/auth/signin'], { 
            queryParams: { returnUrl: state.url } 
          });
          return of(false);
        }

        // Récupérer les rôles requis depuis les données de route
        const requiredRoles = this.getRequiredRoles(route);
        
        // Si aucun rôle n'est requis, autoriser l'accès
        if (!requiredRoles || requiredRoles.length === 0) {
          return of(true);
        }

        // Vérifier si l'utilisateur a le rôle requis
        const user = authState.user;
        if (!user) {
          this.router.navigate(['/auth/signin'], { 
            queryParams: { returnUrl: state.url } 
          });
          return of(false);
        }

        const hasRequiredRole = this.checkUserRole(user.role || 'customer', requiredRoles);
        
        if (hasRequiredRole) {
          return of(true);
        }

        // Rediriger selon le rôle de l'utilisateur
        this.redirectBasedOnUserRole(user.role || 'customer', state.url);
        return of(false);
      })
    );
  }

  private getRequiredRoles(route: ActivatedRouteSnapshot): string[] {
    // Vérifier les données de route directement
    const routeData = route.data['roles'] as string[] | string | undefined;
    
    if (Array.isArray(routeData)) {
      return routeData;
    }
    
    if (typeof routeData === 'string') {
      return [routeData];
    }

    // Vérifier dans les routes parentes
    let parent = route.parent;
    while (parent) {
      const parentData = parent.data['roles'] as string[] | string | undefined;
      
      if (Array.isArray(parentData)) {
        return parentData;
      }
      
      if (typeof parentData === 'string') {
        return [parentData];
      }
      
      parent = parent.parent;
    }

    return [];
  }

  private checkUserRole(userRole: string, requiredRoles: string[]): boolean {
    return requiredRoles.includes(userRole);
  }

  private redirectBasedOnUserRole(userRole: string, attemptedUrl: string): void {
    // Si l'utilisateur est admin mais essaie d'accéder une route client
    if (userRole === 'admin' && attemptedUrl.includes('/orders')) {
      this.router.navigate(['/admin/orders']);
      return;
    }

    // Si l'utilisateur est client mais essaie d'accéder une route admin
    if (userRole === 'customer' && attemptedUrl.includes('/admin')) {
      this.router.navigate(['/catalog']);
      return;
    }

    // Redirection par défaut selon le rôle
    if (userRole === 'admin') {
      this.router.navigate(['/admin/dashboard']);
    } else {
      this.router.navigate(['/catalog']);
    }
  }
}

// Guard spécifique pour les routes admin
export class AdminGuard extends RoleGuard {
  override canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): Observable<boolean> {
    // Forcer les rôles admin pour ce guard
    route.data = { ...route.data, roles: ['admin'] };
    return super.canActivate(route, state);
  }
}

// Guard spécifique pour les routes client
export class CustomerGuard extends RoleGuard {
  override canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): Observable<boolean> {
    // Forcer les rôles client pour ce guard
    route.data = { ...route.data, roles: ['customer'] };
    return super.canActivate(route, state);
  }
}

// Guard pour les routes publiques (invités autorisés)
export class PublicGuard implements CanActivate {
  private authService = inject(AuthService);
  private router = inject(Router);

  canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): Observable<boolean> {
    return this.authService.authState$.pipe(
      take(1),
      map(authState => {
        // Si l'utilisateur est déjà authentifié et essaie d'accéder une page d'auth
        if (authState.isAuthenticated && state.url.includes('/auth')) {
          // Rediriger selon le rôle
          if (authState.user?.role === 'admin') {
            this.router.navigate(['/admin/dashboard']);
          } else {
            this.router.navigate(['/catalog']);
          }
          return false;
        }

        // Autoriser l'accès pour tout le monde
        return true;
      })
    );
  }
}

// Guard pour les routes nécessitant une authentification (invités non autorisés)
export class AuthGuard implements CanActivate {
  private authService = inject(AuthService);
  private router = inject(Router);

  canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): Observable<boolean> {
    return this.authService.authState$.pipe(
      take(1),
      map(authState => {
        if (!authState.isAuthenticated) {
          this.router.navigate(['/auth/signin'], { 
            queryParams: { returnUrl: state.url } 
          });
          return false;
        }
        return true;
      })
    );
  }
}
