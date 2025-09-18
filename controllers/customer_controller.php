<?php

require_once __DIR__ . '/../classes/customer_class.php';

/**
 * Register a new customer.
 *
 * Returns an array:
 *  - ['success' => true, 'id' => <customer_id>]
 *  - ['success' => false, 'message' => '<error message>']
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

    // Create customer
    // set optional fields on the object so createCustomer will pick them up
    if ($country !== '') {
        $customer->setCountry($country);
    }
    if ($city !== '') {
        $customer->setCity($city);
    }
    $customer_id = $customer->createCustomer($name, $email, $password, $phone_number, $role);
    if ($customer_id) {
        return ['success' => true, 'id' => $customer_id];
    }

    return ['success' => false, 'message' => 'Failed to register customer'];
}

function get_customer_by_email_ctr($email)
{
	$customer = new Customer();
	return $customer->getCustomerByEmail($email);
}

