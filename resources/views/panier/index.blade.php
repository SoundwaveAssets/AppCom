<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes paniers</title>
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
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            color: #333;
        }
        .paniers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .panier-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s;
        }
        .panier-card:hover {
            transform: translateY(-5px);
        }
        .panier-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .panier-product {
            margin-bottom: 15px;
        }
        .product-name {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }
        .product-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .product-price {
            color: #28a745;
            font-weight: bold;
        }
        .panier-info {
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-label {
            color: #666;
        }
        .info-value {
            font-weight: bold;
        }
        .panier-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn-edit {
            background-color: #ffc107;
            color: #333;
            flex: 1;
        }
        .btn-edit:hover {
            background-color: #e0a800;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
            flex: 1;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .btn-create {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            display: block;
            margin: 0 auto 30px;
        }
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40,167,69,0.4);
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
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
        .total-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
        }
        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }
        @media (max-width: 768px) {
            .paniers-grid {
                grid-template-columns: 1fr;
            }
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>__ Mes paniers</h1>
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
        <a href="{{ route('client.home') }}" class="back-link">« Retour aux produits</a>
        
        <h2 class="page-title">_ Mes paniers</h2>
        
        @if(session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="error">
                {{ session('error') }}
            </div>
        @endif

        <a href="{{ route('client.home') }}" class="btn-create">+ Créer un nouveau panier</a>

        @if($paniers->count() > 0)
            <div class="paniers-grid">
                @foreach($paniers as $panier)
                    <div class="panier-card">
                        <div class="panier-header">
                            <h3>{{ $panier->product_name }}</h3>
                            <div style="color: #666; font-size: 14px;">ID: #{{ $panier->id }}</div>
                        </div>
                        
                        <div class="panier-product">
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
                                <span>Quantité:</span>
                                <span>{{ $panier->quantity }}</span>
                            </div>
                            <div class="product-details">
                                <span>Stock disponible:</span>
                                <span>{{ $panier->product_stock }}</span>
                            </div>
                        </div>
                        
                        <div class="panier-info">
                            <div class="info-row">
                                <span class="info-label">Total:</span>
                                <span class="info-value">{{ number_format($panier->product_price * $panier->quantity, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Paiement:</span>
                                <span class="info-value">{{ ucfirst($panier->payment_mode) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Type client:</span>
                                <span class="info-value">{{ $panier->client_type }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Date:</span>
                                <span class="info-value">{{ $panier->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        
                        <div class="panier-actions">
                            <a href="{{ route('panier.edit', $panier->id) }}" class="btn btn-edit">Modifier</a>
                            <form action="{{ route('panier.destroy', $panier->id) }}" method="POST" style="flex: 1; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete" style="width: 100%;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce panier?')">Supprimer</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="total-section">
                <div class="total-amount">
                    Total de tous les paniers: {{ number_format($paniers->sum(function($panier) { return $panier->product_price * $panier->quantity; }), 0, ',', ' ') }} FCFA
                </div>
            </div>
        @else
            <div class="empty-state">
                <h3>_ Aucun panier</h3>
                <p>Vous n'avez pas encore de panier. Créez votre premier panier en sélectionnant des produits!</p>
                <br>
                <a href="{{ route('client.home') }}" class="btn btn-primary">Aller aux produits</a>
            </div>
        @endif
    </div>
</body>
</html>
