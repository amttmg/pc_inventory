<?php
session_start();
require 'db_connect.php'; // Your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_code = $_POST['branch_code'];
    $password = $_POST['password'];

    // Verify credentials
    $stmt = $pdo->prepare("SELECT * FROM branch_auth WHERE branch_code = ?");
    $stmt->execute([$branch_code]);
    $branch = $stmt->fetch();

    if ($branch && $password == $branch['branch_code']) { // Simple auth - password is branch code
        $_SESSION['branch_id'] = $branch['branch_code'];
        header("Location: manage_computers.php");
        exit();
    } else {
        $error = "Invalid branch code or password";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Branch Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>
    <h2>Branch Login</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <div class="form-group">
            <label>Branch Code:</label>
            <input type="number" name="branch_code" required>
        </div>
        <div class="form-group">
            <label>Password (Branch Code):</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
</body>

</html>