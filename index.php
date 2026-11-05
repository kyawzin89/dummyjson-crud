<?php
$apiUrl = "https://dummyjson.com/products";
$message = "";
$simulatedResponse = null;

// Helper function to make API requests using cURL
function callAPI($method, $url, $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }

    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// 1. CREATE Product (POST)
if (isset($_POST['action']) && $_POST['action'] === 'create') {
    $newProduct = [
        'title' => $_POST['title'],
        'price' => (float)$_POST['price'],
        'category' => $_POST['category'],
        'description' => $_POST['description']
    ];
    $simulatedResponse = callAPI('POST', "$apiUrl/add", $newProduct);
    $message = "✅ Product Created Successfully (Simulated by DummyJSON)!";
}

// 2. UPDATE Product (PUT)
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $updatedData = [
        'title' => $_POST['title'],
        'price' => (float)$_POST['price']
    ];
    $simulatedResponse = callAPI('PUT', "$apiUrl/$id", $updatedData);
    $message = "✏️ Product #$id Updated Successfully (Simulated by DummyJSON)!";
}

// 3. DELETE Product (DELETE)
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $simulatedResponse = callAPI('DELETE', "$apiUrl/$id");
    $message = "🗑️ Product #$id Deleted Successfully (Simulated by DummyJSON)!";
}

// 4. READ Products (GET) - Limit to 12 for clean display, or search
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($searchQuery !== '') {
    $data = callAPI('GET', "$apiUrl/search?q=" . urlencode($searchQuery));
} else {
    $data = callAPI('GET', "$apiUrl?limit=12");
}
$products = $data['products'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DummyJSON Products CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4 text-center">📦 DummyJSON Products CRUD App</h1>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($message) ?></strong>
            <pre class="mt-2 mb-0 bg-dark text-white p-2 rounded small"><?= htmlspecialchars(json_encode($simulatedResponse, JSON_PRETTY_PRINT)) ?></pre>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <!-- CREATE FORM -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">Add New Product (CREATE)</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-2">
                            <input type="text" name="title" class="form-control" placeholder="Product Title" required>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <input type="number" step="0.01" name="price" class="form-control" placeholder="Price ($)" required>
                            </div>
                            <div class="col">
                                <input type="text" name="category" class="form-control" placeholder="Category" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <textarea name="description" class="form-control" placeholder="Description" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Product</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- UPDATE FORM -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">Update Existing Product (UPDATE)</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update">
                        <div class="mb-2">
                            <input type="number" name="id" class="form-control" placeholder="Product ID (e.g. 1)" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="title" class="form-control" placeholder="New Title" required>
                        </div>
                        <div class="mb-2">
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="New Price ($)" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Update Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SEARCH BAR (READ) -->
    <form method="GET" class="mb-4 d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($searchQuery) ?>">
        <button type="submit" class="btn btn-secondary">Search</button>
        <?php if ($searchQuery): ?>
            <a href="index.php" class="btn btn-outline-secondary">Reset</a>
        <?php endif; ?>
    </form>

    <!-- PRODUCT GRID (READ & DELETE) -->
    <div class="row g-3">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <img src="<?= htmlspecialchars($product['thumbnail']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['title']) ?>" style="height: 180px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-info text-dark align-self-start mb-1">ID: #<?= $product['id'] ?> | <?= htmlspecialchars($product['category']) ?></span>
                        <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
                        <p class="card-text small text-muted flex-grow-1"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-bold fs-5 text-success">$<?= number_format($product['price'], 2) ?></span>
                            <span class="badge bg-warning text-dark">⭐ <?= $product['rating'] ?></span>
                        </div>
                        <!-- DELETE BUTTON -->
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Delete product #<?= $product['id'] ?>?')">Delete (DELETE)</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
