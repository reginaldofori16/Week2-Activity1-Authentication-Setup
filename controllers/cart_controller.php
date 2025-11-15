<?php
require_once __DIR__ . '/../classes/cart_class.php';

class CartController
{
    /**
     * Add product to cart
     */
    public static function add_to_cart_ctr($product_id, $quantity = 1)
    {
        $cart = new CartClass();
        return $cart->addToCart($product_id, $quantity);
    }

    /**
     * Update cart item quantity
     */
    public static function update_cart_quantity_ctr($product_id, $quantity)
    {
        $cart = new CartClass();
        return $cart->updateQuantity($product_id, $quantity);
    }

    /**
     * Remove item from cart
     */
    public static function remove_from_cart_ctr($product_id)
    {
        $cart = new CartClass();
        return $cart->removeFromCart($product_id);
    }

    /**
     * Get all cart items
     */
    public static function get_cart_items_ctr()
    {
        $cart = new CartClass();
        return $cart->getCartItems();
    }

    /**
     * Get cart summary
     */
    public static function get_cart_summary_ctr()
    {
        $cart = new CartClass();
        return $cart->getCartSummary();
    }

    /**
     * Empty cart
     */
    public static function empty_cart_ctr()
    {
        $cart = new CartClass();
        return $cart->emptyCart();
    }

    /**
     * Get cart item count
     */
    public static function get_cart_item_count_ctr()
    {
        $cart = new CartClass();
        return $cart->getCartItemCount();
    }

    /**
     * Check if product is in cart
     */
    public static function is_product_in_cart_ctr($product_id)
    {
        $cart = new CartClass();
        return $cart->isInCart($product_id);
    }

    /**
     * Migrate guest cart to user cart
     */
    public static function migrate_guest_cart_ctr($customer_id)
    {
        $cart = new CartClass();
        return $cart->migrateGuestCart($customer_id);
    }

    /**
     * Get cart items with formatted data for display
     */
    public static function get_formatted_cart_items_ctr()
    {
        $cart = new CartClass();
        $items = $cart->getCartItems();
        $summary = $cart->getCartSummary();

        return [
            'items' => $items,
            'summary' => $summary,
            'formatted_items' => array_map(function($item) {
                return [
                    'product_id' => $item['p_id'],
                    'title' => htmlspecialchars($item['product_title']),
                    'price' => number_format($item['product_price'], 2),
                    'quantity' => $item['qty'],
                    'subtotal' => number_format($item['subtotal'], 2),
                    'image' => $item['product_image'],
                    'category' => $item['cat_name'] ?: 'No Category',
                    'brand' => $item['brand_name'] ?: 'No Brand'
                ];
            }, $items)
        ];
    }

    /**
     * Validate cart before checkout
     */
    public static function validate_cart_before_checkout_ctr()
    {
        $cart = new CartClass();
        $items = $cart->getCartItems();

        if (empty($items)) {
            return [
                'valid' => false,
                'message' => 'Your cart is empty'
            ];
        }

        // Check if all products are still available
        foreach ($items as $item) {
            if ($item['product_price'] <= 0) {
                return [
                    'valid' => false,
                    'message' => 'One or more items in your cart are no longer available'
                ];
            }
        }

        return [
            'valid' => true,
            'message' => 'Cart is valid for checkout'
        ];
    }
}