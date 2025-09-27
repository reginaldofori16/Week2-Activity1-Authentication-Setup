<?php
// Use this file's directory so includes always resolve correctly
require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    redirect_to(LOGIN_PAGE);  // Redirect to login if not logged in or not an admin
    exit;
}

// Fetch categories — file is in actions/, not admin/
// Load category controller and fetch categories for rendering
require_once __DIR__ . '/../controllers/category_controller.php';

require_once __DIR__ . '/../settings/logger.php';

$categories = [];
if (is_logged_in() && is_admin()) {
    try {
        $categories = CategoryController::get_all_categories();
        if ($categories === false) {
            app_log('WARN', 'CategoryController::get_all_categories returned false');
            $categories = [];
        }
    } catch (Throwable $e) {
        // Log and show a friendly message
        app_log_exception($e);
        $categories = [];
        $load_error = true;
    }
}


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
    <form action="<?php echo htmlspecialchars(generate_url('actions/add_category_action.php')); ?>" method="post" style="display:inline-block;">
        <input type="text" name="category_name" placeholder="New category name" required style="padding:.4rem;margin-right:.4rem;" />
        <button type="submit" style="padding:.4rem .6rem;background:#28a745;color:#fff;border:0;border-radius:4px;">Add</button>
    </form>
</div>

<?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] === 'success'): ?>
        <div style="padding:.5rem;background:#e6ffed;border:1px solid #c8f5d0;color:#064;max-width:640px;margin-bottom:1rem;">Category added successfully.</div>
    <?php else: ?>
        <div style="padding:.5rem;background:#ffecec;border:1px solid #f5c6c6;color:#900;max-width:640px;margin-bottom:1rem;"><?php echo htmlspecialchars($_GET['msg'] ?? 'An error occurred'); ?></div>
    <?php endif; ?>
<?php endif; ?>

<?php if (empty($categories)): ?>
    <div style="padding:1rem;border:1px dashed #ccc;border-radius:6px;max-width:640px;">
        <h2>No categories yet</h2>
        <p>There are no categories in the database. Use the button above to add the first category.</p>
    </div>
    <?php if (!empty($load_error)): ?>
        <div style="margin-top:1rem;color:#a00;">
            <strong>Notice:</strong> An error occurred while loading categories. Check <code>logs/app.log</code> for details.
        </div>
    <?php endif; ?>
<?php else: ?>
    <!-- Display categories in a table -->
    <table style="border-collapse:collapse;width:100%;max-width:800px;">
        <thead>
            <tr>
                <th style="border:1px solid #ddd;padding:.5rem;text-align:left;">ID</th>
                <th style="border:1px solid #ddd;padding:.5rem;text-align:left;">Category Name</th>
                <th style="border:1px solid #ddd;padding:.5rem;text-align:left;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($categories ?? []) as $category): ?>
            <tr>
                <td style="border:1px solid #eee;padding:.5rem;vertical-align:top;"><?php echo htmlspecialchars($category['cat_id'] ?? $category['id'] ?? ''); ?></td>
                <td style="border:1px solid #eee;padding:.5rem;vertical-align:top;"><?php echo htmlspecialchars($category['cat_name'] ?? $category['name'] ?? ''); ?></td>
                <td style="border:1px solid #eee;padding:.5rem;vertical-align:top;">
                    <!-- Inline edit form -->
                    <form action="<?php echo htmlspecialchars(generate_url('actions/update_category_action.php')); ?>" method="post" style="display:inline-block;margin-right:.5rem;">
                        <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category['cat_id'] ?? $category['id'] ?? ''); ?>" />
                        <input type="text" name="category_name" value="<?php echo htmlspecialchars($category['cat_name'] ?? $category['name'] ?? ''); ?>" style="padding:.25rem;" />
                        <button type="submit" style="padding:.25rem .4rem;margin-left:.3rem;">Save</button>
                    </form>

                    <!-- Delete form -->
                    <form action="<?php echo htmlspecialchars(generate_url('actions/delete_category_action.php')); ?>" method="post" style="display:inline-block;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($category['cat_id'] ?? $category['id'] ?? ''); ?>" />
                        <button type="submit" style="padding:.25rem .4rem;background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
