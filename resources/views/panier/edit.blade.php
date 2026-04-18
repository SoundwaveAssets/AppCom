<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un panier</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 28px;
            font-weight: bold;
        }
        .auth-buttons {
            display: flex;
            gap: 15px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        .btn-secondary:hover {
            background-color: white;
            color: #667eea;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            color: #333;
        }
        .panier-summary {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .product-info {
            margin-bottom: 20px;
        }
        .product-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }
        .product-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .product-price {
            color: #28a745;
            font-weight: bold;
        }
        .edit-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102,126,234,0.3);
        }
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40,167,69,0.4);
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        .error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .stock-info {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .total-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
        }
        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>__ Modifier panier</h1>
            <div class="auth-buttons">
                @if(auth()->check())
                    <span>Bonjour, {{ auth()->user()->name }}!</span>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Tableau de bord</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Se déconnecter</button>
                    </form>
                @else
                    <a href="{{ route('login.form') }}" class="btn btn-secondary">Connexion</a>
                    <a href="{{ route('register.form') }}" class="btn btn-primary">Inscription</a>
                @endif
            </div>
        </div>
    </header>

    <div class="container">
        <a href="{{ route('panier.index') }}" class="back-link">« Retour à mes paniers</a>
        
        <h2 class="page-title">_ Modifier le panier</h2>
        
        @if(session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        <div class="panier-summary">
            <div class="product-info">
                <div class="product-name">{{ $panier->product_name }}</div>
                @if($panier->product_brand)
                    <div class="product-details">
                        <span>Marque:</span>
                        <span>{{ $panier->product_brand }}</span>
                    </div>
                @endif
                <div class="product-details">
                    <span>Prix unitaire:</span>
                    <span class="product-price">{{ number_format($panier->product_price, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="product-details">
                    <span>Stock disponible:</span>
                    <span>{{ $panier->product_stock + $panier->quantity }}</span>
                </div>
                <div class="product-details">
                    <span>Type client:</span>
                    <span>{{ $panier->client_type }}</span>
                </div>
                <div class="product-details">
                    <span>Date création:</span>
                    <span>{{ $panier->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <form action="{{ route('panier.update', $panier->id) }}" method="POST" class="edit-form">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="quantity">Quantité:</label>
                <input type="number" 
                       id="quantity" 
                       name="quantity" 
                       value="{{ old('quantity', $panier->quantity) }}" 
                       min="1" 
                       max="{{ $panier->product_stock + $panier->quantity }}" 
                       required>
                <div class="stock-info">
                    Stock disponible: {{ $panier->product_stock + $panier->quantity }} unités
                </div>
                @error('quantity')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="payment_mode">Mode de paiement:</label>
                <select id="payment_mode" name="payment_mode" required>
                    <option value="">Sélectionner un mode de paiement</option>
                    <option value="carte" {{ old('payment_mode', $panier->payment_mode) == 'carte' ? 'selected' : '' }}>Carte bancaire</option>
                    <option value="mobile" {{ old('payment_mode', $panier->payment_mode) == 'mobile' ? 'selected' : '' }}>Mobile money</option>
                    <option value="espece" {{ old('payment_mode', $panier->payment_mode) == 'espece' ? 'selected' : '' }}>Espèces</option>
                </select>
                @error('payment_mode')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="card_number">Numéro de carte (16 chiffres):</label>
                <input type="text" 
                       id="card_number" 
                       name="card_number" 
                       value="{{ old('card_number', $panier->card_number) }}" 
                       placeholder="1234 5678 9012 3456" 
                       maxlength="19" 
                       pattern="[0-9\s]{16,19}" 
                       required>
                <div style="color: #666; font-size: 12px; margin-top: 5px;">Entrez 16 chiffres sans espaces</div>
                @error('card_number')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="total-info">
                <div class="total-amount">
                    Total: <span id="total-amount">{{ number_format($panier->product_price * $panier->quantity, 0, ',', ' ') }}</span> FCFA
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">_ Mettre à jour</button>
                <a href="{{ route('panier.index') }}" class="btn-cancel">Annuler</a>
            </div>
        </form>
    </div>

    <script>
        // Formater le numéro de carte
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });

        // Calculer le total en temps réel
        document.getElementById('quantity').addEventListener('input', function() {
            const price = {{ $panier->product_price }};
            const quantity = parseInt(this.value) || 0;
            const total = price * quantity;
            document.getElementById('total-amount').textContent = total.toLocaleString('fr-FR');
        });

        // Valider la quantité
        document.getElementById('quantity').addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            const value = parseInt(this.value);
            
            if (value > max) {
                this.value = max;
                alert('La quantité ne peut pas dépasser le stock disponible (' + max + ')');
            }
            
            if (value < 1) {
                this.value = 1;
            }
        });
    </script>
</body>
</html>
