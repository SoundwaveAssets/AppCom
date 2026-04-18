export interface Category {
  id: number;
  parent_id?: number;
  name: string;
  slug: string;
  description?: string;
  is_active: boolean;
  children?: Category[];
}

export interface Brand {
  id: number;
  name: string;
  slug: string;
  description?: string;
  logo?: string;
  is_active: boolean;
}

export interface Product {
  id: number;
  category_id: number;
  brand_id: number;
  name: string;
  slug: string;
  description: string;
  price: number;
  promo_price?: number;
  weight: number;
  stock: number;
  images: string[]; // Tableau d'images en base64
  technical_specs: Record<string, string>;
  is_active: boolean;
  is_new: boolean;
  is_featured: boolean;
  category: Category;
  brand: Brand;
  created_at: string;
  updated_at: string;
}

// Interface pour le téléchargement d'images
export interface ImageUpload {
  base64: string;
  filename: string;
  mimeType: string;
  size: number;
}

// Interface pour la conversion d'images
export interface ImageConversion {
  original: File;
  base64: string;
  thumbnail?: string;
  compressed?: string;
}

export interface ProductFilters {
  q?: string;
  category_id?: number;
  brand_id?: number;
  in_stock?: boolean;
  price_min?: number;
  price_max?: number;
  per_page?: number;
  page?: number;
}

export interface PaginatedProducts {
  data: Product[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  };
}

export interface ProductDetail {
  product: Product;
  similar_products: Product[];
}
