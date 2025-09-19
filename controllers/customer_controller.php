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
    $role = intval($role) ?: 1;

    // Check for existing email
    $existing = $customer->getCustomerByEmail($email);
    if ($existing) {
        return ['success' => false, 'message' => 'Email is already registered'];
    }

    // Hash password before saving
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Optional fields
    if ($country !== '') $customer->setCountry($country);
    if ($city !== '') $customer->setCity($city);

    $customer_id = $customer->createCustomer($name, $email, $hashed_password, $phone_number, $role);
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
    $customer = new Customer();
    $user = $customer->getCustomerByEmail(trim($email));

    if (!$user) {
        return ['success' => false, 'message' => 'No user found with this email'];
    }

    if (!password_verify($password, $user['customer_pass'])) {
        return ['success' => false, 'message' => 'Incorrect password'];
    }

    return [
        'success' => true,
        'id' => $user['customer_id'],
        'name' => $user['customer_name'],
        'email' => $user['customer_email'],
        'role' => $user['user_role']
    ];
}
