<?php
require_once __DIR__ . '/../settings/db_class.php';

class CategoryClass {
    // Add a new category (CREATE)
    public static function add($name) {
        $db = new db_connection();
        $conn = $db->db_conn();
        if (!$conn) return false;

        $stmt = $conn->prepare("INSERT INTO categories (cat_name) VALUES (?)");
        if (!$stmt) return false;
        $stmt->bind_param("s", $name);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // Get all categories (RETRIEVE)
    public static function get_all() {
        $db = new db_connection();
        $sql = "SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC";
        $rows = $db->db_fetch_all($sql);
        return $rows === false ? [] : $rows;
    }

    // Get category by ID (RETRIEVE)
    public static function get_by_id($id) {
        $db = new db_connection();
        $conn = $db->db_conn();
        if (!$conn) return false;

        $stmt = $conn->prepare("SELECT cat_id, cat_name FROM categories WHERE cat_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // Update category name (UPDATE)
    public static function update($id, $name) {
        $db = new db_connection();
        $conn = $db->db_conn();
        if (!$conn) return false;

        $stmt = $conn->prepare("UPDATE categories SET cat_name = ? WHERE cat_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $name, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // Delete category (DELETE)
    public static function delete($id) {
        $db = new db_connection();
        $conn = $db->db_conn();
        if (!$conn) return false;

        $stmt = $conn->prepare("DELETE FROM categories WHERE cat_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // Check if category exists (for uniqueness)
    public static function exists($name) {
        $db = new db_connection();
        $conn = $db->db_conn();
        if (!$conn) return false;

        $stmt = $conn->prepare("SELECT cat_id FROM categories WHERE cat_name = ?");
        if (!$stmt) return false;
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }
}
?>
