// jQuery + SweetAlert2 handlers for product management
$(function(){
    let editingProduct = null;

    // helper to render products table
    function renderProductRows(products) {
        let rows = '';
        if (products.length === 0) {
            rows = '<tr><td colspan="6" style="text-align:center;padding:20px;">No products found</td></tr>';
        } else {
            products.forEach(function(product){
                const id = product.product_id || product.id || '';
                const title = $('<div>').text(product.product_title || product.title || '').html();
                const category = $('<div>').text(product.cat_name || product.category_name || '').html();
                const brand = $('<div>').text(product.brand_name || product.brand || '').html();
                const price = parseFloat(product.product_price || product.price || 0).toFixed(2);
                const image = product.product_image || product.image || '';

                const imageHtml = image
                    ? `<img src="../${image}" class="product-image" alt="${title}" onerror="this.src='../uploads/placeholder.png'">`
                    : '<span style="color:#999;">No Image</span>';

                rows += `<tr>
                    <td>${imageHtml}</td>
                    <td><strong>${title}</strong></td>
                    <td>${category}</td>
                    <td>${brand}</td>
                    <td>$${price}</td>
                    <td class="actions">
                        <button type="button" class="btn-warning edit-product" data-id="${id}">Edit</button>
                        <button type="button" class="btn-danger delete-product" data-id="${id}">Delete</button>
                    </td>
                </tr>`;
            });
        }
        $('#products-tbody').html(rows);

        // Bind action handlers
        bindActionHandlers();
    }

    // Bind edit and delete button handlers
    function bindActionHandlers() {
        $('.edit-product').off('click').on('click', function(){
            const productId = $(this).data('id');
            loadProductForEdit(productId);
        });

        $('.delete-product').off('click').on('click', function(){
            const productId = $(this).data('id');
            const productTitle = $(this).closest('tr').find('td:nth-child(2) strong').text();

            Swal.fire({
                title: 'Delete Product?',
                html: `Are you sure you want to delete "<strong>${productTitle}</strong>"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancel'
            }).then(function(result){
                if (result.isConfirmed) {
                    deleteProduct(productId);
                }
            });
        });
    }

    // Load product data for editing
    function loadProductForEdit(productId) {
        $.ajax({
            url: PRODUCT_ENDPOINTS.fetch,
            method: 'GET',
            dataType: 'json'
        }).done(function(products){
            const product = products.find(p => (p.product_id || p.id) == productId);
            if (product) {
                editingProduct = product;
                populateEditForm(product);
                switchToEditMode();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Product not found'
                });
            }
        }).fail(function(){
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Failed to load product data'
            });
        });
    }

    // Populate form with product data
    function populateEditForm(product) {
        $('#product_id').val(product.product_id || product.id);
        $('#product_category').val(product.product_cat || product.category_id);
        $('#product_brand').val(product.product_brand || product.brand_id);
        $('#product_title').val(product.product_title || product.title);
        $('#product_price').val(product.product_price || product.price);
        $('#product_desc').val(product.product_desc || product.description || '');
        $('#product_keywords').val(product.product_keywords || product.keywords || '');

        // Show existing image if available
        if (product.product_image || product.image) {
            $('#image-preview').attr('src', '../' + (product.product_image || product.image)).show();
        } else {
            $('#image-preview').hide();
        }
    }

    // Switch to edit mode
    function switchToEditMode() {
        $('#form-title').text('Edit Product');
        $('#submit-btn').text('Update Product').removeClass('btn-primary').addClass('btn-success');
        $('#cancel-edit-btn').show();

        // Scroll to form
        $('html, body').animate({
            scrollTop: $('.form-container').offset().top - 20
        }, 500);
    }

    // Switch to add mode
    function switchToAddMode() {
        editingProduct = null;
        $('#product_id').val('');
        $('#product-form')[0].reset();
        $('#image-preview').hide();
        $('#form-title').text('Add New Product');
        $('#submit-btn').text('Add Product').removeClass('btn-success').addClass('btn-primary');
        $('#cancel-edit-btn').hide();
    }

    // Delete product
    function deleteProduct(productId) {
        $.ajax({
            url: PRODUCT_ENDPOINTS.delete,
            method: 'POST',
            data: { product_id: productId },
            dataType: 'json'
        }).done(function(resp){
            if (resp.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted',
                    text: resp.message || 'Product deleted successfully',
                    toast: true,
                    position: 'top-end',
                    timer: 1200,
                    showConfirmButton: false
                });
                fetchAndRender();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Delete Failed',
                    text: resp.message || 'Failed to delete product'
                });
            }
        }).fail(function(){
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Server error while deleting product'
            });
        });
    }

    // Fetch and render products
    function fetchAndRender(){
        $.ajax({
            url: PRODUCT_ENDPOINTS.fetch,
            method: 'GET',
            dataType: 'json'
        }).done(function(products){
            if (Array.isArray(products)){
                renderProductRows(products);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Fetch Failed',
                    text: 'Failed to fetch products'
                });
            }
        }).fail(function(){
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Server error while fetching products'
            });
        });
    }

    // Form submission handler
    $('#product-form').off('submit').on('submit', function(e){
        e.preventDefault();

        const formData = new FormData(this);
        const isEdit = editingProduct !== null;
        const url = isEdit ? PRODUCT_ENDPOINTS.update : PRODUCT_ENDPOINTS.add;

        // Validate form
        const category = $('#product_category').val();
        const brand = $('#product_brand').val();
        const title = $('#product_title').val().trim();
        const price = $('#product_price').val();

        if (!category || !brand || !title || !price) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fill in all required fields'
            });
            return;
        }

        if (isNaN(price) || parseFloat(price) <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Price must be a positive number'
            });
            return;
        }

        // AJAX submit
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function(resp){
            if (resp.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: isEdit ? 'Updated' : 'Added',
                    text: resp.message || `Product ${isEdit ? 'updated' : 'added'} successfully`,
                    toast: true,
                    position: 'top-end',
                    timer: 1200,
                    showConfirmButton: false
                });
                switchToAddMode();
                fetchAndRender();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: `${isEdit ? 'Update' : 'Add'} Failed`,
                    text: resp.message || `Failed to ${isEdit ? 'update' : 'add'} product`
                });
            }
        }).fail(function(){
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: `Server error while ${isEdit ? 'updating' : 'adding'} product`
            });
        });
    });

    // Cancel edit button handler
    $('#cancel-edit-btn').off('click').on('click', function(){
        switchToAddMode();
    });

    // Image preview handler
    $('#product_image').off('change').on('change', function(e){
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e){
                $('#image-preview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Initial load
    fetchAndRender();
});