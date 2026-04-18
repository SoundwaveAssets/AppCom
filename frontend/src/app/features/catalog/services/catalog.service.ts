import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from '../../../core/services/api.service';
import { Category, Brand, Product, ProductFilters, PaginatedProducts, ProductDetail } from '../../../core/models/catalog.model';

@Injectable({
  providedIn: 'root'
})
export class CatalogService {
  constructor(private apiService: ApiService) {}

  getCategories(): Observable<{ data: Category[] }> {
    return this.apiService.get<{ data: Category[] }>('/catalog/categories');
  }

  getBrands(): Observable<{ data: Brand[] }> {
    return this.apiService.get<{ data: Brand[] }>('/catalog/brands');
  }

  getProducts(filters?: ProductFilters): Observable<PaginatedProducts> {
    return this.apiService.get<PaginatedProducts>('/catalog/products', filters);
  }

  getProduct(id: number): Observable<ProductDetail> {
    return this.apiService.get<ProductDetail>(`/catalog/products/${id}`);
  }

  searchProducts(query: string, filters?: Omit<ProductFilters, 'q'>): Observable<PaginatedProducts> {
    return this.getProducts({ ...filters, q: query });
  }
}
