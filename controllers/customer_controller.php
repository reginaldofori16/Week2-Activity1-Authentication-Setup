<?php

require_once __DIR__ . '/../classes/customer_class.php';

/**
 * Register a new customer.
 */
function register_customer_ctr($name, $email, $password, $phone_number, $role = 1, $country = '', $city = '')
{
    $customer = new Customer();

    // Normalize inputs
    $email = trim($email);
    $name = trim($name);
    $phone_number = trim($phone_number);
    
    // Ensure that if no role is passed, it defaults to 2 (Customer)
    $role = intval($role) ?: 2;

    // Check for existing email
    $existing = $customer->getCustomerByEmail($email);
    if ($existing) {
        return ['success' => false, 'message' => 'Email is already registered'];
    }

    // Do NOT pre-hash here. createCustomer hashes the plain password.
    // Optional fields
    if ($country !== '') $customer->setCountry($country);
    if ($city !== '') $customer->setCity($city);

    // Create customer with the given data, including the role
    $customer_id = $customer->createCustomer($name, $email, $password, $phone_number, $role);
    
    // Return success or failure message
    if ($customer_id) {
        return ['success' => true, 'id' => $customer_id];
    }

    return ['success' => false, 'message' => 'Failed to register customer'];
}

/**
 * Get customer by email (wrapper)
 */
function get_customer_by_email_ctr($email)
{
    $customer = new Customer();
    return $customer->getCustomerByEmail($email);
}

/**
 * Login a customer
 */
function login_customer_ctr($email, $password)
{
    require_once __DIR__ . '/../classes/customer_class.php';
    $cust = new Customer();
    $row = $cust->getCustomerByEmail($email);
    if (!$row) {
        return ['success' => false, 'message' => 'No account with that email'];
    }

    // Use the actual column name returned by getCustomerByEmail
    $hash = $row['customer_pass'] ?? '';
    if (password_verify($password, $hash)) {
        return [
            'success' => true,
            'id' => $row['customer_id'],
            'name' => $row['customer_name'],
            'email' => $row['customer_email'],
            // role column is user_role in the table
            'role' => $row['user_role']
        ];
    }

    return ['success' => false, 'message' => 'Incorrect password'];
}
