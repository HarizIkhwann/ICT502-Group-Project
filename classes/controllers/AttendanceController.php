<?php
require_once __DIR__ . '/../models/Attendance.php';
require_once __DIR__ . '/../Response.php';

class AttendanceController {
    private $attendanceModel;
    
    public function __construct() {
        $this->attendanceModel = new Attendance();
    }
    
    // Get all attendance records
    public function index() {
        try {
            $records = $this->attendanceModel->getAllAttendance();
            Response::success($records, 'Attendance records retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get single attendance record
    public function show($id) {
        try {
            if (empty($id)) {
                Response::error('Attendance ID is required', 400);
            }
            
            $record = $this->attendanceModel->getAttendanceById($id);
            
            if (!$record) {
                Response::notFound('Attendance record not found');
            }
            
            Response::success($record, 'Attendance record retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get attendance by employee
    public function getByEmployee($employee_id) {
        try {
            if (empty($employee_id)) {
                Response::error('Employee ID is required', 400);
            }
            
            $records = $this->attendanceModel->getAttendanceByEmployee($employee_id);
            Response::success($records, 'Employee attendance records retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get attendance by date range
    public function getByDateRange($start_date, $end_date) {
        try {
            if (empty($start_date) || empty($end_date)) {
                Response::error('Start date and end date are required', 400);
            }
            
            $records = $this->attendanceModel->getAttendanceByDateRange($start_date, $end_date);
            Response::success($records, 'Attendance records retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Check in
    public function checkIn($data) {
        try {
            $required = ['employee_id', 'attendance_date', 'check_in_time'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    Response::error("Field '{$field}' is required", 400);
                }
            }
            
            $data['remarks'] = $data['remarks'] ?? null;
            
            $result = $this->attendanceModel->checkIn($data);
            
            if ($result) {
                Response::success(null, 'Check in successful');
            } else {
                Response::error('Failed to check in');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Check out
    public function checkOut($id, $check_out_time) {
        try {
            if (empty($id)) {
                Response::error('Attendance ID is required', 400);
            }
            
            if (empty($check_out_time)) {
                Response::error('Check out time is required', 400);
            }
            
            $existing = $this->attendanceModel->getAttendanceById($id);
            if (!$existing) {
                Response::notFound('Attendance record not found');
            }
            
            $result = $this->attendanceModel->checkOut($id, $check_out_time);
            
            if ($result) {
                Response::success(null, 'Check out successful');
            } else {
                Response::error('Failed to check out');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Update attendance
    public function update($id, $data) {
        try {
            if (empty($id)) {
                Response::error('Attendance ID is required', 400);
            }
            
            $existing = $this->attendanceModel->getAttendanceById($id);
            if (!$existing) {
                Response::notFound('Attendance record not found');
            }
            
            $required = ['attendance_date', 'check_in_time'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    Response::error("Field '{$field}' is required", 400);
                }
            }
            
            $data['check_out_time'] = $data['check_out_time'] ?? null;
            $data['remarks'] = $data['remarks'] ?? null;
            
            $result = $this->attendanceModel->updateAttendance($id, $data);
            
            if ($result) {
                Response::success(null, 'Attendance record updated successfully');
            } else {
                Response::error('Failed to update attendance record');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Delete attendance
    public function destroy($id) {
        try {
            if (empty($id)) {
                Response::error('Attendance ID is required', 400);
            }
            
            $existing = $this->attendanceModel->getAttendanceById($id);
            if (!$existing) {
                Response::notFound('Attendance record not found');
            }
            
            $result = $this->attendanceModel->deleteAttendance($id);
            
            if ($result) {
                Response::success(null, 'Attendance record deleted successfully');
            } else {
                Response::error('Failed to delete attendance record');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
}