<?php
session_start();
include('../includes/db.php');

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle adding product to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1; // Default to 1 for add-to-cart button

    // Check if this product already in cart for user
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // Update quantity
        $new_quantity = $item['quantity'] + 1;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $item['id']]);
    } else {
        // Insert new cart item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }

    header('Location: cart.php');
    exit();
}

// Handle update quantity form
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $cart_id => $quantity) {
        $quantity = max(1, intval($quantity)); // Minimum quantity is 1
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $user_id]);
    }
    header('Location: cart.php');
    exit();
}

// Handle remove item
if (isset($_POST['remove_item'])) {
    $cart_id = $_POST['cart_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header('Location: cart.php');
    exit();
}

// Fetch cart items with product details
$stmt = $conn->prepare("SELECT cart.id AS cart_id, products.*, cart.quantity 
                        FROM cart 
                        JOIN products ON cart.product_id = products.id 
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Your Cart</title>
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        .cart-container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
        }
        input[type=number] {
            width: 60px;
            padding: 6px;
            text-align: center;
        }
        .btn-update, .btn-remove {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-update {
            background-color: #3498db;
            color: white;
            margin-right: 10px;
        }
        .btn-remove {
            background-color: #e74c3c;
            color: white;
        }
        .total-price {
            text-align: right;
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .empty-cart {
            text-align: center;
            font-size: 1.2em;
            color: #666;
        }
        a.continue-shopping {
            display: inline-block;
            margin-bottom: 20px;
            color: #2c3e50;
            text-decoration: none;
            font-weight: bold;
        }
        a.continue-shopping:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <h1>Your Shopping Cart</h1>
        <a href="../index.php" class="continue-shopping">&larr; Continue Shopping</a>

        <?php if (empty($cart_items)) : ?>
            <p class="empty-cart">Your cart is empty.</p>
        <?php else: ?>
            <form method="POST">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <img class="product-image" src="../images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" />
                                    <?php endif; ?>
                                    <br><?= htmlspecialchars($item['name']) ?>
                                </td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <input type="number" name="quantities[<?= $item['cart_id'] ?>]" value="<?= $item['quantity'] ?>" min="1" />
                                </td>
                                <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <button type="submit" name="remove_item" value="Remove" class="btn-remove" formaction="cart.php" formmethod="POST" onclick="return confirm('Remove this item?');">
                                        Remove
                                    </button>
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>" />
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="total-price">Total: $<?= number_format($total_price, 2) ?></div>

                <button type="submit" name="update_cart" class="btn-update">Update Cart</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
