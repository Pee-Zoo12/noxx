<?php
class Employee extends User {
    private $employeeID;
    private $position;
    private $salary;
    private $company;
    private $employmentRecord;
    
    public function __construct($userData = null) {
        parent::__construct($userData);
        
        if ($userData) {
            $this->employeeID = $userData['employeeID'] ?? $this->userID;
            $this->position = $userData['position'] ?? '';
            $this->hireDate = $userData['hireDate'] ?? date('Y-m-d');
            $this->salary = $userData['salary'] ?? 0;
            $this->company = $userData['company'] ?? null;
            $this->employmentRecord = $userData['employmentRecord'] ?? [];
        } else {
            $this->employeeID = $this->userID;
            $this->hireDate = date('Y-m-d');
            $this->employmentRecord = [];
        }
        
        $this->role = 'employee';
    }
    
    public function register() {
        // Validate input data
        if (empty($this->username) || empty($this->email) || empty($this->password)) {
            return false;
        }
        
        // Check if email already exists
        $users = readJsonFile(USERS_FILE);
        
        foreach ($users as $userType => $userList) {
            foreach ($userList as $user) {
                if ($user['email'] === $this->email) {
                    return false;
                }
            }
        }
        
        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Prepare user data
        $userData = $this->toArray();
        
        // Add to employees array
        if (!isset($users['employees'])) {
            $users['employees'] = [];
        }
        $users['employees'][] = $userData;
        
        return writeJsonFile(USERS_FILE, $users);
    }
    
    public function toArray() {
        $data = parent::toArray();
        return array_merge($data, [
            'employeeID' => $this->employeeID,
            'position' => $this->position,
            'hireDate' => $this->hireDate,
            'salary' => $this->salary,
            'company' => $this->company,
            'employmentRecord' => $this->employmentRecord
        ]);
    }
    
    // Getters and Setters
    public function getEmployeeID() {
        return $this->employeeID;
    }
    
    public function getPosition() {
        return $this->position;
    }
    
    public function setPosition($position) {
        $this->position = $position;
    }
    
    public function getHireDate() {
        return $this->hireDate;
    }
    
    public function setHireDate($hireDate) {
        $this->hireDate = $hireDate;
    }
    
    public function getSalary() {
        return $this->salary;
    }
    
    public function setSalary($salary) {
        $this->salary = $salary;
    }
    
    public function getCompany() {
        return $this->company;
    }
    
    public function setCompany($company) {
        $this->company = $company;
    }
    
    public function getEmploymentRecord() {
        return $this->employmentRecord;
    }
    
    public function addEmploymentRecord($record) {
        $this->employmentRecord[] = $record;
    }
    
    // Static methods
    public static function getInstance($employeeID) {
        $users = readJsonFile(USERS_FILE);
        
        if (isset($users['employees'])) {
            foreach ($users['employees'] as $employee) {
                if ($employee['employeeID'] === $employeeID) {
                    return new Employee($employee);
                }
            }
        }
        
        return null;
    }
    
    public static function getAllEmployees() {
        $users = readJsonFile(USERS_FILE);
        $employees = [];
        
        if (isset($users['employees'])) {
            foreach ($users['employees'] as $employeeData) {
                $employees[] = new Employee($employeeData);
            }
        }
        
        return $employees;
    }
}
