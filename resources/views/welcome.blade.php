<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chargement - Boutique en ligne</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .logo {
            font-size: 60px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 40px;
            font-size: 18px;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        .features {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }
        .feature {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #666;
            font-size: 14px;
        }
        .feature-icon {
            font-size: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        @media (max-width: 600px) {
            .container {
                padding: 30px;
                margin: 20px;
            }
            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🛍️</div>
        <h1>Bienvenue sur notre boutique</h1>
        <p class="subtitle">Préparation de votre expérience shopping...</p>
        
        <div class="loading-spinner"></div>
        
        <div class="features">
            <div class="feature">
                <span class="feature-icon">🛍️</span>
                <span>Large catalogue de produits</span>
            </div>
            <div class="feature">
                <span class="feature-icon">🔍</span>
                <span>Recherche avancée</span>
            </div>
            <div class="feature">
                <span class="feature-icon">👤</span>
                <span>Vendeurs vérifiés</span>
            </div>
            <div class="feature">
                <span class="feature-icon">🚚</span>
                <span>Livraison rapide</span>
            </div>
        </div>
    </div>
    
    <script>
        // Redirection automatique vers la page client après 2 secondes
        setTimeout(function() {
            window.location.href = '{{ route("client.home") }}';
        }, 2000);
    </script>
</body>
</html>
