<?php
require_once __DIR__ . '/../models/Employee.php';
require_once __DIR__ . '/../Response.php';

class EmployeeController {
    private $employeeModel;
    
    public function __construct() {
        $this->employeeModel = new Employee();
    }
    
    // Get all employees
    public function index() {
        try {
            $employees = $this->employeeModel->getAllEmployees();
            Response::success($employees, 'Employees retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get single employee
    public function show($id) {
        try {
            if (empty($id)) {
                Response::error('Employee ID is required', 400);
            }
            
            $employee = $this->employeeModel->getEmployeeById($id);
            
            if (!$employee) {
                Response::notFound('Employee not found');
            }
            
            Response::success($employee, 'Employee retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get employees by department
    public function getByDepartment($department_id) {
        try {
            if (empty($department_id)) {
                Response::error('Department ID is required', 400);
            }
            
            $employees = $this->employeeModel->getEmployeesByDepartment($department_id);
            Response::success($employees, 'Employees retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Create new employee
    public function create($data) {
        try {
            $required = ['first_name', 'last_name', 'email', 'phone_number', 'position_id', 'department_id', 'employment_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    Response::error("Field '{$field}' is required", 400);
                }
            }
            
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                Response::error('Invalid email format', 400);
            }
            
            // Validate employment_type
            if (!in_array($data['employment_type'], ['CONTRACT', 'PERMANENT'])) {
                Response::error('Employment type must be either CONTRACT or PERMANENT', 400);
            }
            
            $data['supervisor_id'] = $data['supervisor_id'] ?? null;
            
            $result = $this->employeeModel->createEmployee($data);
            
            if ($result) {
                Response::success(null, 'Employee created successfully');
            } else {
                Response::error('Failed to create employee');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Update employee
    public function update($id, $data) {
        try {
            if (empty($id)) {
                Response::error('Employee ID is required', 400);
            }
            
            $existing = $this->employeeModel->getEmployeeById($id);
            if (!$existing) {
                Response::notFound('Employee not found');
            }
            
            $required = ['first_name', 'last_name', 'email', 'phone_number', 'position_id', 'department_id', 'employment_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    Response::error("Field '{$field}' is required", 400);
                }
            }
            
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                Response::error('Invalid email format', 400);
            }
            
            // Validate employment_type
            if (!in_array($data['employment_type'], ['CONTRACT', 'PERMANENT'])) {
                Response::error('Employment type must be either CONTRACT or PERMANENT', 400);
            }
            
            $result = $this->employeeModel->updateEmployee($id, $data);
            
            if ($result) {
                Response::success(null, 'Employee updated successfully');
            } else {
                Response::error('Failed to update employee');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Delete employee
    public function destroy($id) {
        try {
            if (empty($id)) {
                Response::error('Employee ID is required', 400);
            }
            
            // Try to delete directly without checking first
            // (checking with JOIN might fail if references are broken)
            $result = $this->employeeModel->deleteEmployee($id);
            
            if ($result) {
                Response::success(null, 'Employee deleted successfully');
            } else {
                Response::notFound('Employee not found or already deleted');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
}