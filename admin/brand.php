<?php
// Use this file's directory so includes always resolve correctly
require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    redirect_to(LOGIN_PAGE);  // Redirect to login if not logged in or not an admin
    exit;
}

// The brand list is loaded by AJAX (js/brand.js) from actions/fetch_brand_action.php
// Controllers and fetching are handled client-side to keep the HTML minimal.
require_once __DIR__ . '/../controllers/brand_controller.php';


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        input[type="text"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 8px;
        }
        button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 8px;
        }
        .btn-add {
            background-color: #28a745;
            color: white;
        }
        .btn-add:hover {
            background-color: #218838;
        }
        .btn-save {
            background-color: #007bff;
            color: white;
        }
        .btn-save:hover {
            background-color: #0056b3;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .actions {
            white-space: nowrap;
        }
        .back-link {
            margin-bottom: 20px;
            display: inline-block;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <a href="../index.php" class="back-link">← Back to Home</a>

    <h1>Manage Brands</h1>

    <!-- Form to create new brand -->
    <div class="form-container">
        <form method="post" name="add-brand-form" id="add-brand-form">
            <input type="text" name="brand_name" placeholder="Enter brand name" required />
            <button type="submit" class="btn-add">Add Brand</button>
        </form>
    </div>

    <!-- Brands table — initially empty; JS will populate via AJAX -->
    <div class="table-container">
        <table id="brands-table">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Brand Name</th>
                    <th style="width: 300px;">Actions</th>
                </tr>
            </thead>
            <tbody id="brands-tbody">
                <!-- populated by js/brand.js -->
            </tbody>
        </table>
    </div>

</body>

</html>

<!-- Include jQuery, SweetAlert2, and brand JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // API endpoints used by the JS; generated server-side so paths follow BASE_URL
    var BRAND_ENDPOINTS = {
        fetch: '../actions/fetch_brand_action.php',
        add: '../actions/add_brand_action.php',
        update: '../actions/update_brand_action.php',
        delete: '../actions/delete_brand_action.php'
    };
</script>
<script src="../js/brand.js"></script>