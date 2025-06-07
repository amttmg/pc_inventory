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

    header("Location: manage_computers.php");
    exit();
}

// Get computers for this branch
$computers_stmt = $pdo->prepare("
    SELECT c.*, ct.type_name 
    FROM computers c
    JOIN computer_types ct ON c.type_id = ct.type_id
    WHERE c.branch_id = ?
");
$computers_stmt->execute([$branch['id']]);
$computers = $computers_stmt->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Computers - <?= htmlspecialchars($branch['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            @page {
                size: landscape;
            }

            /* Hide all buttons and forms during print */
            .btn,
            form,
            .dataTables_length,
            .dataTables_filter,
            .dataTables_info,
            .dataTables_paginate {
                display: none !important;
            }

            /* Hide "Actions" column */
            th:last-child,
            td:last-child {
                display: none !important;
            }

            /* Optional: Adjust table layout for printing */
            table {
                border: 1px solid #000 !important;
                border-collapse: collapse !important;
                width: 100% !important;
                font-size: 12pt;
            }

            table th,
            table td {
                border: 1px solid #000 !important;
                padding: 8px !important;
            }

            /* Optional: Hide modal */
            .modal {
                display: none !important;
            }

            .print-footer {
                display: block !important;
                position: fixed;
                bottom: 50px;
                left: 0;
                right: 0;
                padding: 0 40px;
            }
        }

        /* Improve table design in normal view */
        #computersTable thead th {
            background-color: #f8f9fa;
            text-align: center;
        }

        #computersTable td {
            vertical-align: middle;
        }

        /* Center the page title */
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        /* Add subtle box shadow to the table */
        #computersTable {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .print-footer {
            display: none;
        }
    </style>

</head>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col">
                <div class="mb-3 text-center position-relative">
                    <h2 class="mb-0"><?= htmlspecialchars($branch['name']) ?> - Computer Inventory</h2>
                    <a href="logout.php" class="position-absolute top-0 end-0 mt-1 me-2 text-danger" title="Logout" style="font-size: 1.5rem;">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
                <div class="my-3">
                    <button class="btn btn-primary me-2" onclick="resetForm()" data-bs-toggle="modal" data-bs-target="#computerModal">
                        <i class="fas fa-plus"></i> Add New Computer
                    </button>
                    <button class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Page
                    </button>
                </div>

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
                        <?php foreach ($computers as $computer): ?>
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
    <div class="print-footer">
        <div style="display: flex; justify-content: space-between; margin-top: 100px;">
            <div style="text-align: center;">
                <p>______________________________</p>
                <p>IT Focal Person</p>
            </div>
            <div style="text-align: center;">
                <p>______________________________</p>
                <p>Branch Manager</p>
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

            // Edit button handler - Now using data attributes
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
        });

        // Reset form when adding new computer
        function resetForm() {
            $('#computerModal form')[0].reset();
            $('#computer_id').val('');
            $('#computerModalLabel').text('Add New Computer');
        }
    </script>
</body>

</html>