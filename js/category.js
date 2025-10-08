// jQuery + SweetAlert2 handlers that refresh the category list only
$(function(){
    // helper to render a table body from category array
    function renderCategoryRows(categories) {
        var rows = '';
        categories.forEach(function(category){
            var id = category.cat_id || category.id || '';
            var name = $('<div>').text(category.cat_name || category.name || '').html();
            rows += '<tr>' +
                '<td style="border:1px solid #eee;padding:.5rem;vertical-align:top;">'+id+'</td>' +
                '<td style="border:1px solid #eee;padding:.5rem;vertical-align:top;">'+name+'</td>' +
                '<td style="border:1px solid #eee;padding:.5rem;vertical-align:top;">' +
                    '<form class="update-form" style="display:inline-block;margin-right:.5rem;">' +
                        '<input type="hidden" name="category_id" value="'+id+'" />' +
                        '<input type="text" name="category_name" value="'+name+'" style="padding:.25rem;" />' +
                        '<button type="submit" style="padding:.25rem .4rem;margin-left:.3rem;">Save</button>' +
                    '</form>' +
                    '<form class="delete-form" style="display:inline-block;margin-left:.5rem;">' +
                        '<input type="hidden" name="id" value="'+id+'" />' +
                        '<button type="submit" style="padding:.25rem .4rem;background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;">Delete</button>' +
                    '</form>' +
                '</td>' +
            '</tr>';
        });
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
            $.ajax({
                url: CATEGORY_ENDPOINTS.update,
                method: 'POST',
                data: data,
                dataType: 'json'
            }).done(function(resp){
                if (resp.status === 'success'){
                    Swal.fire({icon:'success',title:'Updated',toast:true,position:'top-end',timer:1200,showConfirmButton:false});
                    fetchAndRender();
                } else {
                    Swal.fire({icon:'error',title:resp.message || 'Update failed'});
                }
            }).fail(function(){
                Swal.fire({icon:'error',title:'Server error while updating category'});
            });
        });

        // Delete
        $('form.delete-form').off('submit').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            var data = $form.serialize();
            Swal.fire({
                title: 'Delete this category?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
            }).then(function(result){
                if (result.isConfirmed){
                    $.ajax({
                        url: CATEGORY_ENDPOINTS.delete,
                        method: 'POST',
                        data: data,
                        dataType: 'json'
                    }).done(function(resp){
                        if (resp.status === 'success'){
                            Swal.fire({icon:'success',title:'Deleted',toast:true,position:'top-end',timer:1200,showConfirmButton:false});
                            fetchAndRender();
                        } else {
                            Swal.fire({icon:'error',title:resp.message || 'Delete failed'});
                        }
                    }).fail(function(){
                        Swal.fire({icon:'error',title:'Server error while deleting category'});
                    });
                }
            });
        });
    }

    function fetchAndRender(){
        $.ajax({
            url: CATEGORY_ENDPOINTS.fetch,
            method: 'GET',
            dataType: 'json'
        }).done(function(categories){
            if (Array.isArray(categories)){
                renderCategoryRows(categories);
            } else {
                Swal.fire({icon:'error',title:'Failed to fetch categories'});
            }
        }).fail(function(){
            Swal.fire({icon:'error',title:'Server error while fetching categories'});
        });
    }

    // Add handler for add form
    $('#add-category-form').off('submit').on('submit', function(e){
        e.preventDefault();
        var data = $(this).serialize();
        $.ajax({
            url: CATEGORY_ENDPOINTS.add,
            method: 'POST',
            data: data,
            dataType: 'json'
        }).done(function(resp){
            if (resp.status === 'success'){
                Swal.fire({icon:'success',title:'Added',toast:true,position:'top-end',timer:1200,showConfirmButton:false});
                $('#add-category-form')[0].reset();
                fetchAndRender();
            } else {
                Swal.fire({icon:'error',title:resp.message || 'Add failed'});
            }
        }).fail(function(){
            Swal.fire({icon:'error',title:'Server error while adding category'});
        });
    });

    // initial load
    fetchAndRender();
});
