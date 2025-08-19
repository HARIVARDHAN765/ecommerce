<?php
session_start();
include('../includes/db.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle add product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    $image = '';

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $image = time() . '_' . basename($_FILES['image']['name']);
        $target_dir = "../images/";
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    }

    $stmt = $conn->prepare("INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $price, $description, $image]);

    header("Location: manage_products.php");
    exit();
}

// Handle delete product
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Delete product image file first (optional)
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product && $product['image']) {
        @unlink("../images/" . $product['image']);
    }

    // Delete product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: manage_products.php");
    exit();
}

// Fetch all products
$stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Products</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f4f4f4; }
        form { margin-bottom: 30px; }
        input[type="text"], input[type="number"], textarea {
            width: 100%; padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 4px;
        }
        input[type="file"] {
            margin-bottom: 15px;
        }
        button {
            padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        a.delete-btn {
            color: #e74c3c; text-decoration: none;
        }
        a.delete-btn:hover {
            text-decoration: underline;
        }
        .logout {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h1>Manage Products</h1>

<h2>Add New Product</h2>
<form method="POST" enctype="multipart/form-data">
    <label>Product Name:</label><br>
    <input type="text" name="name" required><br>

    <label>Price:</label><br>
    <input type="number" step="0.01" name="price" required><br>

    <label>Description:</label><br>
    <textarea name="description" rows="4" required></textarea><br>

    <label>Image:</label><br>
    <input type="file" name="image" accept="image/*"><br>

    <button type="submit" name="add_product">Add Product</button>
</form>

<h2>Existing Products</h2>

<?php if (count($products) === 0): ?>
    <p>No products found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price ($)</th>
                <th>Description</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['id']) ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= number_format($p['price'], 2) ?></td>
                    <td><?= htmlspecialchars($p['description']) ?></td>
                    <td>
                        <?php if ($p['image']): ?>
                            <img src="../images/<?= htmlspecialchars($p['image']) ?>" width="80" alt="product image">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_product.php?id=<?= $p['id'] ?>">Edit</a> | 
                        <a href="manage_products.php?delete=<?= $p['id'] ?>" class="delete-btn" onclick="return confirm('Delete this product?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<div class="logout">
    <a href="logout.php">Logout</a>
</div>

</body>
</html>
