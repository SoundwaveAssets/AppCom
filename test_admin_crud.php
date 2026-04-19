<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Importer les classes nécessaires
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;

echo "=== TEST CRUD ADMIN ===\n\n";

// Créer un utilisateur admin pour les tests
$admin = User::where('email', 'admin@test.com')->first();
if (!$admin) {
    $admin = User::create([
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'password' => bcrypt('password123'),
        'role' => 'admin'
    ]);
    echo "✅ Admin créé: " . $admin->name . " (ID: " . $admin->id . ")\n";
} else {
    echo "ℹ️  Admin existant: " . $admin->name . " (ID: " . $admin->id . ")\n";
}

// Test 1: Ajouter une catégorie
echo "\n--- TEST 1: AJOUTER CATÉGORIE ---\n";
$categoryRequest = Request::create('/api/admin/categories', 'POST', [
    'name' => 'Électronique',
    'slug' => 'electronique',
    'is_active' => true
]);

// Simuler l'authentification
$app->instance('auth', function() use ($admin) {
    return new class($admin) {
        public $user;
        public function __construct($user) {
            $this->user = $user;
        }
        public function check() {
            return true;
        }
        public function id() {
            return $this->user->id;
        }
    };
});

$categoryResponse = $app->handle($categoryRequest);
echo "Catégorie - Status: " . $categoryResponse->getStatusCode() . "\n";
if ($categoryResponse->getStatusCode() === 201) {
    echo "✅ Catégorie créée avec succès\n";
    $categoryData = json_decode($categoryResponse->getContent(), true);
    echo "ID: " . $categoryData['data']['id'] . "\n";
    echo "Nom: " . $categoryData['data']['name'] . "\n";
} else {
    echo "❌ Erreur création catégorie: " . $categoryResponse->getContent() . "\n";
}

// Test 2: Ajouter une marque
echo "\n--- TEST 2: AJOUTER MARQUE ---\n";
$brandRequest = Request::create('/api/admin/brands', 'POST', [
    'name' => 'Apple',
    'slug' => 'apple'
]);

$brandResponse = $app->handle($brandRequest);
echo "Marque - Status: " . $brandResponse->getStatusCode() . "\n";
if ($brandResponse->getStatusCode() === 201) {
    echo "✅ Marque créée avec succès\n";
    $brandData = json_decode($brandResponse->getContent(), true);
    echo "ID: " . $brandData['data']['id'] . "\n";
    echo "Nom: " . $brandData['data']['name'] . "\n";
    $brandId = $brandData['data']['id'];
} else {
    echo "❌ Erreur création marque: " . $brandResponse->getContent() . "\n";
    $brandId = null;
}

// Test 3: Ajouter un produit
echo "\n--- TEST 3: AJOUTER PRODUIT ---\n";
if ($brandId && isset($categoryData['data']['id'])) {
    $productRequest = Request::create('/api/admin/products', 'POST', [
        'name' => 'iPhone 15',
        'slug' => 'iphone-15',
        'description' => 'Le dernier iPhone avec design moderne',
        'price' => '999.99',
        'stock' => '50',
        'category_id' => $categoryData['data']['id'],
        'brand_id' => $brandId,
        'is_published' => true
    ]);

    $productResponse = $app->handle($productRequest);
    echo "Produit - Status: " . $productResponse->getStatusCode() . "\n";
    if ($productResponse->getStatusCode() === 201) {
        echo "✅ Produit créé avec succès\n";
        $productData = json_decode($productResponse->getContent(), true);
        echo "ID: " . $productData['data']['id'] . "\n";
        echo "Nom: " . $productData['data']['name'] . "\n";
        echo "Prix: " . $productData['data']['price'] . "\n";
    } else {
        echo "❌ Erreur création produit: " . $productResponse->getContent() . "\n";
    }
} else {
    echo "❌ Impossible de tester le produit (catégorie ou marque manquante)\n";
}

// Test 4: Lister les catégories
echo "\n--- TEST 4: LISTER CATÉGORIES ---\n";
$categoriesRequest = Request::create('/api/admin/categories', 'GET');
$categoriesResponse = $app->handle($categoriesRequest);
echo "Liste catégories - Status: " . $categoriesResponse->getStatusCode() . "\n";
if ($categoriesResponse->getStatusCode() === 200) {
    echo "✅ Catégories récupérées\n";
    $categoriesData = json_decode($categoriesResponse->getContent(), true);
    echo "Nombre de catégories: " . count($categoriesData['data']) . "\n";
} else {
    echo "❌ Erreur récupération catégories: " . $categoriesResponse->getContent() . "\n";
}

// Test 5: Lister les marques
echo "\n--- TEST 5: LISTER MARQUES ---\n";
$brandsRequest = Request::create('/api/admin/brands', 'GET');
$brandsResponse = $app->handle($brandsRequest);
echo "Liste marques - Status: " . $brandsResponse->getStatusCode() . "\n";
if ($brandsResponse->getStatusCode() === 200) {
    echo "✅ Marques récupérées\n";
    $brandsData = json_decode($brandsResponse->getContent(), true);
    echo "Nombre de marques: " . count($brandsData['data']) . "\n";
} else {
    echo "❌ Erreur récupération marques: " . $brandsResponse->getContent() . "\n";
}

echo "\n=== FIN DES TESTS ===\n";
