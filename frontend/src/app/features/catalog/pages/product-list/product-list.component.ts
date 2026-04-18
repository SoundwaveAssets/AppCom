import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatPaginatorModule } from '@angular/material/paginator';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatCardModule } from '@angular/material/card';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { Subject, debounceTime, distinctUntilChanged } from 'rxjs';

import { CatalogService } from '../../services/catalog.service';
import { Product, Category, Brand, ProductFilters, PaginatedProducts } from '../../../../core/models/catalog.model';
import { ProductCardComponent } from '../../components/product-card/product-card.component';

@Component({
  selector: 'app-product-list',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    CommonModule,
    FormsModule,
    MatInputModule,
    MatButtonModule,
    MatCardModule,
    MatIconModule,
    MatPaginatorModule,
    MatProgressSpinnerModule,
    MatSlideToggleModule,
    MatSelectModule,
    MatCheckboxModule,
    ProductCardComponent
  ],
  templateUrl: './product-list.component.html',
  styleUrls: ['./product-list.component.scss']
})
export class ProductListComponent implements OnInit {
  products: Product[] = [];
  categories: Category[] = [];
  brands: Brand[] = [];
  
  isLoading = true;
  isLoadingMore = false;
  
  pagination: PaginatedProducts['pagination'] = {
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0,
    from: 0,
    to: 0
  };

  filters: ProductFilters = {
    per_page: 20,
    page: 1
  };

  searchSubject = new Subject<string>();

  constructor(
    private catalogService: CatalogService,
    private cdr: ChangeDetectorRef
  ) {
    this.searchSubject.pipe(
      debounceTime(300),
      distinctUntilChanged()
    ).subscribe(searchTerm => {
      this.filters.q = searchTerm;
      this.loadProducts();
    });
  }

  ngOnInit(): void {
    this.loadInitialData();
  }

  private async loadInitialData(): Promise<void> {
    try {
      this.isLoading = true;
      this.cdr.detectChanges();
      
      const [categoriesRes, brandsRes] = await Promise.all([
        this.catalogService.getCategories().toPromise(),
        this.catalogService.getBrands().toPromise()
      ]);

      this.categories = categoriesRes?.data || [];
      this.brands = brandsRes?.data || [];

      await this.loadProducts();
    } catch (error) {
      console.error('Error loading initial data:', error);
    } finally {
      setTimeout(() => {
        this.isLoading = false;
        this.cdr.detectChanges();
      }, 0);
    }
  }

  private async loadProducts(): Promise<void> {
    try {
      const response = await this.catalogService.getProducts(this.filters).toPromise();
      
      if (this.filters.page === 1) {
        this.products = response?.data || [];
      } else {
        this.products = [...this.products, ...(response?.data || [])];
      }

      this.pagination = {
        current_page: response?.current_page || 1,
        last_page: response?.last_page || 1,
        per_page: response?.per_page || 20,
        total: response?.total || 0,
        from: response?.from || 0,
        to: response?.to || 0
      };
      
      this.cdr.detectChanges();
    } catch (error) {
      console.error('Error loading products:', error);
    }
  }

  onSearchChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    this.searchSubject.next(target.value);
  }

  onCategoryChange(categoryId: string): void {
    this.filters.category_id = categoryId ? parseInt(categoryId) : undefined;
    this.filters.page = 1;
    this.loadProducts();
  }

  onBrandChange(brandId: string): void {
    this.filters.brand_id = brandId ? parseInt(brandId) : undefined;
    this.filters.page = 1;
    this.loadProducts();
  }

  onInStockChange(inStock: boolean): void {
    this.filters.in_stock = inStock || undefined;
    this.filters.page = 1;
    this.loadProducts();
  }

  onPriceRangeChange(min: string, max: string): void {
    this.filters.price_min = min ? parseInt(min) : undefined;
    this.filters.price_max = max ? parseInt(max) : undefined;
    this.filters.page = 1;
    this.loadProducts();
  }

  onPageChange(event: any): void {
    this.filters.page = event.pageIndex + 1;
    this.filters.per_page = event.pageSize;
    this.loadProducts();
  }

  loadMoreProducts(): void {
    if (this.pagination.current_page < this.pagination.last_page && !this.isLoadingMore) {
      this.isLoadingMore = true;
      this.filters.page = this.pagination.current_page + 1;
      
      this.loadProducts().finally(() => {
        this.isLoadingMore = false;
      });
    }
  }

  resetFilters(): void {
    this.filters = {
      per_page: 20,
      page: 1
    };
    this.loadProducts();
  }

  onAddToCart(product: Product): void {
    // This will be implemented when we create the cart service
    console.log('Add to cart:', product);
  }
}
