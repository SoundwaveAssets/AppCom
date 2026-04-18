<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Produits</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .toolbar {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .search-box, .filter-box {
            flex: 1;
            min-width: 200px;
        }
        .search-box input, .filter-box select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-create {
            background-color: #28a745;
        }
        .btn-create:hover {
            background-color: #218838;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .product-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .product-price {
            font-size: 20px;
            color: #007bff;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .product-stock {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .product-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #888;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .no-products {
            text-align: center;
            color: #666;
            font-size: 16px;
            padding: 40px;
        }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mes Produits</h1>
        
        @if(session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        <div class="toolbar">
            <div class="search-box">
                <form method="GET" action="{{ route('admin.products.index') }}">
                    <input type="text" name="search" placeholder="Rechercher un produit..." value="{{ request('search') }}">
                </form>
            </div>
            
            <div class="filter-box">
                <form method="GET" action="{{ route('admin.products.index') }}">
                    <select name="category_id" onchange="this.form.submit()">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            
            <div class="filter-box">
                <form method="GET" action="{{ route('admin.products.index') }}">
                    <select name="brand_id" onchange="this.form.submit()">
                        <option value="">Toutes les marques</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            
            <div class="search-box">
                <a href="{{ route('admin.products.create') }}" class="btn btn-create">Créer un produit</a>
            </div>
        </div>

        @if($products->count() > 0)
            <div class="products-grid">
                @foreach($products as $product)
                    <div class="product-card">
                        @if($product->images)
                            <img src="{{ asset($product->images) }}" alt="{{ $product->name }}" class="product-image">
                        @else
                            <div style="width: 100%; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999; border-radius: 5px; margin-bottom: 15px;">
                                Pas d'image
                            </div>
                        @endif
                        
                        <div class="product-title">{{ $product->name }}</div>
                        <div class="product-price">{{ number_format($product->price, 0, ',', ' ') }} FCFA</div>
                        <div class="product-stock">Stock: {{ $product->stock }}</div>
                        
                        <div class="product-meta">
                            <span>@if($product->category) {{ $product->category->name }} @endif</span>
                            <span>@if($product->brand) {{ $product->brand->name }} @endif</span>
                        </div>
                        
                        <div class="product-actions" style="margin-top: 15px; display: flex; gap: 10px;">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-edit" style="flex: 1; padding: 8px; text-align: center; text-decoration: none; background-color: #ffc107; color: #333; border-radius: 5px; font-size: 12px; font-weight: bold;">Modifier</a>
                            <form action="{{ route('admin.products.delete', $product->id) }}" method="POST" style="flex: 1; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete" style="width: 100%; padding: 8px; background-color: #dc3545; color: white; border: none; border-radius: 5px; font-size: 12px; font-weight: bold; cursor: pointer;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit?')">Supprimer</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-products">
                Aucun produit trouvé.
            </div>
        @endif

        <a href="{{ route('admin.dashboard') }}" class="back-link">Retour au tableau de bord</a>
    </div>
</body>
</html>
