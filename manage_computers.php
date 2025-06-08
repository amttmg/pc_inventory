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

// Get computer types
$types_stmt = $pdo->query("SELECT * FROM computer_types");
$types = $types_stmt->fetchAll();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        // Delete computer
        $stmt = $pdo->prepare("DELETE FROM computers WHERE computer_id = ? AND branch_id = ?");
        $stmt->execute([$_POST['delete_id'], $branch['id']]);
    } else {
        // Add/Edit computer
        $data = [
            'branch_id' => $branch['id'],
            'type_id' => $_POST['type_id'],
            'asset_tag' => $_POST['asset_tag'],
            'brand' => $_POST['brand'],
            'model' => $_POST['model'],
            'computer_name' => $_POST['computer_name'],
            'processor' => $_POST['processor'],
            'ram' => $_POST['ram'],
            'storage' => $_POST['storage'],
            'os' => $_POST['os'],
            'status' => $_POST['status'],
            'notes' => $_POST['notes']
        ];

        if (!empty($_POST['computer_id'])) {
            // Update existing
            $data['computer_id'] = $_POST['computer_id'];
            $stmt = $pdo->prepare("UPDATE computers SET 
                type_id = :type_id,
                asset_tag = :asset_tag,
                brand = :brand,
                model = :model,
                computer_name = :computer_name,
                processor = :processor,
                ram = :ram,
                storage = :storage,
                os = :os,
                status = :status,
                notes = :notes
                WHERE computer_id = :computer_id AND branch_id = :branch_id");
        } else {
            // Insert new
            $stmt = $pdo->prepare("INSERT INTO computers SET 
                branch_id = :branch_id,
                type_id = :type_id,
                asset_tag = :asset_tag,
                brand = :brand,
                model = :model,
                computer_name = :computer_name,
                processor = :processor,
                ram = :ram,
                storage = :storage,
                os = :os,
                status = :status,
                notes = :notes");
        }
        $stmt->execute($data);
    }
}

// Get current step from URL
$current_step = isset($_GET['step']) ? $_GET['step'] : '1';

// Get computers from database for step 2
$computers_stmt = $pdo->prepare("
    SELECT c.*, ct.type_name 
    FROM computers c
    JOIN computer_types ct ON c.type_id = ct.type_id
    WHERE c.branch_id = ?
");
$computers_stmt->execute([$branch['id']]);
$db_computers = $computers_stmt->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Computers - <?= htmlspecialchars($branch['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Step form styles */
        .step-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex: 1;
        }

        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 60%;
            width: 80%;
            height: 2px;
            background-color: #e9ecef;
            z-index: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .step-circle.active {
            background-color: #0d6efd;
            color: white;
            box-shadow: 0 0 0 6px rgba(13, 110, 253, 0.2);
        }

        .step-circle.completed {
            background-color: #198754;
            color: white;
        }

        .step-title {
            margin-top: 8px;
            font-size: 14px;
            color: #6c757d;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .step-title.active {
            color: #0d6efd;
            font-weight: 600;
        }

        .step-title.completed {
            color: #198754;
        }

        /* Step content styles */
        .step-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .step-content.active {
            display: block;
        }

        /* Table styles */
        #computersTable thead th {
            background-color: #f8f9fa;
            text-align: center;
        }

        #computersTable td {
            vertical-align: middle;
        }

        #computersTable {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        /* Form styles */
        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            border-color: #86b7fe;
        }

        /* File upload styles */
        .file-upload {
            border: 2px dashed #dee2e6;
            border-radius: 5px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .file-upload:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .file-upload i {
            font-size: 2.5rem;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .file-name {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #495057;
        }

        /* Summary card styles */
        .summary-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 15px;
        }

        .summary-card .card-body {
            padding: 15px;
        }

        /* Button styles */
        .btn-action {
            margin-right: 5px;
            margin-bottom: 5px;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Completion page */
        .completion-page {
            text-align: center;
            padding: 40px 20px;
        }

        .completion-icon {
            font-size: 5rem;
            color: #198754;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-5">
        <?php if ($current_step === 'complete'): ?>
            <!-- Completion Page -->
            <div class="step-container">
                <div class="completion-page">
                    <i class="fas fa-check-circle completion-icon"></i>
                    <h2 class="mb-3">Submission Complete!</h2>
                    <p class="lead">Your computers have been successfully added to the inventory.</p>
                    <div class="mt-4">
                        <a href="manage_computers.php?step=1" class="btn btn-primary me-2">
                            <i class="fas fa-plus me-2"></i>Add More Computers
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-tachometer-alt me-2"></i>Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Step Form Container -->
            <div class="step-container">
                <!-- Step Indicators -->
                <div class="step-indicator px-3 py-5">
                    <div class="step-item">
                        <div class="step-circle <?= $current_step === '1' ? 'active' : ($current_step > '1' ? 'completed' : '') ?>">1</div>
                        <div class="step-title <?= $current_step === '1' ? 'active' : ($current_step > '1' ? 'completed' : '') ?>">Instructions</div>
                    </div>
                    <div class="step-item">
                        <div class="step-circle <?= $current_step === '2' ? 'active' : ($current_step > '2' ? 'completed' : '') ?>">2</div>
                        <div class="step-title <?= $current_step === '2' ? 'active' : ($current_step > '2' ? 'completed' : '') ?>">Add Computers</div>
                    </div>
                    <div class="step-item">
                        <div class="step-circle <?= $current_step === '3' ? 'active' : '' ?>">3</div>
                        <div class="step-title <?= $current_step === '3' ? 'active' : '' ?>">Review & Submit</div>
                    </div>
                </div>

                <!-- Step 1: Instructions -->
                <div class="step-content <?= $current_step === '1' ? 'active' : '' ?>" id="step1">
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <div class="text-center mb-5">
                                <h2 class="mb-3">Computer Inventory Management</h2>
                                <p class="lead">Follow these steps to add computers to your branch inventory</p>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Instructions</h5>
                                </div>
                                <div class="card-body">
                                    <ol class="mb-0">
                                        <li class="mb-2">Review the requirements and gather necessary information</li>
                                        <li class="mb-2">Add computers one by one in the next step</li>
                                        <li class="mb-2">Edit or remove computers as needed</li>
                                        <li class="mb-2">Review all information before final submission</li>
                                        <li>Upload any supporting documents with your submission</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Requirements</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="mb-0">
                                        <li class="mb-2"><strong>Asset Tag:</strong> Unique identifier for each computer</li>
                                        <li class="mb-2"><strong>Computer Type:</strong> Desktop, Laptop, Server, etc.</li>
                                        <li class="mb-2"><strong>Basic Specifications:</strong> Processor, RAM, Storage</li>
                                        <li class="mb-2"><strong>Status:</strong> Operational, Maintenance, Retired, etc.</li>
                                        <li><strong>Supporting Documents:</strong> Optional purchase receipts or warranty info</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <a href="manage_computers.php?step=2" class="btn btn-primary btn-lg px-5">
                                    Get Started <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Add Computers -->
                <div class="step-content <?= $current_step === '2' ? 'active' : '' ?>" id="step2">
                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div class="w-100 d-flex justify-content-between align-items-center">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addComputerModal">
                                <i class="fas fa-plus me-2"></i>Add Computer
                            </button>

                            <a href="manage_computers.php?step=3" class="btn btn-primary">
                                Review & Submit <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Entered Inventory Computers</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="computersTable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Asset Tag</th>
                                            <th>Type</th>
                                            <th>Computer Name</th>
                                            <th>Brand/Model</th>
                                            <th>Specs</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($db_computers as $computer): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($computer['asset_tag']) ?></td>
                                                <td><?= htmlspecialchars($computer['type_name']) ?></td>
                                                <td><?= htmlspecialchars($computer['computer_name']) ?></td>
                                                <td>
                                                    <?= htmlspecialchars($computer['brand']) ?>
                                                    <?= $computer['model'] ? ' / ' . htmlspecialchars($computer['model']) : '' ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($computer['processor']) ?><br>
                                                    RAM: <?= htmlspecialchars($computer['ram']) ?><br>
                                                    Storage: <?= htmlspecialchars($computer['storage']) ?>
                                                </td>
                                                <td><?= htmlspecialchars($computer['status']) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary edit-btn"
                                                        data-id="<?= $computer['computer_id'] ?>"
                                                        data-asset-tag="<?= htmlspecialchars($computer['asset_tag']) ?>"
                                                        data-type-id="<?= $computer['type_id'] ?>"
                                                        data-brand="<?= htmlspecialchars($computer['brand']) ?>"
                                                        data-model="<?= htmlspecialchars($computer['model']) ?>"
                                                        data-computer-name="<?= htmlspecialchars($computer['computer_name']) ?>"
                                                        data-processor="<?= htmlspecialchars($computer['processor']) ?>"
                                                        data-ram="<?= htmlspecialchars($computer['ram']) ?>"
                                                        data-storage="<?= htmlspecialchars($computer['storage']) ?>"
                                                        data-os="<?= htmlspecialchars($computer['os']) ?>"
                                                        data-status="<?= htmlspecialchars($computer['status']) ?>"
                                                        data-notes="<?= htmlspecialchars($computer['notes']) ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="delete_id" value="<?= $computer['computer_id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Review & Submit -->
                <div class="step-content <?= $current_step === '3' ? 'active' : '' ?>" id="step3">
                    <div class="row">
                        <div class="col-lg-10 mx-auto">
                            <div class="text-center mb-5">
                                <h2 class="mb-3">Review & Submit</h2>
                                <p class="lead">Please review all computers before final submission</p>
                            </div>



                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>Supporting Documents</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Upload Supporting Documents (Optional)</label>
                                        <div class="file-upload" onclick="document.getElementById('fileInput').click()">
                                            <i class="bi bi-cloud-arrow-up"></i>
                                            <p>Click to upload or drag and drop</p>
                                            <p class="text-muted">PDF, JPG, PNG up to 5MB</p>
                                            <div class="file-name" id="fileName"></div>
                                        </div>
                                        <input type="file" id="fileInput" name="document" style="display: none;" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Confirmation</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="confirmation" required>
                                        <label class="form-check-label" for="confirmation">
                                            I confirm that all information provided is accurate and complete
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="manage_computers.php?step=2" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </a>
                                <form method="POST" enctype="multipart/form-data" class="d-inline">
                                    <input type="hidden" name="final_submit" value="1">
                                    <button type="submit" class="btn btn-success px-4" id="submitBtn" disabled>
                                        <i class="fas fa-check-circle me-2"></i>Submit All Computers
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Computer Modal -->
    <div class="modal fade" id="addComputerModal" tabindex="-1" aria-labelledby="addComputerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="add_computer" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addComputerModalLabel">Add New Computer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Asset Tag *</label>
                                <input type="text" class="form-control" name="asset_tag" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Computer Type *</label>
                                <select class="form-select" name="type_id" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($types as $type): ?>
                                        <option value="<?= $type['type_id'] ?>"><?= htmlspecialchars($type['type_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Brand</label>
                                <input type="text" class="form-control" name="brand">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Model</label>
                                <input type="text" class="form-control" name="model">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Computer Name</label>
                                <input type="text" class="form-control" name="computer_name">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Processor</label>
                                <input type="text" class="form-control" name="processor">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RAM</label>
                                <input type="text" class="form-control" name="ram">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Storage</label>
                                <input type="text" class="form-control" name="storage">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Operating System</label>
                                <input type="text" class="form-control" name="os">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" name="status">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Computer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <!-- Computer Modal -->
    <div class="modal fade" id="computerModal" tabindex="-1" aria-labelledby="computerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="computerModalLabel">Add New Computer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="computer_id" id="computer_id">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Asset Tag *</label>
                                <input type="text" class="form-control" name="asset_tag" id="asset_tag" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Computer Type *</label>
                                <select class="form-select" name="type_id" id="type_id" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($types as $type): ?>
                                        <option value="<?= $type['type_id'] ?>"><?= htmlspecialchars($type['type_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Brand</label>
                                <input type="text" class="form-control" name="brand" id="brand">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Model</label>
                                <input type="text" class="form-control" name="model" id="model">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Computer Name</label>
                                <input type="text" class="form-control" name="computer_name" id="computer_name">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Processor</label>
                                <input type="text" class="form-control" name="processor" id="processor">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RAM</label>
                                <input type="text" class="form-control" name="ram" id="ram">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Storage</label>
                                <input type="text" class="form-control" name="storage" id="storage">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Operating System</label>
                                <input type="text" class="form-control" name="os" id="os">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" name="status" id="status">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Computer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#computersTable').DataTable();

            // Edit button handler
            $('.edit-btn').click(function() {
                var modal = $('#computerModal');

                // Populate form from data attributes
                $('#computer_id').val($(this).data('id'));
                $('#asset_tag').val($(this).data('asset-tag'));
                $('#type_id').val($(this).data('type-id'));
                $('#brand').val($(this).data('brand'));
                $('#model').val($(this).data('model'));
                $('#computer_name').val($(this).data('computer-name'));
                $('#processor').val($(this).data('processor'));
                $('#ram').val($(this).data('ram'));
                $('#storage').val($(this).data('storage'));
                $('#os').val($(this).data('os'));
                $('#status').val($(this).data('status'));
                $('#notes').val($(this).data('notes'));

                // Update modal title
                modal.find('.modal-title').text('Edit Computer');

                // Show modal
                modal.modal('show');
            });

            // File upload display
            $('#fileInput').change(function() {
                if (this.files.length > 0) {
                    $('#fileName').text(this.files[0].name);
                } else {
                    $('#fileName').text('');
                }
            });

            // Drag and drop for file upload
            $('.file-upload').on('dragover', function(e) {
                e.preventDefault();
                $(this).css('border-color', '#0d6efd');
                $(this).css('background-color', '#f8f9fa');
            });

            $('.file-upload').on('dragleave', function(e) {
                e.preventDefault();
                $(this).css('border-color', '#dee2e6');
                $(this).css('background-color', '');
            });

            $('.file-upload').on('drop', function(e) {
                e.preventDefault();
                $(this).css('border-color', '#dee2e6');
                $(this).css('background-color', '');

                if (e.originalEvent.dataTransfer.files.length) {
                    $('#fileInput')[0].files = e.originalEvent.dataTransfer.files;
                    $('#fileName').text(e.originalEvent.dataTransfer.files[0].name);
                }
            });
        });

        // Reset form when adding new computer
        function resetForm() {
            $('#computerModal form')[0].reset();
            $('#computer_id').val('');
            $('#fileName').text('');
            $('#computerModalLabel').text('Add New Computer');
            currentStep = 0;
            showStep(currentStep);
        }

        // Step form functionality
        let currentStep = 0;
        const steps = $('.step');
        const stepIndicators = $('.step-circle');
        const stepTitles = $('.step-title');

        $('#nextBtn').click(function() {
            if (validateStep(currentStep)) {
                if (currentStep < steps.length - 1) {
                    currentStep++;
                    showStep(currentStep);

                    // If moving to final step, update review section
                    if (currentStep === 2) {
                        updateReviewSection();
                    }
                } else {
                    // Submit the form when on last step
                    if ($('#confirmation').is(':checked')) {
                        $('#computerModal form').submit();
                    } else {
                        alert('Please confirm that all information is accurate');
                    }
                }
            }
        });

        $('#prevBtn').click(function() {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        });

        function showStep(stepIndex) {
            // Hide all steps
            steps.removeClass('active');

            // Show current step
            $(steps[stepIndex]).addClass('active');

            // Update button states
            $('#prevBtn').prop('disabled', stepIndex === 0);

            if (stepIndex === steps.length - 1) {
                $('#nextBtn').html('Submit <i class="bi bi-check-circle ms-2"></i>');
            } else {
                $('#nextBtn').html('Next <i class="bi bi-arrow-right ms-2"></i>');
            }

            // Update step indicators
            stepIndicators.removeClass('active completed');
            stepTitles.removeClass('active completed');

            stepIndicators.each(function(index) {
                if (index < stepIndex) {
                    $(this).addClass('completed');
                    $(stepTitles[index]).addClass('completed');
                } else if (index === stepIndex) {
                    $(this).addClass('active');
                    $(stepTitles[index]).addClass('active');
                }
            });
        }

        function validateStep(stepIndex) {
            let isValid = true;

            // Validate step 1 (no validation needed for instructions)

            // Validate step 2 (computer details)
            if (stepIndex === 1) {
                if ($('#asset_tag').val().trim() === '') {
                    alert('Asset Tag is required');
                    isValid = false;
                }

                if ($('#type_id').val() === '') {
                    alert('Computer Type is required');
                    isValid = false;
                }
            }

            return isValid;
        }

        function updateReviewSection() {
            $('#review_asset_tag').text($('#asset_tag').val());
            $('#review_type').text($('#type_id option:selected').text());
            $('#review_brand_model').text($('#brand').val() + ($('#model').val() ? ' / ' + $('#model').val() : ''));
            $('#review_name').text($('#computer_name').val());
            $('#review_processor').text($('#processor').val());
            $('#review_ram').text($('#ram').val());
            $('#review_storage').text($('#storage').val());
            $('#review_status').text($('#status').val());
        }
    </script>
</body>

</html>