<?php
require_once __DIR__ . '/../models/PerformanceEvaluation.php';
require_once __DIR__ . '/../Response.php';

class PerformanceEvaluationController {
    private $evaluationModel;
    
    public function __construct() {
        $this->evaluationModel = new PerformanceEvaluation();
    }
    
    // Get all evaluations
    public function index() {
        try {
            $evaluations = $this->evaluationModel->getAllEvaluations();
            Response::success($evaluations, 'Evaluations retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get single evaluation
    public function show($id) {
        try {
            if (empty($id)) {
                Response::error('Evaluation ID is required', 400);
            }
            
            $evaluation = $this->evaluationModel->getEvaluationById($id);
            
            if (!$evaluation) {
                Response::notFound('Evaluation not found');
            }
            
            Response::success($evaluation, 'Evaluation retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get evaluations by employee
    public function getByEmployee($employee_id) {
        try {
            if (empty($employee_id)) {
                Response::error('Employee ID is required', 400);
            }
            
            $evaluations = $this->evaluationModel->getEvaluationsByEmployee($employee_id);
            Response::success($evaluations, 'Evaluations retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get evaluations by status
    public function getByStatus($status) {
        try {
            if (empty($status)) {
                Response::error('Status is required', 400);
            }
            
            $validStatuses = ['DRAFT', 'COMPLETED', 'REVIEWED', 'APPROVED'];
            if (!in_array($status, $validStatuses)) {
                Response::error('Invalid status', 400);
            }
            
            $evaluations = $this->evaluationModel->getEvaluationsByStatus($status);
            Response::success($evaluations, 'Evaluations retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Create evaluation
    public function create($data) {
        try {
            $required = ['employee_id', 'evaluator_id', 'evaluation_date', 'score'];
            foreach ($required as $field) {
                if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                    Response::error("Field '{$field}' is required", 400);
                }
            }
            
            // Validate score
            if ($data['score'] < 0 || $data['score'] > 100) {
                Response::error('Score must be between 0 and 100', 400);
            }
            
            // Validate status
            $validStatuses = ['DRAFT', 'COMPLETED', 'REVIEWED', 'APPROVED'];
            $data['status'] = $data['status'] ?? 'DRAFT';
            if (!in_array($data['status'], $validStatuses)) {
                Response::error('Invalid status', 400);
            }
            
            // Set defaults for optional fields
            $data['strengths'] = $data['strengths'] ?? '';
            $data['weaknesses'] = $data['weaknesses'] ?? '';
            $data['goals'] = $data['goals'] ?? '';
            
            $result = $this->evaluationModel->createEvaluation($data);
            
            if ($result) {
                Response::success(null, 'Evaluation created successfully');
            } else {
                Response::error('Failed to create evaluation');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Update evaluation status
    public function updateStatus($id, $status) {
        try {
            if (empty($id)) {
                Response::error('Evaluation ID is required', 400);
            }
            
            if (empty($status)) {
                Response::error('Status is required', 400);
            }
            
            $validStatuses = ['DRAFT', 'COMPLETED', 'REVIEWED', 'APPROVED'];
            if (!in_array($status, $validStatuses)) {
                Response::error('Invalid status', 400);
            }
            
            $existing = $this->evaluationModel->getEvaluationById($id);
            if (!$existing) {
                Response::notFound('Evaluation not found');
            }
            
            $result = $this->evaluationModel->updateEvaluationStatus($id, $status);
            
            if ($result) {
                Response::success(null, "Evaluation status updated to {$status} successfully");
            } else {
                Response::error('Failed to update evaluation status');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Update evaluation
    public function update($id, $data) {
        try {
            if (empty($id)) {
                Response::error('Evaluation ID is required', 400);
            }
            
            $existing = $this->evaluationModel->getEvaluationById($id);
            if (!$existing) {
                Response::notFound('Evaluation not found');
            }
            
            $required = ['evaluation_date', 'score', 'status'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    Response::error("Field '{$field}' is required", 400);
                }
            }
            
            // Validate score
            if ($data['score'] < 0 || $data['score'] > 100) {
                Response::error('Score must be between 0 and 100', 400);
            }
            
            // Validate status
            $validStatuses = ['DRAFT', 'COMPLETED', 'REVIEWED', 'APPROVED'];
            if (!in_array($data['status'], $validStatuses)) {
                Response::error('Invalid status', 400);
            }
            
            // Set defaults for optional fields
            $data['strengths'] = $data['strengths'] ?? '';
            $data['weaknesses'] = $data['weaknesses'] ?? '';
            $data['goals'] = $data['goals'] ?? '';
            
            $result = $this->evaluationModel->updateEvaluation($id, $data);
            
            if ($result) {
                Response::success(null, 'Evaluation updated successfully');
            } else {
                Response::error('Failed to update evaluation');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Delete evaluation
    public function delete($id) {
        try {
            if (empty($id)) {
                Response::error('Evaluation ID is required', 400);
            }
            
            $existing = $this->evaluationModel->getEvaluationById($id);
            if (!$existing) {
                Response::notFound('Evaluation not found');
            }
            
            $result = $this->evaluationModel->deleteEvaluation($id);
            
            if ($result) {
                Response::success(null, 'Evaluation deleted successfully');
            } else {
                Response::error('Failed to delete evaluation');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
}
