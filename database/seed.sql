SET FOREIGN_KEY_CHECKS = 0;
USE raquel_hris;


-- ============================================
-- USERS
-- ============================================
TRUNCATE TABLE users;

INSERT INTO users 
(user_id, username, email, password_hash, full_name, role, branch_id)
VALUES
(1, 'admin', 'admin@raquel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Main branch Raquel Admin', 'Admin', 1),
(2, 'Maridel Merle', 'manager@raquel.com', '$2y$10$s0lw8zV2epVYQgYqJN6xaeA0Do4NKAaRCm.KDgU4M146JemUlAu2q', 'Maridel Merle', 'HR Manager', 1),
(3, 'Fred Andrew', 'supervisor@raquel.com', '$2y$10$nbSJVgKm4IiPVJpqvTRi.ORrKmouVZcuEpUi0RWyPKd.AlA75N3Lq', 'Fred Andrew Franca', 'HR Supervisor', 1),
(4, 'James Mendoza', 'staff@raquel.com', '$2y$10$LyJ5uD7EY7V9RSbHo70nQucFtuHh3Pn/RET7JYHuVKGGEZoStHaia', 'Clark James Mendoza', 'HR Staff', 1);


-- ============================================
-- BRANCHES
-- ============================================
TRUNCATE TABLE branches;

INSERT INTO branches 
(branch_id, branch_name, location)
VALUES
(1, 'Main Office', 'Tayabas City, Quezon Province'),
(2, 'Talipan', 'Pagbilao, Quezon');

-- ============================================
-- DEPARTMENTS
-- ============================================
TRUNCATE TABLE departments;

INSERT INTO departments 
(department_id, department_name, description)
VALUES
(3, 'Accounting & Accounts Payable', 'Handles company expenses and payments'),
(4, 'Finance & Treasury', 'Manages budgeting and funds'),
(5, 'General Services Division', 'Handles facilities and maintenance'),
(6, 'IT Department', 'Maintains systems and networks'),
(7, 'Operations Department', 'Manages daily operations'),
(8, 'Internal Audit', 'Ensures accuracy and compliance'),
(9, 'Business Development', 'Handles growth and partnerships'),
(10, 'Compliance & Risk', 'Ensures legal compliance'),
(11, 'Human Resources', 'Manages employees and hiring'),
(12, 'Executive Office (EA)', 'Supports executives'),
(13, 'Marketing & Sales', 'Handles promotion and sales'),
(14, 'Procurement / Purchasing', 'Handles company purchases');

-- ============================================
-- EMPLOYEE
-- ============================================
TRUNCATE TABLE employees;

INSERT INTO employees
(employee_id, first_name, last_name, middle_name, date_of_birth,
 gender, civil_status, hire_date, job_title, department, branch_id,
 employment_status, employment_type)
VALUES
(1, 'Juan', 'Dela Cruz', 'M', '1990-01-15',
 'Male', 'Single', '2026-03-01', 'Staff', 'IT Department', 2,
 'Regular', 'Full-time');

-- ============================================
-- ADDRESSES
-- ============================================
TRUNCATE TABLE employee_addresses;

INSERT INTO employee_addresses
(employee_id, address_type, house_no, street, barangay, city, province)
VALUES
(1, 'Residential', '123', 'Mabini St', 'Brgy. Uno', 'Manila', 'Metro Manila'),
(1, 'Permanent', '456', 'Rizal St', 'Brgy. Dos', 'Quezon City', 'Metro Manila');

-- ============================================
-- CONTACTS
-- ============================================
TRUNCATE TABLE employee_contacts;

INSERT INTO employee_contacts
(employee_id, telephone_number, mobile_number, personal_email)
VALUES
(1, '02-1234567', '09123456789', 'juan@example.com');

-- ============================================
-- FAMILY
-- ============================================
TRUNCATE TABLE employee_children;
TRUNCATE TABLE employee_siblings;

INSERT INTO employee_children
(employee_id, surname, first_name, date_of_birth)
VALUES
(1, 'Dela Cruz', 'Juana', '2020-01-01');

INSERT INTO employee_siblings
(employee_id, surname, first_name, date_of_birth)
VALUES
(1, 'Dela Cruz', 'Jose', '1995-01-01');

-- ============================================
-- EMERGENCY CONTACT
-- ============================================
TRUNCATE TABLE employee_emergency_contacts;

INSERT INTO employee_emergency_contacts
(employee_id, contact_name, relationship, contact_number)
VALUES
(1, 'Maria Dela Cruz', 'Mother', '09123456789');

-- ============================================
-- GOVERNMENT IDS
-- ============================================
TRUNCATE TABLE employee_government_ids;

INSERT INTO employee_government_ids
(employee_id, sss_number, philhealth_number, pagibig_number, tin_number)
VALUES
(1, '12-3456789-0', '12-345678901-2', '1234-5678-9012', '123-456-789-000');

-- ============================================
-- EDUCATION
-- ============================================
TRUNCATE TABLE employee_education;

INSERT INTO employee_education
(employee_id, education_level, degree_course, year_graduated, honors_received)
VALUES
(1, 'College', 'BS Computer Science', '2012', 'Cum Laude');

-- ============================================
-- WORK EXPERIENCE
-- ============================================
TRUNCATE TABLE employee_work_experience;

INSERT INTO employee_work_experience
(employee_id, job_title, company_name, monthly_salary)
VALUES
(1, 'Senior Developer', 'Tech Solutions Inc.', 50000);

-- ============================================
-- TRAINING
-- ============================================
TRUNCATE TABLE employee_trainings;

INSERT INTO employee_trainings
(employee_id, training_title, no_of_hours, conducted_by)
VALUES
(1, 'Advanced PHP Programming', 40, 'PHP Academy');

-- ============================================
-- MEMBERSHIP
-- ============================================
TRUNCATE TABLE employee_memberships;

INSERT INTO employee_memberships
(employee_id, organization_name)
VALUES
(1, 'Philippine Computer Society');

SET FOREIGN_KEY_CHECKS = 1;