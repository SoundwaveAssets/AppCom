<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique - Catalogue de produits</title>
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
        .search-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input, .form-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102,126,234,0.3);
        }
        .search-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .btn-search {
            background-color: #28a745;
            color: white;
        }
        .btn-search:hover {
            background-color: #218838;
        }
        .btn-reset {
            background-color: #6c757d;
            color: white;
        }
        .btn-reset:hover {
            background-color: #5a6268;
        }
        .products-section {
            margin-top: 30px;
        }
        .section-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            color: #333;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
        .product-content {
            padding: 20px;
        }
        .product-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        .product-price {
            font-size: 24px;
            color: #28a745;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .product-stock {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        .product-meta {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-size: 12px;
            color: #888;
        }
        .product-admin {
            color: #667eea;
            font-weight: bold;
        }
        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 18px;
        }
        .product-selection {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .product-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .checkbox-label {
            font-size: 12px;
            color: #667eea;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-create-panier {
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
        .btn-create-panier:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40,167,69,0.4);
        }
        .btn-create-panier:disabled {
            background: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>🛍️ Boutique en ligne</h1>
            <div class="auth-buttons">
                @if(auth()->check())
                    <span>Bonjour, {{ auth()->user()->name }}!</span>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Tableau de bord</a>
                    @endif
                    <a href="{{ route('panier.index') }}" class="btn btn-primary">Mes paniers</a>
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
        <div class="search-section">
            @if(isset($clientPersonalization))
                <div class="welcome-personalized" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center;">
                    <h3 style="margin: 0; font-size: 24px;">Bienvenue {{ $clientPersonalization['Nom'] ?? 'Client' }} !</h3>
                    <p style="margin: 10px 0 0; font-size: 16px; opacity: 0.9;">Email: {{ $clientPersonalization['Email'] ?? '' }}</p>
                    <p style="margin: 5px 0 0; font-size: 14px; opacity: 0.8;">Client ID: {{ $clientPersonalization['ID'] ?? '' }}</p>
                    <p style="margin: 5px 0 0; font-size: 12px; opacity: 0.7;">Inscrit le: {{ $clientPersonalization['Date d\'inscription'] ?? '' }}</p>
                </div>
            @endif
            
            <h2 style="margin-bottom: 20px; color: #333;">🔍 Rechercher des produits</h2>
            <form method="GET" action="{{ route('client.home') }}" class="search-form">
                <div class="form-group">
                    <label for="search">Nom du produit:</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Rechercher un produit...">
                </div>
                
                <div class="form-group">
                    <label for="category_id">Catégorie:</label>
                    <select id="category_id" name="category_id">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="brand_id">Marque:</label>
                    <select id="brand_id" name="brand_id">
                        <option value="">Toutes les marques</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="admin_name">Nom de l'admin:</label>
                    <input type="text" id="admin_name" name="admin_name" value="{{ request('admin_name') }}" placeholder="Rechercher par vendeur...">
                </div>
                
                <div class="form-group">
                    <label for="sort_by">Trier par:</label>
                    <select id="sort_by" name="sort_by">
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date d'ajout</option>
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Nom</option>
                        <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>Prix</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="sort_order">Ordre:</label>
                    <select id="sort_order" name="sort_order">
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Décroissant</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Croissant</option>
                    </select>
                </div>
                
                <div class="search-actions">
                    <button type="submit" class="btn btn-search">🔍 Rechercher</button>
                    <a href="{{ route('client.home') }}" class="btn btn-reset">🔄 Réinitialiser</a>
                </div>
            </form>
        </div>

        <div class="products-section">
            <h2 class="section-title">📦 Catalogue des produits</h2>
            
            @if($products->count() > 0)
                <form id="panierForm" action="{{ route('panier.create') }}" method="GET">
                    <div class="products-grid">
                        @foreach($products as $product)
                            <div class="product-card">
                                @if($product->images)
                                    <img src="{{ asset($product->images) }}" alt="{{ $product->name }}" class="product-image">
                                @else
                                    <div class="product-image">📷 Pas d'image</div>
                                @endif
                                
                                <div class="product-content">
                                    <div class="product-selection">
                                        <input type="checkbox" name="products[]" value="{{ $product->id }}" id="product_{{ $product->id }}" class="product-checkbox">
                                        <label for="product_{{ $product->id }}" class="checkbox-label">Sélectionner</label>
                                    </div>
                                    
                                    <div class="product-title">{{ $product->name }}</div>
                                    <div class="product-price">{{ number_format($product->price, 0, ',', ' ') }} FCFA</div>
                                    <div class="product-stock">Stock: {{ $product->stock }} unités</div>
                                    
                                    <div class="product-meta">
                                        <span>@if($product->category) 📁 {{ $product->category->name }} @endif</span>
                                        <span>@if($product->brand) 🏷️ {{ $product->brand->name }} @endif</span>
                                        <span class="product-admin">👤 {{ $product->user->name }}</span>
                                        <span>📅 {{ $product->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="panier-actions" style="text-align: center; margin-top: 30px;">
                        <button type="submit" id="createPanierBtn" class="btn btn-create-panier" disabled>
                            🛍️ Créer panier
                        </button>
                        <div id="selectionCount" style="margin-top: 10px; color: #666; font-size: 14px;">
                            0 produit sélectionné
                        </div>
                    </div>
                </form>
            @else
                <div class="no-products">
                    😔 Aucun produit trouvé. Essayez de modifier vos critères de recherche.
                </div>
            @endif
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 - Boutique en ligne. Tous droits réservés.</p>
    </footer>
<script>
        // Gérer les checkboxes et le bouton "Créer panier"
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            const createPanierBtn = document.getElementById('createPanierBtn');
            const selectionCount = document.getElementById('selectionCount');
            
            function updateSelectionCount() {
                const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
                const count = checkedBoxes.length;
                
                selectionCount.textContent = count + ' produit' + (count > 1 ? 's' : '') + ' sélectionné' + (count > 1 ? 's' : '');
                
                if (count > 0) {
                    createPanierBtn.disabled = false;
                    createPanierBtn.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
                } else {
                    createPanierBtn.disabled = true;
                    createPanierBtn.style.background = '#ccc';
                }
            }
            
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', updateSelectionCount);
            });
            
            // Initialiser
            updateSelectionCount();
        });
    </script>
</body>
</html>
