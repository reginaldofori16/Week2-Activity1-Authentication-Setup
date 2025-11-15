// Checkout functionality
const CheckoutManager = {
    // API endpoint
    endpoint: 'actions/process_checkout_action.php',

    // Initialize checkout
    init: function() {
        this.bindEvents();
        this.loadCartSummary();
    },

    // Bind event listeners
    bindEvents: function() {
        // Simulate payment button
        $(document).on('click', '#simulatePaymentBtn', (e) => {
            e.preventDefault();
            this.showPaymentModal();
        });

        // Confirm payment button in modal
        $(document).on('click', '#confirmPaymentBtn', (e) => {
            e.preventDefault();
            this.processPayment();
        });

        // Cancel payment button in modal
        $(document).on('click', '#cancelPaymentBtn', (e) => {
            e.preventDefault();
            this.hidePaymentModal();
        });

        // Payment method selection
        $(document).on('change', 'input[name="payment_method"]', (e) => {
            this.updatePaymentForm($(e.currentTarget).val());
        });

        // Modal close events
        $('#paymentModal').on('hidden.bs.modal', () => {
            this.resetPaymentForm();
        });
    },

    // Load cart summary
    loadCartSummary: function() {
        $.ajax({
            url: 'actions/get_cart_summary.php',
            method: 'GET',
            dataType: 'json',
            success: (response) => {
                if (response.status === 'success') {
                    this.updateCheckoutSummary(response.data);
                } else {
                    // Redirect to cart if empty
                    if (response.message && response.message.includes('empty')) {
                        window.location.href = 'cart.php';
                    }
                }
            },
            error: () => {
                this.showNotification('Failed to load cart summary', 'error');
            }
        });
    },

    // Update checkout summary display
    updateCheckoutSummary: function(data) {
        // Update items list
        const itemsContainer = $('.checkout-items');
        if (itemsContainer.length && data.items) {
            let itemsHtml = '';
            data.items.forEach(item => {
                itemsHtml += `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-0">${item.title}</h6>
                            <small class="text-muted">Qty: ${item.quantity} × $${item.price}</small>
                        </div>
                        <strong>$${item.subtotal}</strong>
                    </div>
                `;
            });
            itemsContainer.html(itemsHtml);
        }

        // Update totals
        if (data.summary) {
            const subtotal = data.summary.total_amount || 0;
            const tax = (subtotal * 0.1).toFixed(2);
            const total = (subtotal * 1.1).toFixed(2);

            $('.checkout-subtotal').text(`$${subtotal.toFixed(2)}`);
            $('.checkout-tax').text(`$${tax}`);
            $('.checkout-total').text(`$${total}`);
        }

        // Store data for later use
        this.cartData = data;
    },

    // Show payment modal
    showPaymentModal: function() {
        $('#paymentModal').modal('show');
    },

    // Hide payment modal
    hidePaymentModal: function() {
        $('#paymentModal').modal('hide');
    },

    // Update payment form based on selected method
    updatePaymentForm: function(method) {
        const cardForm = $('#cardPaymentForm');
        const paypalForm = $('#paypalPaymentForm');

        if (method === 'card') {
            cardForm.show();
            paypalForm.hide();
        } else if (method === 'paypal') {
            cardForm.hide();
            paypalForm.show();
        } else {
            cardForm.hide();
            paypalForm.hide();
        }
    },

    // Process payment
    processPayment: function() {
        const btn = $('#confirmPaymentBtn');
        const originalText = btn.html();

        // Disable button and show loading
        btn.prop('disabled', true)
           .html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        // Simulate payment processing delay
        setTimeout(() => {
            // Submit order
            $.ajax({
                url: this.endpoint,
                method: 'POST',
                dataType: 'json',
                success: (response) => {
                    if (response.status === 'success') {
                        this.handlePaymentSuccess(response);
                    } else {
                        this.handlePaymentError(response.message);
                    }
                },
                error: () => {
                    this.handlePaymentError('Payment processing failed. Please try again.');
                },
                complete: () => {
                    // Restore button
                    btn.prop('disabled', false).html(originalText);
                }
            });
        }, 2000); // 2 second delay to simulate processing
    },

    // Handle successful payment
    handlePaymentSuccess: function(response) {
        // Hide payment modal
        this.hidePaymentModal();

        // Show success modal with order details
        this.showOrderConfirmation(response);

        // Reset cart badge
        if (window.CartManager) {
            CartManager.updateCartBadge(0);
        }
    },

    // Handle payment error
    handlePaymentError: function(message) {
        this.showNotification(message || 'Payment failed', 'error');
        this.hidePaymentModal();
    },

    // Show order confirmation
    showOrderConfirmation: function(orderData) {
        const modal = $('#orderConfirmationModal');
        const content = $('#orderConfirmationContent');

        content.html(`
            <div class="text-center">
                <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Order Confirmed!</h4>
                <p class="text-muted">Thank you for your purchase</p>

                <div class="bg-light p-3 rounded mt-4 text-start">
                    <div class="row mb-2">
                        <div class="col-6"><strong>Order ID:</strong></div>
                        <div class="col-6">#${orderData.order_id}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Invoice No:</strong></div>
                        <div class="col-6">${orderData.invoice_no}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Date:</strong></div>
                        <div class="col-6">${new Date().toLocaleDateString()}</div>
                    </div>
                    <div class="row">
                        <div class="col-6"><strong>Total Amount:</strong></div>
                        <div class="col-6">$${orderData.total_amount}</div>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-muted small">A confirmation email has been sent to your registered email address</p>
                </div>
            </div>
        `);

        // Show modal
        modal.modal('show');

        // Redirect to home page after closing modal
        modal.on('hidden.bs.modal', () => {
            window.location.href = 'index.php';
        });
    },

    // Reset payment form
    resetPaymentForm: function() {
        // Reset payment method selection
        $('input[name="payment_method"][value="card"]').prop('checked', true);
        this.updatePaymentForm('card');

        // Clear form fields
        $('#cardNumber').val('');
        $('#cardName').val('');
        $('#cardExpiry').val('');
        $('#cardCVV').val('');
    },

    // Show notification
    showNotification: function(message, type = 'info') {
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

// Initialize checkout when document is ready
$(document).ready(function() {
    CheckoutManager.init();
});

// Export for use in other scripts
window.CheckoutManager = CheckoutManager;