<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['branch_id'])) {
    header("Location: login.php");
    exit();
}

$branch_code = $_SESSION['branch_id'];

// Get branch info
$branch_stmt = $pdo->prepare("SELECT * FROM branches WHERE code = ?");
$branch_stmt->execute([$branch_code]);
$branch = $branch_stmt->fetch();

// Check if already submitted
if ($branch['submitted_at']) {
    die("You have already submitted your inventory on " . date('d M Y H:i', strtotime($branch['submitted_at'])) . ". Please contact IT department if you need to make changes.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['signature_file'])) {
    $uploadDir = 'uploads/signatures/';

    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $fileExt = pathinfo($_FILES['signature_file']['name'], PATHINFO_EXTENSION);
    $fileName = 'signature_' . $branch_code . '_' . time() . '.' . $fileExt;
    $filePath = $uploadDir . $fileName;

    // Move uploaded file
    if (move_uploaded_file($_FILES['signature_file']['tmp_name'], $filePath)) {
        // Update branch record
        $update_stmt = $pdo->prepare("UPDATE branches SET submitted_at = NOW(), file = ? WHERE id = ?");
        $update_stmt->execute([$filePath, $branch['id']]);

        // Redirect to success page or show message
        $_SESSION['success_message'] = "Inventory submitted successfully!";
        header("Location: manage_computers.php");
        exit();
    } else {
        $error = "Failed to upload file. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Final Submission - <?= htmlspecialchars($branch['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .submission-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .instructions {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin-bottom: 20px;
        }

        .btn-submit {
            background-color: #28a745;
            color: white;
            padding: 10px 25px;
            font-size: 1.1rem;
        }

        .file-upload {
            border: 2px dashed #dee2e6;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
            border-radius: 5px;
            cursor: pointer;
        }

        .file-upload:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .file-input {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="submission-container">
            <div class="text-center mb-4">
                <h2><i class="fas fa-check-circle text-success me-2"></i> Final Inventory Submission</h2>
                <p class="text-muted"><?= htmlspecialchars($branch['name'] . ' (' . $branch['code'] . ')') ?></p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="instructions">
                <h5><i class="fas fa-info-circle me-2"></i>Submission Instructions</h5>
                <ol>
                    <li>Please review all computer entries carefully before final submission</li>
                    <li>Once submitted, you won't be able to make changes without contacting IT department</li>
                    <li>Upload a signed copy of the inventory (scan or photo)</li>
                    <li>Click "Submit Inventory" button to complete the process</li>
                </ol>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Upload Signed Inventory (PDF/Image)</label>
                    <div class="file-upload" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                        <p class="mb-1">Click to upload file</p>
                        <p class="small text-muted">PDF, JPG, or PNG (Max 5MB)</p>
                        <div id="fileName" class="mt-2 text-primary fw-bold"></div>
                    </div>
                    <input type="file" class="file-input" id="fileInput" name="signature_file" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="manage_computers.php" class="btn btn-secondary me-md-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Inventory
                    </a>
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-paper-plane me-1"></i> Submit Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show selected file name
        document.getElementById('fileInput').addEventListener('change', function(e) {
            if (this.files.length > 0) {
                document.getElementById('fileName').textContent = this.files[0].name;
            }
        });

        // Confirm before submission
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to submit the inventory? You won\'t be able to make changes after submission.')) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>