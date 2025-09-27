<?php
class CategoryClass extends Database {
    // Add a new category (CREATE)
    public static function add($name) {
        $query = "INSERT INTO categories (cat_name) VALUES (?)";
        return self::executeQuery($query, [$name]);
    }

    // Get all categories (RETRIEVE)
    public static function get_all() {
        $query = "SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC";
        return self::fetchAll($query);
    }

    // Get category by ID (RETRIEVE)
    public static function get_by_id($id) {
        $query = "SELECT cat_id, cat_name FROM categories WHERE cat_id = ?";
        return self::fetchOne($query, [$id]);
    }

    // Update category name (UPDATE)
    public static function update($id, $name) {
        $query = "UPDATE categories SET cat_name = ? WHERE cat_id = ?";
        return self::executeQuery($query, [$name, $id]);
    }

    // Delete category (DELETE)
    public static function delete($id) {
        $query = "DELETE FROM categories WHERE cat_id = ?";
        return self::executeQuery($query, [$id]);
    }

    // Check if category exists (for uniqueness)
    public static function exists($name) {
        $query = "SELECT cat_id FROM categories WHERE cat_name = ?";
        return self::fetchOne($query, [$name]);
    }
}
?>
