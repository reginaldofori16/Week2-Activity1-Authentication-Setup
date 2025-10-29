// jQuery + SweetAlert2 handlers that refresh the brand list only
$(function(){
    // helper to render a table body from brand array
    function renderBrandRows(brands) {
        var rows = '';
        if (brands.length === 0) {
            rows = '<tr><td colspan="3" style="text-align:center;padding:20px;">No brands found</td></tr>';
        } else {
            brands.forEach(function(brand){
                var id = brand.brand_id || brand.id || '';
                var name = $('<div>').text(brand.brand_name || brand.name || '').html();
                rows += '<tr>' +
                    '<td>'+id+'</td>' +
                    '<td>'+name+'</td>' +
                    '<td class="actions">' +
                        '<form class="update-form" style="display:inline-block;margin-right:8px;">' +
                            '<input type="hidden" name="brand_id" value="'+id+'" />' +
                            '<input type="text" name="brand_name" value="'+name+'" style="padding:6px;border:1px solid #ddd;border-radius:4px;" required />' +
                            '<button type="submit" class="btn-save">Save</button>' +
                        '</form>' +
                        '<form class="delete-form" style="display:inline-block;">' +
                            '<input type="hidden" name="brand_id" value="'+id+'" />' +
                            '<button type="submit" class="btn-delete">Delete</button>' +
                        '</form>' +
                    '</td>' +
                '</tr>';
            });
        }
        $('table tbody').html(rows);

        // re-bind handlers
        bindRowHandlers();
    }

    function bindRowHandlers(){
        // Update
        $('form.update-form').off('submit').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            var data = $form.serialize();

            // Validate input
            var brandName = $form.find('input[name="brand_name"]').val().trim();
            if (brandName === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Brand name cannot be empty'
                });
                return;
            }

            $.ajax({
                url: BRAND_ENDPOINTS.update,
                method: 'POST',
                data: data,
                dataType: 'json'
            }).done(function(resp){
                if (resp.status === 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: resp.message || 'Brand updated successfully',
                        toast: true,
                        position: 'top-end',
                        timer: 1200,
                        showConfirmButton: false
                    });
                    fetchAndRender();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: resp.message || 'Failed to update brand'
                    });
                }
            }).fail(function(){
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Server error while updating brand'
                });
            });
        });

        // Delete
        $('form.delete-form').off('submit').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            var brandName = $form.closest('tr').find('td:nth-child(2)').text();

            Swal.fire({
                title: 'Delete Brand?',
                html: 'Are you sure you want to delete the brand "<strong>' + brandName + '</strong>?"',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancel'
            }).then(function(result){
                if (result.isConfirmed){
                    $.ajax({
                        url: BRAND_ENDPOINTS.delete,
                        method: 'POST',
                        data: $form.serialize(),
                        dataType: 'json'
                    }).done(function(resp){
                        if (resp.status === 'success'){
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: resp.message || 'Brand deleted successfully',
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
                                text: resp.message || 'Failed to delete brand'
                            });
                        }
                    }).fail(function(){
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Server error while deleting brand'
                        });
                    });
                }
            });
        });
    }

    function fetchAndRender(){
        $.ajax({
            url: BRAND_ENDPOINTS.fetch,
            method: 'GET',
            dataType: 'json'
        }).done(function(brands){
            if (Array.isArray(brands)){
                renderBrandRows(brands);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Fetch Failed',
                    text: 'Failed to fetch brands'
                });
            }
        }).fail(function(){
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Server error while fetching brands'
            });
        });
    }

    // Add handler for add form
    $('#add-brand-form').off('submit').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        var brandName = $form.find('input[name="brand_name"]').val().trim();

        // Validate input
        if (brandName === '') {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please enter a brand name'
            });
            return;
        }

        var data = $form.serialize();
        $.ajax({
            url: BRAND_ENDPOINTS.add,
            method: 'POST',
            data: data,
            dataType: 'json'
        }).done(function(resp){
            if (resp.status === 'success'){
                Swal.fire({
                    icon: 'success',
                    title: 'Added',
                    text: resp.message || 'Brand added successfully',
                    toast: true,
                    position: 'top-end',
                    timer: 1200,
                    showConfirmButton: false
                });
                $('#add-brand-form')[0].reset();
                fetchAndRender();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Add Failed',
                    text: resp.message || 'Failed to add brand'
                });
            }
        }).fail(function(){
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Server error while adding brand'
            });
        });
    });

    // initial load
    fetchAndRender();
});