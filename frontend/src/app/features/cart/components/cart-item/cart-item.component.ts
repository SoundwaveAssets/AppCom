import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { CartItem } from '../../../../core/models/cart.model';

@Component({
  selector: 'app-cart-item',
  standalone: true,
  imports: [
    CommonModule,
    MatIconModule,
    MatButtonModule,
    MatInputModule,
    MatFormFieldModule
  ],
  templateUrl: './cart-item.component.html',
  styleUrls: ['./cart-item.component.scss']
})
export class CartItemComponent {
  @Input() item!: CartItem;
  @Output() quantityChange = new EventEmitter<{ productId: number; quantity: number }>();
  @Output() remove = new EventEmitter<number>();

  onQuantityChange(newQuantity: number): void {
    if (newQuantity > 0) {
      this.quantityChange.emit({
        productId: this.item.product_id,
        quantity: newQuantity
      });
    }
  }

  onRemove(): void {
    this.remove.emit(this.item.product_id);
  }

  get itemTotal(): number {
    return this.item.unit_price * this.item.quantity;
  }
}
