import { Order } from './cart.model';

export interface DashboardStats {
  totalRevenue: number;
  totalOrders: number;
  pendingOrders: number;
  topProducts: Array<{
    id: number;
    name: string;
    total_sold: number;
    revenue: number;
  }>;
  lowStockProducts: Array<{
    id: number;
    name: string;
    stock: number;
  }>;
}

export interface AdminOrder extends Order {
  customer_name?: string;
  customer_email: string;
  payment_status?: string;
}

export interface OrderFilters {
  status?: string;
  date_from?: string;
  date_to?: string;
  search?: string;
  per_page?: number;
  page?: number;
}
