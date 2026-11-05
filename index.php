<?php
$apiUrl = "https://dummyjson.com/products";
$message = "";
$messageType = "success";
$simulatedResponse = null;

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
    $message = "✨ Product '{$_POST['title']}' Created Successfully!";
    $messageType = "primary";
}

// 2. UPDATE Product (PUT)
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $updatedData = [
        'title' => $_POST['title'],
        'price' => (float)$_POST['price']
    ];
    $simulatedResponse = callAPI('PUT', "$apiUrl/$id", $updatedData);
    $message = "✏️ Product #$id Updated Successfully!";
    $messageType = "warning";
}

// 3. DELETE Product (DELETE)
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $simulatedResponse = callAPI('DELETE', "$apiUrl/$id");
    $message = "🗑️ Product #$id Deleted Successfully!";
    $messageType = "danger";
}

// 4. READ Products (GET) - Search or Category or Default
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';

if ($searchQuery !== '') {
    $data = callAPI('GET', "$apiUrl/search?q=" . urlencode($searchQuery));
} elseif ($selectedCategory !== '') {
    $data = callAPI('GET', "$apiUrl/category/" . urlencode($selectedCategory));
} else {
    $data = callAPI('GET', "$apiUrl?limit=16");
}
$products = $data['products'] ?? [];
$categories = ['beauty', 'fragrances', 'furniture', 'groceries', 'laptops', 'smartphones'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexStore | Interactive DummyJSON Shop & CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
        }
        body {
            background-color: #f4f6fb;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        /* Sticky Glass Navbar */
        .navbar-custom {
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(12px);
        }
        /* Hero Banner */
        .hero-banner {
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.25);
        }
        /* Interactive Product Cards & Hover Effects */
        .product-card {
            border: none;
            border-radius: 16px;
            background: #ffffff;
            transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow: hidden;
            position: relative;
        }
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.1) !important;
        }
        .img-wrapper {
            height: 210px;
            overflow: hidden;
            background: #f8fafc;
            position: relative;
            cursor: pointer;
        }
        .img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 15px;
            transition: transform 0.5s ease;
        }
        .product-card:hover .img-wrapper img {
            transform: scale(1.12);
        }
        /* Quick Action Hover Overlay */
        .card-actions-overlay {
            position: absolute;
            top: 12px;
            right: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            opacity: 0;
            transform: translateX(15px);
            transition: all 0.3s ease;
        }
        .product-card:hover .card-actions-overlay {
            opacity: 1;
            transform: translateX(0);
        }
        .action-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            border: none;
            transition: transform 0.2s ease, background 0.2s ease;
        }
        .action-icon-btn:hover {
            transform: scale(1.15);
        }
        .discount-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #ef4444;
            color: white;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .category-pill {
            transition: all 0.2s ease;
            border-radius: 50px;
            text-transform: capitalize;
        }
        .category-pill:hover {
            transform: translateY(-2px);
        }
        .btn-buy {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 600;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .btn-buy:hover {
            opacity: 0.92;
            color: white;
            transform: scale(1.02);
        }
    </style>
</head>
<body>

<!-- TOP NAVIGATION BAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-2" href="index.php">
            <i class="bi bi-bag-heart-fill text-info"></i> NexStore <span class="badge bg-secondary fs-6 fw-normal">API CRUD</span>
        </a>
        
        <div class="d-flex align-items-center gap-3 order-lg-3">
            <!-- Add Product Modal Trigger -->
            <button class="btn btn-outline-light rounded-pill px-3 btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-circle-fill text-info"></i> Add Product
            </button>
            
            <!-- Shopping Cart Trigger -->
            <button class="btn btn-primary rounded-pill px-3 position-relative d-flex align-items-center gap-2" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas">
                <i class="bi bi-cart3"></i> Cart
                <span id="cartCountBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
            </button>
        </div>
    </div>
</nav>

<div class="container py-4">
    <!-- HERO & SEARCH SECTION -->
    <div class="hero-banner mb-4">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-3 mb-lg-0">
                <h1 class="fw-bold display-6 mb-2">Interactive Product Storefront</h1>
                <p class="mb-0 opacity-75">Explore products, test real-time CRUD operations, and simulate purchases powered by DummyJSON.</p>
            </div>
            <div class="col-lg-5">
                <form method="GET" class="d-flex gap-2 bg-white p-2 rounded-pill shadow-sm">
                    <input type="text" name="search" class="form-control border-0 rounded-pill ps-3" placeholder="Search laptops, perfumes, beauty..." value="<?= htmlspecialchars($searchQuery) ?>">
                    <?php if ($searchQuery || $selectedCategory): ?>
                        <a href="index.php" class="btn btn-light rounded-pill px-3"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-dark rounded-pill px-4"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>
    </div>

    <!-- CATEGORY PILLS -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <span class="text-muted small fw-semibold me-2"><i class="bi bi-funnel-fill"></i> Categories:</span>
        <a href="index.php" class="btn btn-sm category-pill <?= ($selectedCategory === '' && $searchQuery === '') ? 'btn-dark' : 'btn-outline-secondary bg-white' ?>">All Products</a>
        <?php foreach ($categories as $cat): ?>
            <a href="?category=<?= urlencode($cat) ?>" class="btn btn-sm category-pill <?= ($selectedCategory === $cat) ? 'btn-dark' : 'btn-outline-secondary bg-white' ?>">
                <?= htmlspecialchars($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- CRUD API RESPONSE NOTIFICATION -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($message) ?></h6>
                    <span class="small text-muted">DummyJSON API Response Payload:</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <pre class="mt-2 mb-0 bg-dark text-info p-3 rounded-3 small" style="max-height: 160px; overflow-y: auto;"><?= htmlspecialchars(json_encode($simulatedResponse, JSON_PRETTY_PRINT)) ?></pre>
        </div>
    <?php endif; ?>

    <!-- PRODUCT GRID -->
    <div class="row g-4">
        <?php if (empty($products)): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-box-seam display-1 text-muted"></i>
                <h4 class="mt-3 text-secondary">No products found</h4>
                <a href="index.php" class="btn btn-primary rounded-pill mt-2">View All Products</a>
            </div>
        <?php endif; ?>

        <?php foreach ($products as $product): 
            $discount = round($product['discountPercentage'] ?? 0);
            $productJson = htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8');
        ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card product-card h-100 shadow-sm">
                    <!-- Image & Hover Overlay -->
                    <div class="img-wrapper" onclick='openQuickView(<?= $productJson ?>)'>
                        <?php if ($discount > 0): ?>
                            <span class="discount-badge">-<?= $discount ?>%</span>
                        <?php endif; ?>
                        <img src="<?= htmlspecialchars($product['thumbnail']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                        
                        <!-- Floating Edit & Delete Buttons on Hover -->
                        <div class="card-actions-overlay" onclick="event.stopPropagation();">
                            <button class="action-icon-btn text-primary" title="Edit Product (PUT)" 
                                    onclick="openEditModal(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['title'])) ?>', <?= $product['price'] ?>)">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form method="POST" onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($product['title'])) ?> (ID #<?= $product['id'] ?>)?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                <button type="submit" class="action-icon-btn text-danger" title="Delete Product (DELETE)">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body d-flex flex-column p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-light text-secondary border text-capitalize"><?= htmlspecialchars($product['category']) ?></span>
                            <span class="small fw-semibold text-warning"><i class="bi bi-star-fill"></i> <?= $product['rating'] ?></span>
                        </div>
                        
                        <h6 class="card-title fw-bold text-dark mb-1 text-truncate" style="cursor:pointer;" onclick='openQuickView(<?= $productJson ?>)'>
                            <?= htmlspecialchars($product['title']) ?>
                        </h6>
                        <p class="card-text small text-muted flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($product['description']) ?>
                        </p>

                        <div class="d-flex justify-content-between align-items-baseline mt-2 mb-3">
                            <div>
                                <span class="fs-5 fw-bold text-dark">$<?= number_format($product['price'], 2) ?></span>
                                <small class="text-muted ms-1">ID #<?= $product['id'] ?></small>
                            </div>
                            <span class="badge bg-success-subtle text-success"><?= ($product['stock'] ?? 15) ?> in stock</span>
                        </div>

                        <!-- Interactive Purchase & Cart Buttons -->
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-dark btn-sm flex-grow-1 rounded-3" 
                                    onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['title'])) ?>', <?= $product['price'] ?>, '<?= htmlspecialchars($product['thumbnail']) ?>')">
                                <i class="bi bi-cart-plus"></i> Add
                            </button>
                            <button class="btn btn-buy btn-sm flex-grow-1 rounded-3" 
                                    onclick="buyNow(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['title'])) ?>', <?= $product['price'] ?>, '<?= htmlspecialchars($product['thumbnail']) ?>')">
                                <i class="bi bi-lightning-fill"></i> Buy Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 1. CREATE PRODUCT MODAL (POST) -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Add New Product (CREATE)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Product Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Wireless Gaming Headset" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-semibold small">Price ($)</label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="99.99" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold small">Category</label>
                            <input type="text" name="category" class="form-control" placeholder="electronics" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe the product features..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. EDIT PRODUCT MODAL (PUT) -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-warning text-dark rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Update Product (UPDATE)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="update">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Product ID</label>
                        <input type="number" name="id" id="editId" class="form-control bg-light" readonly required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Updated Title</label>
                        <input type="text" name="title" id="editTitle" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Updated Price ($)</label>
                        <input type="number" step="0.01" name="price" id="editPrice" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. INTERACTIVE PRODUCT QUICK-VIEW MODAL -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="row g-0">
                <div class="col-md-6 bg-light d-flex align-items-center justify-content-center p-4">
                    <img id="qvImage" src="" alt="" class="img-fluid" style="max-height: 300px; object-fit: contain;">
                </div>
                <div class="col-md-6 p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <span id="qvCategory" class="badge bg-primary-subtle text-primary text-capitalize mb-2"></span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <h4 id="qvTitle" class="fw-bold mb-1"></h4>
                    <p id="qvBrand" class="text-muted small mb-2"></p>
                    <h3 id="qvPrice" class="fw-bold text-success mb-3"></h3>
                    <p id="qvDesc" class="text-secondary small flex-grow-1"></p>
                    
                    <div class="border-top pt-3 mt-3 d-flex justify-content-between small text-muted mb-3">
                        <span><i class="bi bi-box-seam me-1"></i>Stock: <strong id="qvStock"></strong></span>
                        <span><i class="bi bi-star-fill text-warning me-1"></i>Rating: <strong id="qvRating"></strong></span>
                        <span><i class="bi bi-upc-scan me-1"></i>ID: #<strong id="qvId"></strong></span>
                    </div>
                    <button id="qvBuyBtn" class="btn btn-buy w-100 rounded-pill py-2">
                        <i class="bi bi-bag-check-fill me-1"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. SLIDE-OUT SHOPPING CART OFFCANVAS -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold"><i class="bi bi-cart3 me-2 text-primary"></i>Your Shopping Cart</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <div id="cartItemsContainer" class="flex-grow-1 overflow-auto"></div>
        <div class="border-top pt-3 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted fw-semibold">Total Amount:</span>
                <span id="cartTotal" class="fs-4 fw-bold text-success">$0.00</span>
            </div>
            <button onclick="checkoutPurchase()" class="btn btn-buy w-100 rounded-pill py-2 fs-6">
                <i class="bi bi-credit-card-2-front-fill me-2"></i>Complete Purchase
            </button>
        </div>
    </div>
</div>

<!-- TOAST NOTIFICATION FOR CART / PURCHASE -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast align-items-center text-bg-dark border-0 rounded-3" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">Added to cart!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let cart = JSON.parse(localStorage.getItem('nexstore_cart')) || [];

    function saveAndRenderCart() {
        localStorage.setItem('nexstore_cart', JSON.stringify(cart));
        const container = document.getElementById('cartItemsContainer');
        const badge = document.getElementById('cartCountBadge');
        const totalEl = document.getElementById('cartTotal');

        const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
        badge.textContent = totalItems;

        if (cart.length === 0) {
            container.innerHTML = `<div class="text-center text-muted my-5"><i class="bi bi-cart-x display-4"></i><p class="mt-2">Your cart is empty</p></div>`;
            totalEl.textContent = '$0.00';
            return;
        }

        let total = 0;
        container.innerHTML = cart.map((item, idx) => {
            total += item.price * item.qty;
            return `
                <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3 bg-light">
                    <img src="${item.thumb}" style="width:55px; height:55px; object-fit:contain;" class="bg-white rounded p-1">
                    <div class="flex-grow-1">
                        <h6 class="mb-0 small fw-bold text-truncate" style="max-width: 150px;">${item.title}</h6>
                        <span class="text-success small fw-semibold">$${item.price.toFixed(2)}</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="changeQty(${idx}, -1)">-</button>
                        <span class="small fw-bold px-1">${item.qty}</span>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="changeQty(${idx}, 1)">+</button>
                    </div>
                </div>
            `;
        }).join('');
        totalEl.textContent = '$' + total.toFixed(2);
    }

    function addToCart(id, title, price, thumb) {
        const existing = cart.find(i => i.id === id);
        if (existing) {
            existing.qty++;
        } else {
            cart.push({ id, title, price, thumb, qty: 1 });
        }
        saveAndRenderCart();
        showToast(`🛒 <strong>${title}</strong> added to cart!`);
    }

    function buyNow(id, title, price, thumb) {
        addToCart(id, title, price, thumb);
        const offcanvas = new bootstrap.Offcanvas(document.getElementById('cartOffcanvas'));
        offcanvas.show();
    }

    function changeQty(idx, delta) {
        cart[idx].qty += delta;
        if (cart[idx].qty <= 0) cart.splice(idx, 1);
        saveAndRenderCart();
    }

    function checkoutPurchase() {
        if (cart.length === 0) {
            showToast('⚠️ Your cart is empty!');
            return;
        }
        const total = document.getElementById('cartTotal').textContent;
        cart = [];
        saveAndRenderCart();
        bootstrap.Offcanvas.getInstance(document.getElementById('cartOffcanvas')).hide();
        showToast(`🎉 Order Confirmed! Total charged: <strong>${total}</strong>`);
    }

    function openEditModal(id, title, price) {
        document.getElementById('editId').value = id;
        document.getElementById('editTitle').value = title;
        document.getElementById('editPrice').value = price;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function openQuickView(product) {
        document.getElementById('qvImage').src = product.thumbnail;
        document.getElementById('qvCategory').textContent = product.category;
        document.getElementById('qvTitle').textContent = product.title;
        document.getElementById('qvBrand').textContent = product.brand ? `Brand: ${product.brand}` : '';
        document.getElementById('qvPrice').textContent = '$' + Number(product.price).toFixed(2);
        document.getElementById('qvDesc').textContent = product.description;
        document.getElementById('qvStock').textContent = product.stock ?? 15;
        document.getElementById('qvRating').textContent = product.rating;
        document.getElementById('qvId').textContent = product.id;

        const btn = document.getElementById('qvBuyBtn');
        btn.onclick = () => {
            addToCart(product.id, product.title, product.price, product.thumbnail);
            bootstrap.Modal.getInstance(document.getElementById('quickViewModal')).hide();
        };
        new bootstrap.Modal(document.getElementById('quickViewModal')).show();
    }

    function showToast(htmlMsg) {
        document.getElementById('toastMessage').innerHTML = htmlMsg;
        new bootstrap.Toast(document.getElementById('liveToast')).show();
    }

    saveAndRenderCart();
</script>
</body>
</html>
