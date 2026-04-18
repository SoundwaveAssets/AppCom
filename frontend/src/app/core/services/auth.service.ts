import { Injectable, signal, computed, inject } from '@angular/core';
import { Auth, signInWithEmailAndPassword, createUserWithEmailAndPassword, signOut, onAuthStateChanged, User as FirebaseUser } from '@angular/fire/auth';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { toObservable, takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { BehaviorSubject, combineLatest, of } from 'rxjs';
import { map, switchMap, catchError, startWith } from 'rxjs/operators';
import { User, AuthState } from '../models/user.model';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly auth = inject(Auth);
  private readonly router = inject(Router);
  private readonly snackBar = inject(MatSnackBar);

  // Signaux pour l'état d'authentification
  private readonly user = signal<User | null>(null);
  private readonly isLoading = signal(true);
  private readonly userIsAuthenticated = computed(() => this.user() !== null);

  // Signaux exposés publiquement
  readonly user$ = toObservable(this.user);
  readonly isLoading$ = toObservable(this.isLoading);
  readonly isAuthenticated$ = toObservable(this.userIsAuthenticated);

  // Signal combiné pour l'état complet
  readonly authState = computed(() => ({
    user: this.user(),
    isLoading: this.isLoading(),
    isAuthenticated: this.userIsAuthenticated()
  }));

  // Observable pour les composants qui préfèrent RxJS
  readonly authState$ = toObservable(this.authState);

  constructor() {
    this.initializeAuthState();
  }

  private initializeAuthState(): void {
    onAuthStateChanged(this.auth, async (firebaseUser: FirebaseUser | null) => {
      this.isLoading.set(true);
      
      if (firebaseUser) {
        try {
          const user = await this.getUserData(firebaseUser);
          this.user.set(user);
          this.isLoading.set(false);
        } catch (error) {
          console.error('Error fetching user data:', error);
          this.user.set(null);
          this.isLoading.set(false);
        }
      } else {
        this.user.set(null);
        this.isLoading.set(false);
      }
    });
  }

  private async getUserData(firebaseUser: FirebaseUser): Promise<User> {
    const tokenResult = await firebaseUser.getIdTokenResult();
    const claims = tokenResult.claims;

    return {
      id: firebaseUser.uid,
      email: firebaseUser.email!,
      displayName: firebaseUser.displayName || undefined,
      role: (claims['role'] as 'admin' | 'customer') || 'customer',
      customerId: (claims['customerId'] as string) || undefined,
      createdAt: firebaseUser.metadata.creationTime!
    };
  }

  async signIn(email: string, password: string): Promise<void> {
    try {
      await signInWithEmailAndPassword(this.auth, email, password);
      this.snackBar.open('Connexion réussie', 'Fermer', { duration: 3000 });
      this.router.navigate(['/']);
    } catch (error: any) {
      let message = 'Erreur de connexion';
      if (error.code === 'auth/user-not-found') {
        message = 'Utilisateur non trouvé';
      } else if (error.code === 'auth/wrong-password') {
        message = 'Mot de passe incorrect';
      }
      this.snackBar.open(message, 'Fermer', { duration: 3000 });
      throw error;
    }
  }

  async signUp(email: string, password: string, displayName?: string): Promise<void> {
    try {
      const result = await createUserWithEmailAndPassword(this.auth, email, password);
      
      if (displayName) {
        await import('firebase/auth').then(({ updateProfile }) => {
          return updateProfile(result.user, { displayName });
        });
      }

      this.snackBar.open('Compte créé avec succès', 'Fermer', { duration: 3000 });
      this.router.navigate(['/']);
    } catch (error: any) {
      let message = 'Erreur lors de la création du compte';
      if (error.code === 'auth/email-already-in-use') {
        message = 'Cet email est déjà utilisé';
      } else if (error.code === 'auth/weak-password') {
        message = 'Le mot de passe est trop faible';
      }
      this.snackBar.open(message, 'Fermer', { duration: 3000 });
      throw error;
    }
  }

  async signOut(): Promise<void> {
    try {
      await signOut(this.auth);
      this.snackBar.open('Déconnexion réussie', 'Fermer', { duration: 3000 });
      this.router.navigate(['/']);
    } catch (error) {
      console.error('Error signing out:', error);
      this.snackBar.open('Erreur lors de la déconnexion', 'Fermer', { duration: 3000 });
    }
  }

  get currentUser(): User | null {
    return this.user();
  }

  get isAuthenticated(): boolean {
    return this.userIsAuthenticated();
  }

  get isAdmin(): boolean {
    return this.user()?.role === 'admin';
  }

  async getIdToken(): Promise<string | null> {
    const user = this.auth.currentUser;
    return user ? await user.getIdToken() : null;
  }
}
