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
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
            font-family: Arial, sans-serif;
        }

        .login-box {
            background: white;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-box h2 {
            margin-bottom: 25px;
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            background: #007BFF;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #0056b3;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="login-box">
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
    </div>
</body>

</html>