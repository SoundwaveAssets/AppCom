export interface User {
  id: string;
  email: string;
  displayName?: string;
  role?: 'admin' | 'customer';
  customerId?: string;
  createdAt: string;
}

export interface AuthState {
  user: User | null;
  isLoading: boolean;
  isAuthenticated: boolean;
}
