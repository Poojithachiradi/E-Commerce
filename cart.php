<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_item = $result->fetch_assoc();
    
    if ($cart_item) {
        $new_quantity = $cart_item['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    }
    $stmt->execute();
}

// Handle Remove from Cart
if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
}

// Handle Update Quantity
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        $stmt->execute();
    }
}

// Fetch Cart Items
$stmt = $conn->prepare("
    SELECT cart.product_id, cart.quantity, products.name, products.price, products.image 
    FROM cart 
    JOIN products ON cart.product_id = products.id 
    WHERE cart.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

$total_cost = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            font-size: 2em;
            margin-bottom: 20px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .cart-item img {
            max-width: 80px;
            border-radius: 8px;
            margin-right: 20px;
        }
        .item-details {
            flex-grow: 1;
        }
        .item-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #343a40;
        }
        .item-price {
            font-size: 1.1em;
            color: #495057;
        }
        .item-actions {
            display: flex;
            align-items: center;
        }
        .item-actions button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            margin-left: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .item-actions button:hover {
            background-color: #0056b3;
        }
        .quantity {
            width: 50px;
            padding: 5px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .cart-actions a {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1em;
            transition: background-color 0.3s;
        }
        .cart-actions a:hover {
            background-color: #218838;
        }
        .total-cost {
            font-size: 1.6em;
            font-weight: bold;
            color: #343a40;
            text-align: center;
            margin-top: 20px;
        }
        .empty-cart {
            text-align: center;
            font-size: 1.2em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Cart</h2>
        <?php if (empty($cart_items)) : ?>
            <p class="empty-cart">Your cart is empty.</p>
        <?php else : ?>
            <?php foreach ($cart_items as $item) : 
                $total_cost += $item['price'] * $item['quantity']; ?>
                <div class="cart-item">
                    <img src="../images/<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                    <div class="item-details">
                        <div class="item-name"><?= htmlspecialchars($item['name']); ?></div>
                        <div class="item-price">$<?= number_format($item['price'], 2); ?> x <?= $item['quantity']; ?></div>
                    </div>
                    <div class="item-actions">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                            <input type="number" name="quantity" value="<?= $item['quantity']; ?>" class="quantity" min="1" required>
                            <button type="submit" name="update_quantity">Update</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                            <button type="submit" name="remove_from_cart">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="total-cost">Total: $<?= number_format($total_cost, 2); ?></div>
            <div class="cart-actions">
                <a href="index.php">Back to Shop</a>
                <a href="checkout.php">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

