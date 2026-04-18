import { Injectable } from '@angular/core';
import { PaymentIntent } from '../../../core/models/cart.model';

@Injectable({
  providedIn: 'root'
})
export class StripeService {
  constructor() {}

  async confirmPayment(clientSecret: string): Promise<{ paymentIntent?: any; error?: any }> {
    // Simulation pour le développement
    console.log('Payment confirmation simulated with clientSecret:', clientSecret);
    
    // Simuler un délai de traitement
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    // Simuler un paiement réussi
    return { 
      paymentIntent: { 
        id: 'pi_test_' + Date.now(),
        status: 'succeeded'
      }
    };
  }

  async createPaymentElement(clientSecret: string, containerSelector: string): Promise<void> {
    // Simulation pour le développement
    console.log('Payment element creation simulated');
    
    // Créer un élément de simulation
    const container = document.querySelector(containerSelector);
    if (container) {
      container.innerHTML = `
        <div style="padding: 20px; border: 2px dashed #ccc; text-align: center;">
          <h4>Simulation de paiement Stripe</h4>
          <p>En production, l'interface Stripe s'afficherait ici</p>
          <button style="padding: 10px 20px; background: #1976d2; color: white; border: none; border-radius: 4px;">
            Payer maintenant (Simulation)
          </button>
        </div>
      `;
    }
  }
}
