<?php
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../Response.php';

class DepartmentController {
    private $departmentModel;

    public function __construct() {
        $this->departmentModel = new Department();
    }

    // Get all departments
    public function index() {
        try {
            $departments = $this->departmentModel->getAllDepartments();
            Response::success($departments, "Departments retrieved successfully.");
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }

    // Get a single department by ID
    public function show($id) {
        try {
            if (empty($id)) {
                Response::error("Department ID is required.", 400);
            }

            $department = $this->departmentModel->getDepartmentById($id);
            if ($department) {
                Response::success($department, "Department retrieved successfully.");
            } else {
                Response::notFound("Department not found.");
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }

    // Create a new department
    public function create($data) {
        try {
            // DEBUG: Log received data
            error_log("Data received in controller: " . print_r($data, true));
            error_log("department_name: " . ($data['department_name'] ?? 'NOT SET'));
            error_log("location: " . ($data['location'] ?? 'NOT SET'));
            
            // Validate required fields
            if (empty($data['department_name']) || empty($data['location'])) {
                Response::error('Department name and location are required. Received: ' . json_encode($data), 400);
            }

            // Set default values if not provided
            $data['budget'] = $data['budget'] ?? 0;
            $data['manager_id'] = $data['manager_id'] ?? null;

            $result = $this->departmentModel->createDepartment($data);

            if ($result) {
                Response::success(null, 'Department created successfully');
            } else {
                Response::error('Failed to create department');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }

    // Update department
    public function update($id, $data) {
        try {
            if (empty($id)) {
                Response::error('Department ID is required', 400);
            }
            
            // Check if department exists
            $existing = $this->departmentModel->getDepartmentById($id);
            if (!$existing) {
                Response::notFound('Department not found');
            }
            
            // Validate required fields
            if (empty($data['department_name']) || empty($data['location'])) {
                Response::error('Department name and location are required', 400);
            }
            
            $result = $this->departmentModel->updateDepartment($id, $data);
            
            if ($result) {
                Response::success(null, 'Department updated successfully');
            } else {
                Response::error('Failed to update department');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }

    // Delete department
    public function destroy($id) {
        try {
            if (empty($id)) {
                Response::error('Department ID is required', 400);
            }
            
            // Check if department exists
            $existing = $this->departmentModel->getDepartmentById($id);
            if (!$existing) {
                Response::notFound('Department not found');
            }
            
            $result = $this->departmentModel->deleteDepartment($id);
            
            if ($result) {
                Response::success(null, 'Department deleted successfully');
            } else {
                Response::error('Failed to delete department');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
}