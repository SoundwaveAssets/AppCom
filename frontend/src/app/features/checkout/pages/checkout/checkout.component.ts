import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatStepperModule } from '@angular/material/stepper';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatIconModule } from '@angular/material/icon';

import { CartService } from '../../../cart/services/cart.service';
import { StripeService } from '../../services/stripe.service';
import { CartItem, Cart, ShippingAddress, PaymentIntent } from '../../../../core/models/cart.model';

@Component({
  selector: 'app-checkout',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    MatInputModule,
    MatButtonModule,
    MatCardModule,
    MatStepperModule,
    MatFormFieldModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatIconModule
  ],
  templateUrl: './checkout.component.html',
  styleUrls: ['./checkout.component.scss']
})
export class CheckoutComponent implements OnInit {
  cart$!: Observable<Cart>;
  isLoading = false;
  paymentIntent: PaymentIntent | null = null;

  shippingAddress: ShippingAddress = {
    address: '',
    city: '',
    phone: ''
  };

  addressForm!: FormGroup;
  stepIndex = 0;
  cartStep!: FormGroup;
  addressStep!: FormGroup;
  paymentStep!: FormGroup;

  constructor(
    private cartService: CartService,
    private stripeService: StripeService,
    private router: Router,
    private snackBar: MatSnackBar,
    private fb: FormBuilder
  ) {}

  ngOnInit(): void {
    this.cart$ = this.cartService.cart$;
    
    this.cartStep = this.fb.group({});
    this.addressStep = this.fb.group({});
    this.paymentStep = this.fb.group({});
    
    this.addressForm = this.fb.group({
      address: [''],
      city: [''],
      phone: ['']
    });
    
    if (this.cartService.isEmpty()) {
      this.router.navigate(['/cart']);
      this.snackBar.open('Votre panier est vide', 'Fermer', { duration: 3000 });
    }
  }

  get total(): number {
    return this.cartService.total;
  }

  get itemCount(): number {
    return this.cartService.itemCount;
  }

  async createPaymentIntent(): Promise<void> {
    if (!this.isShippingAddressValid()) {
      this.snackBar.open('Veuillez compléter votre adresse de livraison', 'Fermer', { duration: 3000 });
      return;
    }

    try {
      this.isLoading = true;

      // For now, we'll use a guest session. In a real app, you'd get this from auth service
      const guestSessionId = this.generateGuestSessionId();
      const customerEmail = 'guest@example.com'; // Should come from user input or auth

      const paymentIntent = await this.cartService.createPaymentIntent(
        customerEmail,
        this.shippingAddress,
        guestSessionId
      ).toPromise();

      if (paymentIntent) {
        this.paymentIntent = paymentIntent;
        // In a real implementation, you would now show the Stripe payment element
        // For demo purposes, we'll simulate a successful payment
        this.simulatePayment();
      }
    } catch (error) {
      console.error('Error creating payment intent:', error);
      this.snackBar.open('Erreur lors de la création du paiement', 'Fermer', { duration: 3000 });
    } finally {
      this.isLoading = false;
    }
  }

  private async simulatePayment(): Promise<void> {
    // Simulate payment processing
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    this.snackBar.open('Paiement simulé avec succès!', 'Fermer', { duration: 3000 });
    this.cartService.clearCart();
    this.router.navigate(['/']);
  }

  private isShippingAddressValid(): boolean {
    return !!(this.shippingAddress.address && 
              this.shippingAddress.city && 
              this.shippingAddress.phone);
  }

  private generateGuestSessionId(): string {
    return 'guest_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
  }

  onAddressChange(): void {
    // Address validation can be added here
  }

  goBack(): void {
    this.router.navigate(['/cart']);
  }
}
