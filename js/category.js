// Handle Category Form Submission for Add Category
document.getElementById('categoryForm').addEventListener('submit', function(event) {
    event.preventDefault();

    let categoryName = document.getElementById('category_name').value;

    if (categoryName.trim() === '') {
        alert('Category name cannot be empty!');
        return;
    }

    let xhr = new XMLHttpRequest();
    xhr.open('POST', 'add_category_action.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        alert(xhr.responseText); // Show success/failure message
    };
    xhr.send('category_name=' + encodeURIComponent(categoryName));
});

// Handle Category Update and Delete similarly...
