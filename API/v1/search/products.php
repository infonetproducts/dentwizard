<?php
// api/v1/search/products.php
// Search products

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Validate authentication
AuthMiddleware::validateRequest();
$client_id = $GLOBALS['auth_user']['client_id'];

// Get search parameters
$q = isset($_GET['q']) ? $_GET['q'] : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$price_min = isset($_GET['price_min']) ? (float)$_GET['price_min'] : 0;
$price_max = isset($_GET['price_max']) ? (float)$_GET['price_max'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'relevance';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = ($page - 1) * $limit;

if (empty($q) && $category == 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Search query or category is required'
    ]);
    exit;
}

$pdo = getPDOConnection();
$base_url = getenv('BASE_URL') ?: 'https://your-php-server.com';

// Build search query
$where_conditions = ["i.CID = :client_id", "i.status_item = 'Y'"];
$params = ['client_id' => $client_id];

if (!empty($q)) {
    $where_conditions[] = "(
        i.item_title LIKE :q1 OR 
        i.Description LIKE :q2 OR 
        i.FormID LIKE :q3
    )";
    $params['q1'] = "%$q%";
    $params['q2'] = "%$q%";
    $params['q3'] = "%$q%";
}

if ($category > 0) {
    $where_conditions[] = "fcl.CategoryID = :category";
    $params['category'] = $category;
}

if ($price_min > 0) {
    $where_conditions[] = "i.Price >= :price_min";
    $params['price_min'] = $price_min;
}

if ($price_max > 0) {
    $where_conditions[] = "i.Price <= :price_max";
    $params['price_max'] = $price_max;
}

$where_sql = implode(' AND ', $where_conditions);

// Get facets for filtering
$facets = [];

// Get categories facet
$cat_sql = "SELECT c.ID, c.Name, COUNT(DISTINCT i.ID) as count
            FROM Items i
            LEFT JOIN FormCategoryLink fcl ON fcl.FormID = i.ID
            LEFT JOIN Category c ON c.ID = fcl.CategoryID
            WHERE $where_sql AND c.ID IS NOT NULL
            GROUP BY c.ID
            ORDER BY count DESC
            LIMIT 10";

$stmt = $pdo->prepare($cat_sql);
$stmt->execute($params);
$facets['categories'] = $stmt->fetchAll();

// Get price ranges facet
$price_sql = "SELECT 
                MIN(Price) as min_price,
                MAX(Price) as max_price
              FROM Items i
              LEFT JOIN FormCategoryLink fcl ON fcl.FormID = i.ID
              WHERE $where_sql";

$stmt = $pdo->prepare($price_sql);
$stmt->execute($params);
$price_range = $stmt->fetch();

$facets['price_ranges'] = [
    ['range' => '0-25', 'min' => 0, 'max' => 25],
    ['range' => '25-50', 'min' => 25, 'max' => 50],
    ['range' => '50-100', 'min' => 50, 'max' => 100],
    ['range' => '100+', 'min' => 100, 'max' => $price_range['max_price']]
];

// Get total count
$count_sql = "SELECT COUNT(DISTINCT i.ID) as total
              FROM Items i
              LEFT JOIN FormCategoryLink fcl ON fcl.FormID = i.ID
              WHERE $where_sql";

$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetch()['total'];

// Determine sort order
$order_by = match($sort) {
    'price_asc' => 'i.Price ASC',
    'price_desc' => 'i.Price DESC',
    'name' => 'i.item_title ASC',
    'newest' => 'i.ID DESC',
    default => 'i.item_title ASC' // For relevance, you might want to add a relevance score
};

// Get search results
$sql = "SELECT DISTINCT
            i.ID as id,
            i.FormID as form_id,
            i.item_title as title,
            i.Description as description,
            i.Price as price,
            i.ImageFile as image_file,
            c.Name as category_name,
            fcl.CategoryID as category_id
        FROM Items i
        LEFT JOIN FormCategoryLink fcl ON fcl.FormID = i.ID
        LEFT JOIN Category c ON c.ID = fcl.CategoryID
        WHERE $where_sql
        ORDER BY $order_by
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge($params, [
    'limit' => $limit,
    'offset' => $offset
]));

$results = [];
while ($row = $stmt->fetch()) {
    $image_url = null;
    $thumbnail_url = null;
    
    if ($row['image_file']) {
        $image_url = $base_url . '/pdf/' . $client_id . '/' . $row['image_file'];
        $thumbnail_url = $base_url . '/pdf/' . $client_id . '/thumbs/' . $row['image_file'];
    }
    
    $results[] = [
        'id' => (int)$row['id'],
        'form_id' => $row['form_id'],
        'title' => $row['title'],
        'description' => strip_tags(substr($row['description'], 0, 200)) . '...',
        'price' => (float)$row['price'],
        'image_url' => $image_url,
        'thumbnail_url' => $thumbnail_url,
        'category_id' => (int)$row['category_id'],
        'category_name' => $row['category_name']
    ];
}

// Return response
echo json_encode([
    'success' => true,
    'data' => [
        'results' => $results,
        'facets' => $facets,
        'total_results' => (int)$total,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'items_per_page' => $limit
        ]
    ]
]);
?>