<?php
include 'db.php'; // Include database connection
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if ID is set
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM products WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    
    $query = "UPDATE products SET name=?, price=?, description=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sdsi", $name, $price, $description, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: manage_products.php'); // Redirect to product management page
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 50%;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            margin-top: 15px;
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Product</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            <label>Price:</label>
            <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
            <label>Description:</label>
            <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            <button type="submit" name="update">Update</button>
        </form>
    </div>
</body>
</html>

