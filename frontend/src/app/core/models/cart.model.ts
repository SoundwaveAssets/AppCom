export interface CartItem {
  product_id: number;
  product_name: string;
  unit_price: number;
  quantity: number;
  product?: any; // Product details for display
}

export interface Cart {
  items: CartItem[];
  total: number;
  items_count: number;
}

export interface ShippingAddress {
  address: string;
  city: string;
  phone: string;
}

export interface OrderItem {
  product_id: number;
  product_name: string;
  unit_price: number;
  quantity: number;
}

export interface Order {
  id: number;
  order_number: string;
  user_id?: number;
  guest_session_id?: string;
  customer_email: string;
  total_amount: number;
  status: 'pending' | 'paid' | 'shipped' | 'delivered' | 'cancelled';
  shipping_address: ShippingAddress;
  items: OrderItem[];
  created_at: string;
  updated_at: string;
}

export interface PaymentIntent {
  order_id: number;
  order_number: string;
  client_secret: string;
  total: number;
}

export interface CartValidationResult {
  valid: boolean;
  cart?: {
    items: CartItem[];
    total: number;
  };
  errors?: string[];
}
