<?php
session_start();
include 'includes/db.php';

// Fetch products from database
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Store</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Optional -->
</head>
<body>
    <header>
        <h1>Welcome to Our Store</h1>
        <nav>
            <a href="pages/login.php">Login</a>
            <a href="pages/register.php">Register</a>
            <a href="pages/cart.php">Cart</a>
            <form method="POST" style="display: inline;">
                <button type="submit" name="logout">Logout</button>
            </form>
        </nav>
    </header>

    <main>
        <h2>Products</h2>
        <div class="product-list">
            <?php if (empty($products)) : ?>
                <p>No products available.</p>
            <?php else : ?>
                <?php foreach ($products as $product) : ?>
                    <div class="product">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p>Price: $<?= number_format($product['price'], 2) ?></p>
                        <p><?= htmlspecialchars($product['description']) ?></p>
                        <?php if (!empty($product['image'])) : ?>
                            <img src="images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" width="200">
                        <?php endif; ?>
                        <form method="POST" action="pages/cart.php">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Online Store. All rights reserved.</p>
    </footer>
</body>
</html>
