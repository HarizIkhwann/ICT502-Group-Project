<?php
require_once __DIR__ . '/../models/Qualification.php';
require_once __DIR__ . '/../Response.php';

class QualificationController {
    private $qualificationModel;
    
    public function __construct() {
        $this->qualificationModel = new Qualification();
    }
    
    // Get all qualifications
    public function index() {
        try {
            $qualifications = $this->qualificationModel->getAllQualifications();
            Response::success($qualifications, 'Qualifications retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get single qualification
    public function show($id) {
        try {
            if (empty($id)) {
                Response::error('Qualification ID is required', 400);
            }
            
            $qualification = $this->qualificationModel->getQualificationById($id);
            
            if (!$qualification) {
                Response::notFound('Qualification not found');
            }
            
            Response::success($qualification, 'Qualification retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get qualifications by employee
    public function getByEmployee($employee_id) {
        try {
            if (empty($employee_id)) {
                Response::error('Employee ID is required', 400);
            }
            
            $qualifications = $this->qualificationModel->getQualificationsByEmployee($employee_id);
            Response::success($qualifications, 'Employee qualifications retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Create new qualification
    public function create($data) {
        try {
            if (empty($data['name'])) {
                Response::error('Qualification name is required', 400);
            }
            
            $data['description'] = $data['description'] ?? null;
            
            $result = $this->qualificationModel->createQualification($data);
            
            if ($result) {
                Response::success(null, 'Qualification created successfully');
            } else {
                Response::error('Failed to create qualification');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Assign qualification to employee
    public function assign($data) {
        try {
            $required = ['employee_id', 'qualification_id', 'date_obtained', 'institution_name'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    Response::error("Field '{$field}' is required", 400);
                }
            }
            
            $result = $this->qualificationModel->assignToEmployee(
                $data['employee_id'],
                $data['qualification_id'],
                $data
            );
            
            if ($result) {
                Response::success(null, 'Qualification assigned to employee successfully');
            } else {
                Response::error('Failed to assign qualification');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Remove qualification from employee
    public function remove($employee_id, $qualification_id) {
        try {
            if (empty($employee_id) || empty($qualification_id)) {
                Response::error('Employee ID and Qualification ID are required', 400);
            }
            
            $result = $this->qualificationModel->removeFromEmployee($employee_id, $qualification_id);
            
            if ($result) {
                Response::success(null, 'Qualification removed from employee successfully');
            } else {
                Response::error('Failed to remove qualification or assignment not found');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Update qualification
    public function update($id, $data) {
        try {
            if (empty($id)) {
                Response::error('Qualification ID is required', 400);
            }
            
            $existing = $this->qualificationModel->getQualificationById($id);
            if (!$existing) {
                Response::notFound('Qualification not found');
            }
            
            if (empty($data['name'])) {
                Response::error('Qualification name is required', 400);
            }
            
            $result = $this->qualificationModel->updateQualification($id, $data);
            
            if ($result) {
                Response::success(null, 'Qualification updated successfully');
            } else {
                Response::error('Failed to update qualification');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Delete qualification
    public function destroy($id) {
        try {
            if (empty($id)) {
                Response::error('Qualification ID is required', 400);
            }
            
            $existing = $this->qualificationModel->getQualificationById($id);
            if (!$existing) {
                Response::notFound('Qualification not found');
            }
            
            $result = $this->qualificationModel->deleteQualification($id);
            
            if ($result) {
                Response::success(null, 'Qualification deleted successfully');
            } else {
                Response::error('Failed to delete qualification');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
}