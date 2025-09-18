$(document).ready(function() {
    $('#register-form').submit(function(e) {
        e.preventDefault();

        name = $('#name').val();
        email = $('#email').val();
        password = $('#password').val();
        phone_number = $('#phone_number').val();
        role = $('input[name="role"]:checked').val();

        if (name == '' || email == '' || password == '' || phone_number == '') {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please fill in all important fields!', // some fields like country, city and image are optional
            });

            return;
        } else if (password.length < 6 || !password.match(/[a-z]/) || !password.match(/[A-Z]/) || !password.match(/[0-9]/)) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Password must be at least 6 characters long and contain at least one lowercase letter, one uppercase letter, and one number!',
            });

            return;
        }

        var payload = {
            name: name,
            email: email,
            password: password,
            phone_number: phone_number,
            role: role
        };

        console.log('Register request payload:', payload);

        $.ajax({
            url: '../actions/register_customer_action.php',
            type: 'POST',
            data: payload,
            success: function(response) {
                console.log('Register response:', response);
                if (response.debug_db_connected !== undefined) {
                    console.log('DB connected (debug):', response.debug_db_connected);
                }
                if (response.debug_db_error) {
                    console.warn('DB error (debug):', response.debug_db_error);
                }

                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message,
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                // Try to log response JSON if present
                try {
                    var resp = jqXHR.responseJSON || JSON.parse(jqXHR.responseText || '{}');
                    console.log('Error response:', resp);
                    if (resp.debug_db_connected !== undefined) console.log('DB connected (debug):', resp.debug_db_connected);
                    if (resp.debug_db_error) console.warn('DB error (debug):', resp.debug_db_error);
                } catch (e) {
                    console.warn('Could not parse error response JSON');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An error occurred! Please try again later.',
                });
            }
        });
    });
});