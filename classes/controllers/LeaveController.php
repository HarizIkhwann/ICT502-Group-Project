<?php
require_once __DIR__ . '/../models/Leave.php';
require_once __DIR__ . '/../Response.php';

class LeaveController {
    private $leaveModel;
    
    public function __construct() {
        $this->leaveModel = new Leave();
    }
    
    // Get all leave records
    public function index() {
        try {
            $leaves = $this->leaveModel->getAllLeaves();
            Response::success($leaves, 'Leave records retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get single leave record
    public function show($id) {
        try {
            if (empty($id)) {
                Response::error('Leave ID is required', 400);
            }
            
            $leave = $this->leaveModel->getLeaveById($id);
            
            if (!$leave) {
                Response::notFound('Leave record not found');
            }
            
            Response::success($leave, 'Leave record retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get leaves by employee
    public function getByEmployee($employee_id) {
        try {
            if (empty($employee_id)) {
                Response::error('Employee ID is required', 400);
            }
            
            $leaves = $this->leaveModel->getLeavesByEmployee($employee_id);
            Response::success($leaves, 'Employee leave records retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get leaves by status
    public function getByStatus($status) {
        try {
            if (empty($status)) {
                Response::error('Status is required', 400);
            }
            
            $leaves = $this->leaveModel->getLeavesByStatus($status);
            Response::success($leaves, 'Leave records retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get all leave types
    public function getLeaveTypes() {
        try {
            $types = $this->leaveModel->getAllLeaveTypes();
            Response::success($types, 'Leave types retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Create leave request
    public function create($data) {
        try {
            // Debug: Log received data
            error_log("Leave Create - Received data: " . print_r($data, true));
            
            // Debug: Check what data is received
            if ($data === null || !is_array($data)) {
                Response::error('Invalid request data', 400);
            }
            
            $required = ['employee_id', 'leave_type_id', 'start_date', 'end_date', 'reason'];
            foreach ($required as $field) {
                if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                    error_log("Leave Create - Missing field: $field, exists: " . (array_key_exists($field, $data) ? 'yes' : 'no') . ", value: " . ($data[$field] ?? 'NULL'));
                    Response::error("Field '{$field}' is required", 400);
                }
            }
            
            // Default status is PENDING
            $data['status'] = $data['status'] ?? 'PENDING';
            
            $result = $this->leaveModel->createLeave($data);
            
            if ($result) {
                Response::success(null, 'Leave request created successfully');
            } else {
                Response::error('Failed to create leave request');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Approve/Reject leave
    public function updateStatus($id, $status, $approved_by) {
        try {
            if (empty($id)) {
                Response::error('Leave ID is required', 400);
            }
            
            if (empty($status)) {
                Response::error('Status is required', 400);
            }
            
            if (empty($approved_by)) {
                Response::error('Approver ID is required', 400);
            }
            
            // Validate status
            $validStatuses = ['APPROVED', 'REJECTED'];
            if (!in_array($status, $validStatuses)) {
                Response::error('Invalid status. Must be APPROVED or REJECTED', 400);
            }
            
            $existing = $this->leaveModel->getLeaveById($id);
            if (!$existing) {
                Response::notFound('Leave record not found');
            }
            
            $result = $this->leaveModel->updateLeaveStatus($id, $status, $approved_by);
            
            if ($result) {
                Response::success(null, "Leave request {$status} successfully");
            } else {
                Response::error('Failed to update leave status');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Update leave
    public function update($id, $data) {
        try {
            if (empty($id)) {
                Response::error('Leave ID is required', 400);
            }
            
            $existing = $this->leaveModel->getLeaveById($id);
            if (!$existing) {
                Response::notFound('Leave record not found');
            }
            
            $required = ['leave_type_id', 'start_date', 'end_date', 'reason'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    Response::error("Field '{$field}' is required", 400);
                }
            }
            
            $result = $this->leaveModel->updateLeave($id, $data);
            
            if ($result) {
                Response::success(null, 'Leave record updated successfully');
            } else {
                Response::error('Failed to update leave record');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Delete leave
    public function destroy($id) {
        try {
            if (empty($id)) {
                Response::error('Leave ID is required', 400);
            }
            
            $existing = $this->leaveModel->getLeaveById($id);
            if (!$existing) {
                Response::notFound('Leave record not found');
            }
            
            $result = $this->leaveModel->deleteLeave($id);
            
            if ($result) {
                Response::success(null, 'Leave record deleted successfully');
            } else {
                Response::error('Failed to delete leave record');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
}