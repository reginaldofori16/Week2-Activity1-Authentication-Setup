// Cart functionality
const CartManager = {
    // API endpoints
    endpoints: {
        add: 'actions/add_to_cart_action.php',
        remove: 'actions/remove_from_cart_action.php',
        update: 'actions/update_quantity_action.php',
        empty: 'actions/empty_cart_action.php'
    },

    // Initialize cart functionality
    init: function() {
        this.bindEvents();
        this.updateCartBadge();
    },

    // Bind event listeners
    bindEvents: function() {
        // Add to cart buttons
        $(document).on('click', '.btn-add-to-cart', (e) => {
            e.preventDefault();
            const btn = $(e.currentTarget);
            const productId = btn.data('product-id');
            const quantity = btn.data('quantity') || 1;
            this.addToCart(productId, quantity, btn);
        });

        // Remove from cart buttons
        $(document).on('click', '.btn-remove-item', (e) => {
            e.preventDefault();
            const productId = $(e.currentTarget).data('product-id');
            this.removeFromCart(productId);
        });

        // Update quantity inputs
        $(document).on('change', '.qty-input', (e) => {
            const input = $(e.currentTarget);
            const productId = input.data('product-id');
            let quantity = parseInt(input.val()) || 1;

            // Validate quantity
            if (quantity < 1) quantity = 1;
            if (quantity > 100) quantity = 100;

            input.val(quantity);
            this.updateQuantity(productId, quantity);
        });

        // Empty cart button
        $(document).on('click', '#emptyCartBtn', (e) => {
            e.preventDefault();
            this.emptyCart();
        });

        // Quantity adjustment buttons
        $(document).on('click', '.qty-decrease', (e) => {
            const btn = $(e.currentTarget);
            const input = btn.siblings('.qty-input');
            let quantity = parseInt(input.val()) || 1;
            if (quantity > 1) {
                quantity--;
                input.val(quantity);
                this.updateQuantity(input.data('product-id'), quantity);
            }
        });

        $(document).on('click', '.qty-increase', (e) => {
            const btn = $(e.currentTarget);
            const input = btn.siblings('.qty-input');
            let quantity = parseInt(input.val()) || 1;
            if (quantity < 100) {
                quantity++;
                input.val(quantity);
                this.updateQuantity(input.data('product-id'), quantity);
            }
        });
    },

    // Add product to cart
    addToCart: function(productId, quantity, button) {
        // Show loading state
        if (button) {
            const originalText = button.html();
            button.prop('disabled', true)
                   .html('<span class="spinner-border spinner-border-sm me-2"></span>Adding...');
        }

        $.ajax({
            url: this.endpoints.add,
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: (response) => {
                if (response.status === 'success') {
                    this.showNotification('Product added to cart!', 'success');
                    this.updateCartBadge(response.cart_count);

                    // If on cart page, refresh cart display
                    if (window.location.pathname.includes('cart.php')) {
                        this.refreshCartDisplay();
                    }
                } else {
                    this.showNotification(response.message || 'Failed to add to cart', 'error');
                }
            },
            error: () => {
                this.showNotification('Network error. Please try again.', 'error');
            },
            complete: () => {
                // Restore button state
                if (button) {
                    button.prop('disabled', false)
                          .html(originalText || '<i class="bi bi-cart-plus"></i> Add to Cart');
                }
            }
        });
    },

    // Remove item from cart
    removeFromCart: function(productId) {
        if (!confirm('Are you sure you want to remove this item from cart?')) {
            return;
        }

        $.ajax({
            url: this.endpoints.remove,
            method: 'POST',
            data: {
                product_id: productId
            },
            dataType: 'json',
            success: (response) => {
                if (response.status === 'success') {
                    this.showNotification('Item removed from cart', 'success');
                    this.updateCartBadge(response.cart_count);

                    // Remove item row with animation
                    $(`#cart-item-${productId}`).fadeOut(300, function() {
                        $(this).remove();

                        // Update totals
                        CartManager.updateCartTotals(response.cart_total);

                        // Check if cart is empty
                        if (response.cart_count === 0) {
                            CartManager.showEmptyCart();
                        }
                    });
                } else {
                    this.showNotification(response.message || 'Failed to remove item', 'error');
                }
            },
            error: () => {
                this.showNotification('Network error. Please try again.', 'error');
            }
        });
    },

    // Update item quantity
    updateQuantity: function(productId, quantity) {
        // Show loading state
        const input = $(`.qty-input[data-product-id="${productId}"]`);
        input.addClass('loading');

        $.ajax({
            url: this.endpoints.update,
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: (response) => {
                if (response.status === 'success') {
                    this.updateCartBadge(response.cart_count);

                    // Update item subtotal
                    const subtotalCell = $(`#subtotal-${productId}`);
                    if (subtotalCell.length) {
                        subtotalCell.text(`$${response.item_subtotal}`);
                    }

                    // Update cart totals
                    this.updateCartTotals(response.cart_total);
                } else {
                    this.showNotification(response.message || 'Failed to update quantity', 'error');
                    // Reset to previous quantity on error
                    this.refreshCartDisplay();
                }
            },
            error: () => {
                this.showNotification('Network error. Please try again.', 'error');
                this.refreshCartDisplay();
            },
            complete: () => {
                input.removeClass('loading');
            }
        });
    },

    // Empty cart
    emptyCart: function() {
        if (!confirm('Are you sure you want to empty your cart?')) {
            return;
        }

        $.ajax({
            url: this.endpoints.empty,
            method: 'POST',
            dataType: 'json',
            success: (response) => {
                if (response.status === 'success') {
                    this.showNotification('Cart emptied successfully', 'success');
                    this.updateCartBadge(0);
                    this.showEmptyCart();
                } else {
                    this.showNotification(response.message || 'Failed to empty cart', 'error');
                }
            },
            error: () => {
                this.showNotification('Network error. Please try again.', 'error');
            }
        });
    },

    // Update cart badge in navigation
    updateCartBadge: function(count = null) {
        if (count === null) {
            // Fetch current count from server
            $.ajax({
                url: 'actions/get_cart_count.php',
                method: 'GET',
                dataType: 'json',
                success: (response) => {
                    if (response.status === 'success') {
                        this.updateCartBadge(response.cart_count);
                    }
                }
            });
            return;
        }

        const badge = $('.cart-badge');
        if (badge.length) {
            if (count > 0) {
                badge.text(count).show();
            } else {
                badge.hide();
            }
        }
    },

    // Update cart totals
    updateCartTotals: function(total) {
        $('.cart-total').text(`$${total}`);
        $('.cart-subtotal').text(`$${total}`);

        // Calculate tax (10%) and total with tax
        const tax = (parseFloat(total) * 0.1).toFixed(2);
        const grandTotal = (parseFloat(total) * 1.1).toFixed(2);

        $('.cart-tax').text(`$${tax}`);
        $('.cart-grand-total').text(`$${grandTotal}`);
    },

    // Refresh cart display
    refreshCartDisplay: function() {
        if (window.location.pathname.includes('cart.php')) {
            location.reload();
        }
    },

    // Show empty cart message
    showEmptyCart: function() {
        const cartContainer = $('.cart-items-container');
        if (cartContainer.length) {
            cartContainer.html(`
                <div class="text-center py-5">
                    <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">Your cart is empty</h4>
                    <p class="text-muted">Add some products to your cart to see them here</p>
                    <a href="all_product.php" class="btn btn-primary mt-3">
                        <i class="bi bi-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            `);

            // Hide cart summary
            $('.cart-summary').hide();
        }
    },

    // Show notification
    showNotification: function(message, type = 'info') {
        // Use SweetAlert2 if available, otherwise use alert
        if (typeof Swal !== 'undefined') {
            const icon = type === 'success' ? 'success' :
                         type === 'error' ? 'error' : 'info';

            Swal.fire({
                icon: icon,
                title: type === 'success' ? 'Success!' : 'Error',
                text: message,
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }
};

// Initialize cart when document is ready
$(document).ready(function() {
    CartManager.init();
});

// Export for use in other scripts
window.CartManager = CartManager;