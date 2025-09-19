<?php

require_once '../settings/db_class.php';

class Customer extends db_connection
{
	private $customer_id;
	private $name;
	private $email;
	private $password;
    private $country;
    private $city;
	private $profile_image;
	private $role;
	private $date_created;
	private $phone_number;

	public function __construct($customer_id = null)
	{
		parent::db_connect();
		if ($customer_id) {
			$this->customer_id = $customer_id;
			$this->loadCustomer();
		}
	}

	private function loadCustomer($customer_id = null)
	{
		if ($customer_id) {
			$this->customer_id = $customer_id;
		}
		if (!$this->customer_id) {
			return false;
		}
		$stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_id = ?");
		$stmt->bind_param("i", $this->customer_id);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_assoc();
		if ($result) {
			$this->name = $result['customer_name'];
			$this->email = $result['customer_email'];
			$this->role = $result['user_role'];
			$this->date_created = isset($result['date_created']) ? $result['date_created'] : null;
			$this->phone_number = $result['customer_contact'];
		}
	}

	public function createCustomer($name, $email, $password, $phone_number, $role)
	{
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		// ensure country and city columns are provided to satisfy strict SQL mode defaults
		// caller should pass $country and $city if available; default to empty strings when not provided
		// For backward-compatibility we'll allow calling code to pass only 5 args and handle the rest externally.
		// NOTE: This method signature is kept simple; controller/action will supply country and city values.
		// If you prefer, update the signature to accept $country and $city.
		// Here we'll attempt to use properties if set; otherwise default to empty.
		$country = isset($this->country) ? $this->country : '';
		$city = isset($this->city) ? $this->city : '';

		$stmt = $this->db->prepare("INSERT INTO customer (customer_name, customer_email, customer_pass, customer_contact, user_role, customer_country, customer_city) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("ssssiss", $name, $email, $hashed_password, $phone_number, $role, $country, $city);
		if ($stmt->execute()) {
			return $this->db->insert_id;
		}
		return false;
	}

	public function getCustomerByEmail($email) {
    $sql = "SELECT 
                customer_id,
                customer_name,
                customer_email,
                customer_pass,
                customer_country,
                customer_city,
                customer_contact,
                customer_image,
                user_role
            FROM customer
            WHERE customer_email = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

}

