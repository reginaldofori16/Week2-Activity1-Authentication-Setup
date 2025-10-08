<?php
// Use this file's directory so includes always resolve correctly
require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    redirect_to(LOGIN_PAGE);  // Redirect to login if not logged in or not an admin
    exit;
}

// The category list is loaded by AJAX (js/category.js) from actions/fetch_category_action.php
// Controllers and fetching are handled client-side to keep the HTML minimal.
require_once __DIR__ . '/../controllers/category_controller.php';


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1>Manage Categories</h1>

    <!-- Button to create new category -->
    <div style="margin-bottom:1rem;display:flex;align-items:center;gap:1rem;">


        <!-- Inline add-category form -->
        <form method="post" style="display:inline-block;" name="add-category-form" id="add-category-form">
            <input type="text" name="category_name" placeholder="New category name" required
                style="padding:.4rem;margin-right:.4rem;" />
            <button type="submit"
                style="padding:.4rem .6rem;background:#28a745;color:#fff;border:0;border-radius:4px;">Add</button>
        </form>
    </div>

    <!-- Category table — initially empty; JS will populate via AJAX -->
    <table id="categories-table" style="border-collapse:collapse;width:100%;max-width:800px;">
        <thead>
            <tr>
                <th style="border:1px solid #ddd;padding:.5rem;text-align:left;">ID</th>
                <th style="border:1px solid #ddd;padding:.5rem;text-align:left;">Category Name</th>
                <th style="border:1px solid #ddd;padding:.5rem;text-align:left;">Action</th>
            </tr>
        </thead>
        <tbody id="categories-tbody">
            <!-- populated by js/category.js -->
        </tbody>
    </table>

</body>

</html>

<!-- Include jQuery, SweetAlert2, and category JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // API endpoints used by the JS; generated server-side so paths follow BASE_URL
    var CATEGORY_ENDPOINTS = {
        fetch: '<?php echo htmlspecialchars(generate_url("actions/fetch_category_action.php")); ?>',
        add: '<?php echo htmlspecialchars(generate_url("actions/add_category_action.php")); ?>',
        update: '<?php echo htmlspecialchars(generate_url("actions/update_category_action.php")); ?>',
        delete: '<?php echo htmlspecialchars(generate_url("actions/delete_category_action.php")); ?>'
    };
</script>
<script src="../js/category.js"></script>