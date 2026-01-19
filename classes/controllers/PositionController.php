<?php
require_once __DIR__ . '/../models/Position.php';
require_once __DIR__ . '/../Response.php';

class PositionController {
    private $positionModel;
    
    public function __construct() {
        $this->positionModel = new Position();
    }
    
    // Get all positions
    public function index() {
        try {
            $positions = $this->positionModel->getAllPositions();
            Response::success($positions, 'Positions retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get single position
    public function show($id) {
        try {
            if (empty($id)) {
                Response::error('Position ID is required', 400);
            }
            
            $position = $this->positionModel->getPositionById($id);
            
            if (!$position) {
                Response::notFound('Position not found');
            }
            
            Response::success($position, 'Position retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Create new position
    public function create($data) {
        try {
            // Validate required fields
            if (empty($data['position_title'])) {
                Response::error('Position title is required', 400);
            }
            
            // Set default values if not provided
            $data['job_description'] = $data['job_description'] ?? null;
            $data['min_salary'] = $data['min_salary'] ?? 0;
            $data['max_salary'] = $data['max_salary'] ?? 0;
            
            // Validate salary range
            if ($data['min_salary'] > $data['max_salary']) {
                Response::error('Minimum salary cannot be greater than maximum salary', 400);
            }
            
            $result = $this->positionModel->createPosition($data);
            
            if ($result) {
                Response::success(null, 'Position created successfully');
            } else {
                Response::error('Failed to create position');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Update position
    public function update($id, $data) {
        try {
            if (empty($id)) {
                Response::error('Position ID is required', 400);
            }
            
            // Check if position exists
            $existing = $this->positionModel->getPositionById($id);
            if (!$existing) {
                Response::notFound('Position not found');
            }
            
            // Validate required fields
            if (empty($data['position_title'])) {
                Response::error('Position title is required', 400);
            }
            
            // Validate salary range
            if ($data['min_salary'] > $data['max_salary']) {
                Response::error('Minimum salary cannot be greater than maximum salary', 400);
            }
            
            $result = $this->positionModel->updatePosition($id, $data);
            
            if ($result) {
                Response::success(null, 'Position updated successfully');
            } else {
                Response::error('Failed to update position');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Delete position
    public function destroy($id) {
        try {
            if (empty($id)) {
                Response::error('Position ID is required', 400);
            }
            
            // Check if position exists
            $existing = $this->positionModel->getPositionById($id);
            if (!$existing) {
                Response::notFound('Position not found');
            }
            
            $result = $this->positionModel->deletePosition($id);
            
            if ($result) {
                Response::success(null, 'Position deleted successfully');
            } else {
                Response::error('Failed to delete position');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
}