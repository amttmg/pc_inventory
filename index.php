<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Step Form</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: fadeIn 0.5s ease;
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
            width: 32px;
            height: 32px;
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
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
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
        }

        .step-title.active {
            color: #0d6efd;
            font-weight: 500;
        }

        .step-title.completed {
            color: #198754;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        .form-card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background-color: #f8f9fa;
            border-radius: 10px 10px 0 0 !important;
        }

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

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            border-color: #86b7fe;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card form-card">
                    <div class="card-header bg-white py-3">
                        <h4 class="mb-0 text-center">Registration Form</h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Step Indicators -->
                        <div class="step-indicator px-3">
                            <div class="step-item">
                                <div class="step-circle active" id="step1-indicator">1</div>
                                <div class="step-title active">Personal Info</div>
                            </div>
                            <div class="step-item">
                                <div class="step-circle" id="step2-indicator">2</div>
                                <div class="step-title">Contact Details</div>
                            </div>
                            <div class="step-item">
                                <div class="step-circle" id="step3-indicator">3</div>
                                <div class="step-title">Preferences</div>
                            </div>
                        </div>

                        <!-- Step 1 -->
                        <!-- Step 1: Instructions -->
                        <div class="step active" id="step1">
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
                                        <a href="index.php?step=2" class="btn btn-primary btn-lg px-5">
                                            Get Started <i class="fas fa-arrow-right ms-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="step" id="step2">
                            <h5 class="mb-4 text-primary">Contact Details</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone">
                                </div>
                                <div class="col-12">
                                    <label for="address" class="form-label">Street Address</label>
                                    <input type="text" class="form-control" id="address">
                                </div>
                                <div class="col-md-4">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city">
                                </div>
                                <div class="col-md-4">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="state">
                                </div>
                                <div class="col-md-4">
                                    <label for="zip" class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" id="zip">
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="step" id="step3">
                            <h5 class="mb-4 text-primary">Preferences</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Communication Preferences</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailPref">
                                        <label class="form-check-label" for="emailPref">Email</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="smsPref">
                                        <label class="form-check-label" for="smsPref">SMS</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="phonePref">
                                        <label class="form-check-label" for="phonePref">Phone Calls</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="newsletter" class="form-label">Newsletter Frequency</label>
                                    <select class="form-select" id="newsletter">
                                        <option value="">Select an option</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#">terms and conditions</a>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="btn-container">
                            <button type="button" class="btn btn-outline-secondary px-4" id="prevBtn" disabled>
                                <i class="bi bi-arrow-left me-2"></i>Previous
                            </button>
                            <button type="button" class="btn btn-primary px-4" id="nextBtn">
                                Next <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const steps = document.querySelectorAll('.step');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const stepIndicators = document.querySelectorAll('.step-circle');
            const stepTitles = document.querySelectorAll('.step-title');

            let currentStep = 0;

            // Initialize the form
            showStep(currentStep);

            // Next button click handler
            nextBtn.addEventListener('click', function() {
                if (validateStep(currentStep)) {
                    if (currentStep < steps.length - 1) {
                        currentStep++;
                        showStep(currentStep);
                    } else {
                        // Form submission would go here
                        alert('Form submitted successfully!');
                        // document.querySelector('form').submit();
                    }
                }
            });

            // Previous button click handler
            prevBtn.addEventListener('click', function() {
                if (currentStep > 0) {
                    currentStep--;
                    showStep(currentStep);
                }
            });

            function showStep(stepIndex) {
                // Hide all steps
                steps.forEach(step => step.classList.remove('active'));

                // Show current step
                steps[stepIndex].classList.add('active');

                // Update button states
                prevBtn.disabled = stepIndex === 0;
                nextBtn.textContent = stepIndex === steps.length - 1 ? 'Submit' : 'Next';

                // Update step indicators
                stepIndicators.forEach((indicator, index) => {
                    indicator.classList.remove('active', 'completed');
                    if (index < stepIndex) {
                        indicator.classList.add('completed');
                    } else if (index === stepIndex) {
                        indicator.classList.add('active');
                    }
                });

                // Update step titles
                stepTitles.forEach((title, index) => {
                    title.classList.remove('active', 'completed');
                    if (index < stepIndex) {
                        title.classList.add('completed');
                    } else if (index === stepIndex) {
                        title.classList.add('active');
                    }
                });
            }

            function validateStep(stepIndex) {
                let isValid = true;
                const currentStepFields = steps[stepIndex].querySelectorAll('[required]');

                currentStepFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                // Special validation for email
                if (stepIndex === 1 && !validateEmail(document.getElementById('email').value)) {
                    document.getElementById('email').classList.add('is-invalid');
                    isValid = false;
                }

                return isValid;
            }

            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
        });
    </script>
</body>

</html>