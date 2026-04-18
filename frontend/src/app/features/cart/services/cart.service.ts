import { Injectable, signal, computed, inject } from '@angular/core';
import { toObservable } from '@angular/core/rxjs-interop';
import { Observable, of } from 'rxjs';
import { map, catchError, startWith, shareReplay } from 'rxjs/operators';
import { ApiService } from '../../../core/services/api.service';
import { Cart, CartItem, CartValidationResult, ShippingAddress, PaymentIntent } from '../../../core/models/cart.model';
import { Product } from '../../../core/models/catalog.model';

@Injectable({
  providedIn: 'root'
})
export class CartService {
  private readonly apiService = inject(ApiService);

  // Signaux pour l'état du panier
  private readonly items = signal<CartItem[]>([]);
  private readonly cartTotal = computed(() => 
    this.items().reduce((sum, item) => sum + (item.unit_price * item.quantity), 0)
  );
  private readonly itemsCount = computed(() => 
    this.items().reduce((sum, item) => sum + item.quantity, 0)
  );

  // Signal combiné pour l'état complet du panier
  readonly cart = computed(() => ({
    items: this.items(),
    total: this.cartTotal(),
    items_count: this.itemsCount()
  }));

  // Observables pour les composants qui préfèrent RxJS
  readonly cart$ = toObservable(this.cart);
  readonly items$ = toObservable(this.items);
  readonly total$ = toObservable(this.cartTotal);
  readonly itemsCount$ = toObservable(this.itemsCount);

  constructor() {
    this.loadCartFromStorage();
  }

  private loadCartFromStorage(): void {
    try {
      // Vérifier si localStorage est disponible (SSR safe)
      if (typeof localStorage !== 'undefined') {
        const savedCart = localStorage.getItem('cart');
        if (savedCart) {
          const cart: Cart = JSON.parse(savedCart);
          this.items.set(cart.items || []);
        }
      }
    } catch (error) {
      console.warn('Error loading cart from storage:', error);
    }
  }

  private saveCartToStorage(): void {
    const cart = this.cart();
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('cart', JSON.stringify(cart));
    }
  }

  addToCart(product: Product, quantity: number = 1): void {
    const currentItems = this.items();
    const existingItemIndex = currentItems.findIndex(item => item.product_id === product.id);

    let updatedItems: CartItem[];

    if (existingItemIndex >= 0) {
      // Update existing item
      updatedItems = [...currentItems];
      updatedItems[existingItemIndex].quantity += quantity;
    } else {
      // Add new item
      const newItem: CartItem = {
        product_id: product.id,
        product_name: product.name,
        unit_price: product.promo_price || product.price,
        quantity,
        product
      };
      updatedItems = [...currentItems, newItem];
    }

    this.items.set(updatedItems);
    this.saveCartToStorage();
  }

  updateQuantity(productId: number, quantity: number): void {
    if (quantity <= 0) {
      this.removeFromCart(productId);
      return;
    }

    const currentItems = this.items();
    const updatedItems = currentItems.map(item =>
      item.product_id === productId ? { ...item, quantity } : item
    );

    this.items.set(updatedItems);
    this.saveCartToStorage();
  }

  removeFromCart(productId: number): void {
    const currentItems = this.items();
    const updatedItems = currentItems.filter(item => item.product_id !== productId);

    this.items.set(updatedItems);
    this.saveCartToStorage();
  }

  clearCart(): void {
    this.items.set([]);
    localStorage.removeItem('cart');
  }

  validateCart(): Observable<CartValidationResult> {
    const cart = this.cart();
    const items = cart.items.map(({ product, ...item }) => item);

    return this.apiService.post<CartValidationResult>('/checkout/validate-cart', { items });
  }

  createPaymentIntent(
    customerEmail: string,
    shippingAddress: ShippingAddress,
    guestSessionId?: string
  ): Observable<PaymentIntent> {
    const cart = this.cart();
    const items = cart.items.map(({ product, ...item }) => item);

    const payload = {
      items,
      customer_email: customerEmail,
      guest_session_id: guestSessionId,
      shipping_address: shippingAddress
    };

    return this.apiService.post<PaymentIntent>('/checkout/payment-intent', payload);
  }

  get currentCart(): Cart {
    return this.cart();
  }

  get itemCount(): number {
    return this.itemsCount();
  }

  get total(): number {
    return this.cartTotal();
  }

  isEmpty(): boolean {
    return this.items().length === 0;
  }
}
