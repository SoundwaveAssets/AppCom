<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Administrateur</title>
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
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .menu-item {
            background: #007bff;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .menu-item:hover {
            background: #0056b3;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .logout-btn {
            display: block;
            width: 200px;
            margin: 30px auto 0;
            padding: 12px;
            background: #dc3545;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .welcome-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .welcome-message h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .welcome-message p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-message">
            <h2>Bienvenue, {{ auth()->user()->name }} !</h2>
            <p>Gérez votre catalogue de produits en toute simplicité</p>
        </div>
        
        @if(session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        <div class="menu-grid">
            <a href="{{ route('admin.products.index') }}" class="menu-item">
                Mes Produits
            </a>
            <a href="{{ route('admin.categories.create') }}" class="menu-item">
                Créer une Catégorie
            </a>
            <a href="{{ route('admin.brands.create') }}" class="menu-item">
                Créer une Marque
            </a>
            <a href="{{ route('admin.products.create') }}" class="menu-item">
                Créer un Produit
            </a>
        </div>

        <form action="{{ route('logout') }}" method="POST" style="display: inline-block; width: 200px; margin: 30px auto 0;">
            @csrf
            <button type="submit" class="logout-btn">Se déconnecter</button>
        </form>
    </div>
</body>
</html>
