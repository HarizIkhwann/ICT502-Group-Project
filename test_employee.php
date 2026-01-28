<?php
require_once 'config/database.php';
require_once 'classes/models/Employee.php';

$db = new Database();
$conn = $db->getConnection();
$emp = new Employee($conn);
$result = $emp->getAllEmployees();

echo "Total employees: " . count($result) . "\n";
if (count($result) > 0) {
    echo "First employee ID: " . $result[0]['EMPLOYEE_ID'] . "\n";
    echo "First employee Name: " . $result[0]['FIRST_NAME'] . " " . $result[0]['LAST_NAME'] . "\n";
}
