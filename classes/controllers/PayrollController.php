<?php
require_once __DIR__ . '/../models/Payroll.php';
require_once __DIR__ . '/../Response.php';

class PayrollController {
    private $payrollModel;
    
    public function __construct() {
        $this->payrollModel = new Payroll();
    }
    
    // Get all payroll records
    public function index() {
        try {
            $payrolls = $this->payrollModel->getAllPayrolls();
            Response::success($payrolls, 'Payroll records retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get single payroll record
    public function show($id) {
        try {
            if (empty($id)) {
                Response::error('Payroll ID is required', 400);
            }
            
            $payroll = $this->payrollModel->getPayrollById($id);
            
            if (!$payroll) {
                Response::notFound('Payroll record not found');
            }
            
            Response::success($payroll, 'Payroll record retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get payrolls by employee
    public function getByEmployee($employee_id) {
        try {
            if (empty($employee_id)) {
                Response::error('Employee ID is required', 400);
            }
            
            $payrolls = $this->payrollModel->getPayrollsByEmployee($employee_id);
            Response::success($payrolls, 'Payroll records retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Get payrolls by date range
    public function getByDateRange($start_date, $end_date) {
        try {
            if (empty($start_date) || empty($end_date)) {
                Response::error('Start date and end date are required', 400);
            }
            
            $payrolls = $this->payrollModel->getPayrollsByDateRange($start_date, $end_date);
            Response::success($payrolls, 'Payroll records retrieved successfully');
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Create payroll record
    public function create($data) {
        try {
            $required = ['employee_id', 'pay_period_start', 'pay_period_end', 'payment_date', 
                        'gross_pay', 'net_pay'];
            foreach ($required as $field) {
                if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                    Response::error("Field '{$field}' is required", 400);
                }
            }
            
            // Validate payment method
            $validMethods = ['BANK_TRANSFER', 'CHEQUE', 'CASH', 'DIRECT_DEPOSIT'];
            if (isset($data['payment_method']) && !in_array($data['payment_method'], $validMethods)) {
                Response::error('Invalid payment method', 400);
            }
            
            // Set defaults
            $data['allowances'] = $data['allowances'] ?? 0;
            $data['overtime_pay'] = $data['overtime_pay'] ?? 0;
            $data['deductions'] = $data['deductions'] ?? 0;
            $data['tax_amount'] = $data['tax_amount'] ?? 0;
            $data['payment_method'] = $data['payment_method'] ?? 'BANK_TRANSFER';
            
            // Validate net_pay calculation
            $calculated_net = $data['gross_pay'] + $data['allowances'] + $data['overtime_pay'] 
                            - $data['deductions'] - $data['tax_amount'];
            
            if (abs($calculated_net - $data['net_pay']) > 0.01) {
                Response::error('Net pay calculation mismatch. Expected: ' . number_format($calculated_net, 2), 400);
            }
            
            $result = $this->payrollModel->createPayroll($data);
            
            if ($result) {
                Response::success(null, 'Payroll record created successfully');
            } else {
                Response::error('Failed to create payroll record');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Update payroll record
    public function update($id, $data) {
        try {
            if (empty($id)) {
                Response::error('Payroll ID is required', 400);
            }
            
            $existing = $this->payrollModel->getPayrollById($id);
            if (!$existing) {
                Response::notFound('Payroll record not found');
            }
            
            $required = ['pay_period_start', 'pay_period_end', 'payment_date', 
                        'gross_pay', 'net_pay'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    Response::error("Field '{$field}' is required", 400);
                }
            }
            
            // Validate payment method
            $validMethods = ['BANK_TRANSFER', 'CHEQUE', 'CASH', 'DIRECT_DEPOSIT'];
            if (isset($data['payment_method']) && !in_array($data['payment_method'], $validMethods)) {
                Response::error('Invalid payment method', 400);
            }
            
            // Set defaults
            $data['allowances'] = $data['allowances'] ?? 0;
            $data['overtime_pay'] = $data['overtime_pay'] ?? 0;
            $data['deductions'] = $data['deductions'] ?? 0;
            $data['tax_amount'] = $data['tax_amount'] ?? 0;
            $data['payment_method'] = $data['payment_method'] ?? 'BANK_TRANSFER';
            
            // Validate net_pay calculation
            $calculated_net = $data['gross_pay'] + $data['allowances'] + $data['overtime_pay'] 
                            - $data['deductions'] - $data['tax_amount'];
            
            if (abs($calculated_net - $data['net_pay']) > 0.01) {
                Response::error('Net pay calculation mismatch. Expected: ' . number_format($calculated_net, 2), 400);
            }
            
            $result = $this->payrollModel->updatePayroll($id, $data);
            
            if ($result) {
                Response::success(null, 'Payroll record updated successfully');
            } else {
                Response::error('Failed to update payroll record');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    // Delete payroll record
    public function delete($id) {
        try {
            if (empty($id)) {
                Response::error('Payroll ID is required', 400);
            }
            
            $existing = $this->payrollModel->getPayrollById($id);
            if (!$existing) {
                Response::notFound('Payroll record not found');
            }
            
            $result = $this->payrollModel->deletePayroll($id);
            
            if ($result) {
                Response::success(null, 'Payroll record deleted successfully');
            } else {
                Response::error('Failed to delete payroll record');
            }
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
}
