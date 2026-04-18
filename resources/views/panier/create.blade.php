<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un panier</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        .panier-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            color: #333;
        }
        .products-summary {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .product-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            gap: 20px;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
        .product-info {
            flex: 1;
        }
        .product-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .product-price {
            color: #28a745;
            font-weight: bold;
        }
        .product-brand {
            color: #666;
            font-size: 14px;
        }
        .quantity-input {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
        }
        .payment-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
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
        @media (max-width: 768px) {
            .product-item {
                flex-direction: column;
                text-align: center;
            }
            .product-image {
                margin: 0 auto;
            }
            .quantity-input {
                margin: 10px auto;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>__ Panier</h1>
            <div class="auth-buttons">
                @if(auth()->check())
                    <span>Bonjour, {{ auth()->user()->name }}!</span>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Tableau de bord</a>
                    @endif
                    <a href="{{ route('login.form') }}" class="btn btn-secondary">Se connecter</a>
                    <a href="{{ route('register.form') }}" class="btn btn-primary">S'inscrire</a>
                @else
                    <a href="{{ route('login.form') }}" class="btn btn-secondary">Connexion</a>
                    <a href="{{ route('register.form') }}" class="btn btn-primary">Inscription</a>
                @endif
            </div>
        </div>
    </header>

    <div class="container">
        <a href="{{ route('client.home') }}" class="back-link">« Retour aux produits</a>
        
        <h2 class="panier-title">_ Créer votre panier</h2>
        
        @if(session('error'))
            <div class="success" style="background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('panier.store') }}" method="POST" id="panierForm">
            @csrf
            
            <div class="products-summary">
                <h3 style="margin-bottom: 20px;">Produits sélectionnés:</h3>
                
                @foreach($products as $product)
                    <div class="product-item">
                        @if($product->images)
                            <img src="{{ asset($product->images) }}" alt="{{ $product->name }}" class="product-image">
                        @else
                            <div class="product-image">_ Pas d'image</div>
                        @endif
                        
                        <div class="product-info">
                            <div class="product-name">{{ $product->name }}</div>
                            <div class="product-price">{{ number_format($product->price, 0, ',', ' ') }} FCFA</div>
                            <div class="product-brand">@if($product->brand) {{ $product->brand->name }} @endif</div>
                            <div style="color: #666; font-size: 14px;">Stock disponible: {{ $product->stock }}</div>
                        </div>
                        
                        <div>
                            <label for="quantity_{{ $product->id }}">Quantité:</label>
                            <input type="number" 
                                   id="quantity_{{ $product->id }}" 
                                   name="quantities[{{ $product->id }}]" 
                                   class="quantity-input" 
                                   min="1" 
                                   max="{{ $product->stock }}" 
                                   value="1" 
                                   required>
                            <input type="hidden" name="products[]" value="{{ $product->id }}">
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="payment-form">
                <h3 class="form-title">Informations de paiement</h3>
                
                <div class="form-group">
                    <label for="payment_mode">Mode de paiement:</label>
                    <select id="payment_mode" name="payment_mode" required>
                        <option value="">Sélectionner un mode de paiement</option>
                        <option value="carte">Carte bancaire</option>
                        <option value="mobile">Mobile money</option>
                        <option value="espece">Espèces</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="card_number">Numéro de carte (16 chiffres):</label>
                    <input type="text" 
                           id="card_number" 
                           name="card_number" 
                           placeholder="1234 5678 9012 3456" 
                           maxlength="19" 
                           pattern="[0-9\s]{16,19}" 
                           required>
                    <div style="color: #666; font-size: 12px; margin-top: 5px;">Entrez 16 chiffres sans espaces</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">_ Valider le panier</button>
                    <a href="{{ route('client.home') }}" class="btn-cancel">Annuler</a>
                </div>
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

        // Valider les quantités
        document.querySelectorAll('.quantity-input').forEach(function(input) {
            input.addEventListener('change', function() {
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
        });
    </script>
</body>
</html>
