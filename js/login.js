$(document).ready(function () {
    $('#login-form').submit(function (e) {
        e.preventDefault();

        const email = $('#email').val();
        const password = $('#password').val();

        // Basic validation
        if (email === '' || password === '') {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please enter both email and password!',
            });
            return;
        }

        const payload = {
            email: email,
            password: password
        };

        console.log('Login request payload:', payload);

        $.ajax({
            url: '../actions/login_customer_action.php',
            type: 'POST',
            data: payload,
            success: function (response) {
                console.log('Login response:', response);

                // Debug logs (if backend returns debug info)
                if (response.debug_db_connected !== undefined) {
                    console.log('DB connected (debug):', response.debug_db_connected);
                }
                if (response.debug_db_error) {
                    console.warn('DB error (debug):', response.debug_db_error);
                }

                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome!',
                        text: response.message || 'Login successful!',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        // Check if there's a redirect URL stored in session
                        $.get('../actions/get_redirect_url.php', function(data) {
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.href = '../index.php';
                            }
                        });
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login failed',
                        text: response.message || 'Invalid email or password!',
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                try {
                    const resp = jqXHR.responseJSON || JSON.parse(jqXHR.responseText || '{}');
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
