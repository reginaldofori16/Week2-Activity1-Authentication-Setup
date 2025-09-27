<?php
// Use this file's directory so includes always resolve correctly
require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    redirect_to(LOGIN_PAGE);  // Redirect to login if not logged in or not an admin
    exit;
}

// Fetch categories — file is in actions/, not admin/
require_once __DIR__ . '/../actions/fetch_category_action.php';


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
<a href="add_category.php">Add New Category</a>

<!-- Display categories in a table -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $category): ?>
        <tr>
            <td><?php echo $category['id']; ?></td>
            <td><?php echo $category['cat_name']; ?></td>
            <td>
                <a href="update_category.php?id=<?php echo $category['id']; ?>">Edit</a> |
                <a href="delete_category.php?id=<?php echo $category['id']; ?>">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
