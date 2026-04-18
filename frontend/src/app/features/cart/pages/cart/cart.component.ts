import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Observable } from 'rxjs';

import { CartService } from '../../services/cart.service';
import { CartItem, Cart } from '../../../../core/models/cart.model';
import { CartItemComponent } from '../../components/cart-item/cart-item.component';

@Component({
  selector: 'app-cart',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    MatIconModule,
    MatButtonModule,
    MatCardModule,
    MatDividerModule,
    MatFormFieldModule,
    MatInputModule,
    CartItemComponent
  ],
  templateUrl: './cart.component.html',
  styleUrls: ['./cart.component.scss']
})
export class CartComponent implements OnInit {
  cart$!: Observable<Cart>;

  constructor(
    private cartService: CartService,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    this.cart$ = this.cartService.cart$;
  }

  onQuantityChange(event: { productId: number; quantity: number }): void {
    this.cartService.updateQuantity(event.productId, event.quantity);
  }

  onRemoveItem(productId: number): void {
    this.cartService.removeFromCart(productId);
    this.snackBar.open('Article retiré du panier', 'Fermer', { duration: 3000 });
  }

  onClearCart(): void {
    this.cartService.clearCart();
    this.snackBar.open('Panier vidé', 'Fermer', { duration: 3000 });
  }

  get itemCount(): number {
    return this.cartService.itemCount;
  }

  get total(): number {
    return this.cartService.total;
  }

  isEmpty(): boolean {
    return this.cartService.isEmpty();
  }

  get subtotal(): number {
    return this.cartService.total;
  }

  get hasPromo(): boolean {
    return false; // Pour l'instant, pas de promo active
  }

  get promoAmount(): number {
    return 0; // Pour l'instant, pas de montant de promo
  }
}
