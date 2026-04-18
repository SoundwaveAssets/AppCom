import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Product } from '../../../../core/models/catalog.model';
import { CurrencyPipe } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatBadgeModule } from '@angular/material/badge';
import { NO_ERRORS_SCHEMA } from '@angular/core';

@Component({
  selector: 'app-product-card',
  standalone: true,
  imports: [
    CommonModule,
    MatButtonModule,
    MatIconModule,
    MatBadgeModule
  ],
  templateUrl: './product-card.component.html',
  styleUrls: ['./product-card.component.scss'],
  schemas: [NO_ERRORS_SCHEMA]
})
export class ProductCardComponent {
  @Input() product!: Product;
  @Input() showAddToCart = true;
  @Output() addToCart = new EventEmitter<Product>();

  get mainImage(): string {
    return this.product.images?.[0] || '/assets/placeholder-product.jpg';
  }

  get hasPromo(): boolean {
    return !!(this.product.promo_price && this.product.promo_price < this.product.price);
  }

  get isInStock(): boolean {
    return (this.product.stock ?? 0) > 0;
  }

  get isOutOfStock(): boolean {
    return !this.isInStock;
  }

  onAddToCart(event: Event): void {
    event.preventDefault();
    if (this.isInStock) {
      this.addToCart.emit(this.product);
    }
  }

  onImageError(event: Event): void {
    const img = event.target as HTMLImageElement;
    img.src = '/assets/placeholder-product.jpg';
  }

  calculatePromoPercentage(): number {
    if (!this.hasPromo || !this.product.promo_price) return 0;
    return Math.round((1 - this.product.promo_price / this.product.price) * 100);
  }
}
