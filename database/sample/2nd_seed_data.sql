SET FOREIGN_KEY_CHECKS = 0;
USE raquel_hris;

-- ============================================
-- USERS
-- ============================================
DELETE FROM `users`; ALTER TABLE `users` AUTO_INCREMENT = 1;

INSERT INTO `users` VALUES 
('1', 'admin', 'admin@raquel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Main branch Raquel Admin', 'Admin', '1', NULL, '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('2', 'Maridel Merle', 'manager@raquel.com', '$2y$10$s0lw8zV2epVYQgYqJN6xaeA0Do4NKAaRCm.KDgU4M146JemUlAu2q', 'Maridel Merle', 'HR Manager', '1', 'assets/img/avatars/uploads/user_2_1775294738.png', '1', '2026-04-04 17:10:36', '2026-04-04 17:25:38', NULL),
('3', 'Fred Andrew', 'supervisor@raquel.com', '$2y$10$nbSJVgKm4IiPVJpqvTRi.ORrKmouVZcuEpUi0RWyPKd.AlA75N3Lq', 'Fred Andrew Franca', 'HR Supervisor', '1', 'assets/img/avatars/uploads/user_3_1775294785.jpg', '1', '2026-04-04 17:10:36', '2026-04-04 17:26:25', NULL),
('4', 'James Mendoza', 'staff@raquel.com', '$2y$10$LyJ5uD7EY7V9RSbHo70nQucFtuHh3Pn/RET7JYHuVKGGEZoStHaia', 'Clark James Mendoza', 'HR Staff', '1', 'assets/img/avatars/uploads/user_4_1775294800.jpg', '1', '2026-04-04 17:10:36', '2026-04-04 17:26:40', NULL);

-- ============================================
-- BRANCHES
-- ============================================
DELETE FROM `branches`; ALTER TABLE `branches` AUTO_INCREMENT = 1;

INSERT INTO `branches` VALUES 
('1', 'Main Office', 'Tayabas City, Quezon Province', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('5', 'Cota', 'Lucena', '1', '2026-04-04 17:54:58', '2026-04-04 18:24:05', NULL),
('6', 'Talipan', 'Pagbilao', '1', '2026-04-04 17:54:58', '2026-04-04 18:23:50', NULL);

-- ============================================
-- DEPARTMENTS
-- ============================================
DELETE FROM `departments`; ALTER TABLE `departments` AUTO_INCREMENT = 1;

INSERT INTO `departments` VALUES 
('1', 'Accounting & Accounts Payable', 'Handles company expenses and payments', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('2', 'Finance & Treasury', 'Manages budgeting and funds', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('3', 'General Services Division', 'Handles facilities and maintenance', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('4', 'IT Department', 'Maintains systems and networks', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('5', 'Operations Department', 'Manages daily operations', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('6', 'Internal Audit', 'Ensures accuracy and compliance', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('7', 'Business Development', 'Handles growth and partnerships', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('8', 'Compliance & Risk', 'Ensures legal compliance', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('9', 'Human Resources', 'Manages employees and hiring', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('10', 'Executive Office (EA)', 'Supports executives', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('11', 'Marketing & Sales', 'Handles promotion and sales', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('12', 'Procurement / Purchasing', 'Handles company purchases', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('18', 'HR Department', 'Mayuwi', '1', '2026-04-04 17:54:58', '2026-04-04 18:25:33', NULL),
('19', 'Finance Department', 'Imported via CSV', '1', '2026-04-04 17:54:58', '2026-04-04 17:54:58', NULL);

-- ============================================
-- EMPLOYEES
-- ============================================
DELETE FROM `employees`; ALTER TABLE `employees` AUTO_INCREMENT = 1;

INSERT INTO `employees` VALUES 
('2', 'Daniel', 'Paraiso', 'Maldeva', '', '2000-01-01', 'Lucena City', 'Male', 'Single', '2026-04-04', 'Programmer', '4', '1', 'Regular', 'Full-time', 'emp_69d0dc0acf3f9.jpg', '1', '2026-04-04 17:38:18', '2026-04-04 17:38:18', NULL),
('5', 'Jane', 'Doe', 'Smith', '', '1992-05-15', 'Cebu', 'Female', 'Married', '2024-02-15', 'HR Specialist', '18', '5', 'Regular', 'Full-time', 'emp_69d0e68fe5f85.png', '1', '2026-04-04 17:54:58', '2026-04-04 18:23:11', NULL),
('6', 'Jan Paul', 'Merilles', 'Luna', '', '1995-03-20', 'Lucena City', 'Male', 'Single', '2024-03-01', 'Data Analyst', '4', '1', 'Regular', 'Full-time', 'emp_69d0e64cb617d.jpg', '1', '2026-04-04 17:54:58', '2026-04-04 18:22:04', NULL),
('7', 'Fred Andrew', 'Franca', 'Santos', '', '1993-07-10', 'Batangas City', 'Male', 'Single', '2024-03-15', 'Systems Administrator', '4', '6', 'Regular', 'Full-time', 'emp_69d0e6442b085.jpg', '1', '2026-04-04 17:54:58', '2026-04-04 18:21:56', NULL),
('8', 'Mark Wilson', 'De Torres', 'Gomez', '', '1994-11-25', 'Cavite City', 'Male', 'Single', '2024-04-01', 'Accountant', '19', '1', 'Regular', 'Full-time', 'emp_69d0e60e762f7.png', '1', '2026-04-04 17:54:58', '2026-04-04 18:21:02', NULL);

-- ============================================
-- EMPLOYEE_ADDRESSES
-- ============================================
DELETE FROM `employee_addresses`; ALTER TABLE `employee_addresses` AUTO_INCREMENT = 1;

INSERT INTO `employee_addresses` VALUES 
('3', '2', 'Residential', '123 Block 5 Lot 10', 'Mabini Street', 'Sunshine Village', 'San Isidro', 'Tayabas City', 'Quezon', '4327', '2026-04-04 17:38:18', '2026-04-04 17:38:18'),
('4', '2', 'Permanent', '123 Block 5 Lot 10', 'Mabini Street', 'Sunshine Village', 'San Isidro', 'Tayabas City', 'Quezon', '4327', '2026-04-04 17:38:18', '2026-04-04 17:38:18'),
('47', '8', 'Residential', '202', 'Oak St', 'Greenview', 'Imus', 'Cavite City', 'Cavite', '4100', '2026-04-04 18:21:02', '2026-04-04 18:21:02'),
('48', '8', 'Permanent', '202', 'Oak St', 'Greenview', 'Imus', 'Cavite City', 'Cavite', '4100', '2026-04-04 18:21:02', '2026-04-04 18:21:02'),
('49', '7', 'Residential', '101', 'Pine St', 'Woodland', 'Sta Rita', 'Batangas City', 'Batangas', '4200', '2026-04-04 18:21:56', '2026-04-04 18:21:56'),
('50', '7', 'Permanent', '101', 'Pine St', 'Woodland', 'Sta Rita', 'Batangas City', 'Batangas', '4200', '2026-04-04 18:21:56', '2026-04-04 18:21:56'),
('51', '6', 'Residential', '789', 'High St', 'Hillside', 'San Roque', 'Lucena City', 'Quezon', '4301', '2026-04-04 18:22:04', '2026-04-04 18:22:04'),
('52', '6', 'Permanent', '789', 'High St', 'Hillside', 'San Roque', 'Lucena City', 'Quezon', '4301', '2026-04-04 18:22:04', '2026-04-04 18:22:04'),
('53', '5', 'Residential', '456', 'Side St', 'Central Park', 'Poblacion', 'Cebu City', 'Cebu', '6000', '2026-04-04 18:23:11', '2026-04-04 18:23:11'),
('54', '5', 'Permanent', '456', 'Side St', 'Central Park', 'Poblacion', 'Cebu City', 'Cebu', '6000', '2026-04-04 18:23:11', '2026-04-04 18:23:11');

-- ============================================
-- EMPLOYEE_CHILDREN
-- ============================================
DELETE FROM `employee_children`; ALTER TABLE `employee_children` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_CONTACTS
-- ============================================
DELETE FROM `employee_contacts`; ALTER TABLE `employee_contacts` AUTO_INCREMENT = 1;

INSERT INTO `employee_contacts` VALUES 
('2', '2', '(042) 123-4567', '0912-345-6789', 'Daniel@gmail.com', '2026-04-04 17:38:18', '2026-04-04 17:38:18'),
('11', '5', '032-123-4567', '0920-111-2222', 'jane@example.com', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('12', '6', '042-710-1234', '0918-111-1111', 'janpaul@example.com', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('13', '7', '043-123-4567', '0919-222-2222', 'fred@example.com', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('14', '8', '046-123-4567', '0921-333-3333', 'mark@example.com', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('15', '6', '042-710-1234', '0918-111-1111', 'janpaul@example.com', '2026-04-04 18:13:13', '2026-04-04 18:13:13'),
('16', '6', '042-710-1234', '0918-111-1111', 'janpaul@example.com', '2026-04-04 18:13:22', '2026-04-04 18:13:22'),
('17', '6', '042-710-1234', '0918-111-1111', 'janpaul@example.com', '2026-04-04 18:13:31', '2026-04-04 18:13:31'),
('18', '6', '042-710-1234', '0918-111-1111', 'janpaul@example.com', '2026-04-04 18:13:54', '2026-04-04 18:13:54'),
('19', '6', '042-710-1234', '0918-111-1111', 'janpaul@example.com', '2026-04-04 18:16:33', '2026-04-04 18:16:33'),
('20', '6', '042-710-1234', '0918-111-1111', 'janpaul@example.com', '2026-04-04 18:16:57', '2026-04-04 18:16:57'),
('21', '6', '042-710-1234', '0918-111-1111', 'janpaul@example.com', '2026-04-04 18:19:30', '2026-04-04 18:19:30'),
('22', '7', '043-123-4567', '0919-222-2222', 'fred@example.com', '2026-04-04 18:19:46', '2026-04-04 18:19:46'),
('23', '7', '043-123-4567', '0919-222-2222', 'fred@example.com', '2026-04-04 18:19:55', '2026-04-04 18:19:55'),
('24', '8', '046-123-4567', '0921-333-3333', 'mark@example.com', '2026-04-04 18:21:02', '2026-04-04 18:21:02'),
('25', '7', '043-123-4567', '0919-222-2222', 'fred@example.com', '2026-04-04 18:21:56', '2026-04-04 18:21:56'),
('26', '6', '042-710-1234', '0918-111-1111', 'janpaul@example.com', '2026-04-04 18:22:04', '2026-04-04 18:22:04'),
('27', '5', '032-123-4567', '0920-111-2222', 'jane@example.com', '2026-04-04 18:23:11', '2026-04-04 18:23:11');

-- ============================================
-- EMPLOYEE_DETAILS
-- ============================================
DELETE FROM `employee_details`; ALTER TABLE `employee_details` AUTO_INCREMENT = 1;

INSERT INTO `employee_details` VALUES 
('1', '2', '1.65', '60.00', 'A+', 'Filipino', '2026-04-04 17:38:18', '2026-04-04 17:38:18'),
('10', '5', '1.60', '55.00', 'A', 'Filipino', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('11', '6', '1.70', '65.00', 'B', 'Filipino', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('12', '7', '1.72', '68.00', 'O', 'Filipino', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('13', '8', '1.74', '70.00', 'A', 'Filipino', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('14', '6', '1.70', '65.00', '', 'Filipino', '2026-04-04 18:13:13', '2026-04-04 18:13:13'),
('15', '6', '1.70', '65.00', '', 'Filipino', '2026-04-04 18:13:22', '2026-04-04 18:13:22'),
('16', '6', '1.70', '65.00', '', 'Filipino', '2026-04-04 18:13:31', '2026-04-04 18:13:31'),
('17', '6', '1.70', '65.00', '', 'Filipino', '2026-04-04 18:13:54', '2026-04-04 18:13:54'),
('18', '6', '1.70', '65.00', '', 'Filipino', '2026-04-04 18:16:33', '2026-04-04 18:16:33'),
('19', '6', '1.70', '65.00', '', 'Filipino', '2026-04-04 18:16:57', '2026-04-04 18:16:57'),
('20', '6', '1.70', '65.00', '', 'Filipino', '2026-04-04 18:19:30', '2026-04-04 18:19:30'),
('21', '7', '1.72', '68.00', '', 'Filipino', '2026-04-04 18:19:46', '2026-04-04 18:19:46'),
('22', '7', '1.72', '68.00', '', 'Filipino', '2026-04-04 18:19:55', '2026-04-04 18:19:55'),
('23', '8', '1.74', '70.00', '', 'Filipino', '2026-04-04 18:21:02', '2026-04-04 18:21:02'),
('24', '7', '1.72', '68.00', '', 'Filipino', '2026-04-04 18:21:56', '2026-04-04 18:21:56'),
('25', '6', '1.70', '65.00', '', 'Filipino', '2026-04-04 18:22:04', '2026-04-04 18:22:04'),
('26', '5', '1.60', '55.00', '', 'Filipino', '2026-04-04 18:23:11', '2026-04-04 18:23:11');

-- ============================================
-- EMPLOYEE_DISCLOSURES
-- ============================================
DELETE FROM `employee_disclosures`; ALTER TABLE `employee_disclosures` AUTO_INCREMENT = 1;

INSERT INTO `employee_disclosures` VALUES 
('1', '2', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 17:38:18', '2026-04-04 17:38:18'),
('2', '6', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:13:13', '2026-04-04 18:13:13'),
('3', '6', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:13:22', '2026-04-04 18:13:22'),
('4', '6', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:13:31', '2026-04-04 18:13:31'),
('5', '6', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:13:54', '2026-04-04 18:13:54'),
('6', '6', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:16:33', '2026-04-04 18:16:33'),
('7', '6', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:16:57', '2026-04-04 18:16:57'),
('8', '6', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:19:30', '2026-04-04 18:19:30'),
('9', '7', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:19:46', '2026-04-04 18:19:46'),
('10', '7', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:19:55', '2026-04-04 18:19:55'),
('11', '8', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:21:02', '2026-04-04 18:21:02'),
('12', '7', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:21:56', '2026-04-04 18:21:56'),
('13', '6', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:22:04', '2026-04-04 18:22:04'),
('14', '5', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '0', '', '2026-04-04 18:23:11', '2026-04-04 18:23:11');

-- ============================================
-- EMPLOYEE_EDUCATION
-- ============================================
DELETE FROM `employee_education`; ALTER TABLE `employee_education` AUTO_INCREMENT = 1;

INSERT INTO `employee_education` VALUES 
('2', '2', 'College', 'Quezon State University', 'Bachelor of Science in Information Technology', '2018-06-12', '2022-05-30', 'Graduate', '2022', 'Cum Laude', '2026-04-04 17:38:18', '2026-04-04 17:38:18'),
('3', '2', 'Secondary', 'Quezon National High School', 'Academic Track (General Education)', '2014-06-01', '2018-04-05', 'Graduate', '2018', 'With Honors', '2026-04-04 17:38:18', '2026-04-04 17:38:18'),
('4', '2', 'Elementary', 'San Isidro Elementary School', 'Primary Education', '2008-06-01', '2014-03-25', 'Graduate', '2014', 'With Honors', '2026-04-04 17:38:18', '2026-04-04 17:38:18');

-- ============================================
-- EMPLOYEE_ELIGIBILITY
-- ============================================
DELETE FROM `employee_eligibility`; ALTER TABLE `employee_eligibility` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_EMERGENCY_CONTACTS
-- ============================================
DELETE FROM `employee_emergency_contacts`; ALTER TABLE `employee_emergency_contacts` AUTO_INCREMENT = 1;

INSERT INTO `employee_emergency_contacts` VALUES 
('2', '2', 'Maria', 'Parent', '09998786652', '2026-04-04 17:38:18', '2026-04-04 17:38:18'),
('11', '5', 'John Doe', 'Spouse', '0920-333-4444', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('12', '6', 'Paul Merilles', 'Father', '0918-222-2222', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('13', '7', 'Andrew Franca', 'Father', '0919-333-3333', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('14', '8', 'Wilson De Torres', 'Father', '0921-444-4444', '2026-04-04 17:54:58', '2026-04-04 17:54:58'),
('15', '6', 'Paul Merilles', 'Father', '0918-222-2222', '2026-04-04 18:13:13', '2026-04-04 18:13:13'),
('16', '6', 'Paul Merilles', 'Father', '0918-222-2222', '2026-04-04 18:13:22', '2026-04-04 18:13:22'),
('17', '6', 'Paul Merilles', 'Father', '0918-222-2222', '2026-04-04 18:13:31', '2026-04-04 18:13:31'),
('18', '6', 'Paul Merilles', 'Father', '0918-222-2222', '2026-04-04 18:13:54', '2026-04-04 18:13:54'),
('19', '6', 'Paul Merilles', 'Father', '0918-222-2222', '2026-04-04 18:16:33', '2026-04-04 18:16:33'),
('20', '6', 'Paul Merilles', 'Father', '0918-222-2222', '2026-04-04 18:16:57', '2026-04-04 18:16:57'),
('21', '6', 'Paul Merilles', 'Father', '0918-222-2222', '2026-04-04 18:19:30', '2026-04-04 18:19:30'),
('22', '7', 'Andrew Franca', 'Father', '0919-333-3333', '2026-04-04 18:19:46', '2026-04-04 18:19:46'),
('23', '7', 'Andrew Franca', 'Father', '0919-333-3333', '2026-04-04 18:19:55', '2026-04-04 18:19:55'),
('24', '8', 'Wilson De Torres', 'Father', '0921-444-4444', '2026-04-04 18:21:02', '2026-04-04 18:21:02'),
('25', '7', 'Andrew Franca', 'Father', '0919-333-3333', '2026-04-04 18:21:56', '2026-04-04 18:21:56'),
('26', '6', 'Paul Merilles', 'Father', '0918-222-2222', '2026-04-04 18:22:04', '2026-04-04 18:22:04'),
('27', '5', 'John Doe', 'Spouse', '0920-333-4444', '2026-04-04 18:23:11', '2026-04-04 18:23:11');

-- ============================================
-- EMPLOYEE_FAMILY
-- ============================================
DELETE FROM `employee_family`; ALTER TABLE `employee_family` AUTO_INCREMENT = 1;

INSERT INTO `employee_family` VALUES 
('1', '2', 'Spouse', 'Paraiso', 'Maria', 'Malveda', '', 'Teacher', '2026-04-04 17:38:18', '2026-04-04 17:38:18'),
('2', '2', 'Father', 'Paraiso', 'Jose', 'Reyes', '', 'Farmer', '2026-04-04 17:38:18', '2026-04-04 17:38:18'),
('3', '2', 'Mother', 'Malveda', 'Maria', 'Santos', NULL, 'Teacher', '2026-04-04 17:38:18', '2026-04-04 17:38:18');

-- ============================================
-- EMPLOYEE_GOVERNMENT_IDS
-- ============================================
DELETE FROM `employee_government_ids`; ALTER TABLE `employee_government_ids` AUTO_INCREMENT = 1;

INSERT INTO `employee_government_ids` VALUES 
('2', '2', '12-3456789-0', '12-345678901-2', '1234-5678-9012', '123-456-789-000', '2026-04-04 17:38:18', '2026-04-04 17:38:18'),
('24', '8', '34-3333333-0', '333333333333', '333333333333', '333-333-333-000', '2026-04-04 18:21:02', '2026-04-04 18:21:02'),
('25', '7', '23-2222222-0', '222222222222', '222222222222', '222-222-222-000', '2026-04-04 18:21:56', '2026-04-04 18:21:56'),
('26', '6', '12-1111111-0', '111111111111', '111111111111', '111-111-111-000', '2026-04-04 18:22:04', '2026-04-04 18:22:04'),
('27', '5', '98-7654321-0', '987654321098', '987654321098', '987-654-321-000', '2026-04-04 18:23:11', '2026-04-04 18:23:11');

-- ============================================
-- EMPLOYEE_LIABILITIES
-- ============================================
DELETE FROM `employee_liabilities`; ALTER TABLE `employee_liabilities` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_MEMBERSHIPS
-- ============================================
DELETE FROM `employee_memberships`; ALTER TABLE `employee_memberships` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_PERSONAL_PROPERTIES
-- ============================================
DELETE FROM `employee_personal_properties`; ALTER TABLE `employee_personal_properties` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_REAL_PROPERTIES
-- ============================================
DELETE FROM `employee_real_properties`; ALTER TABLE `employee_real_properties` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_RECOGNITIONS
-- ============================================
DELETE FROM `employee_recognitions`; ALTER TABLE `employee_recognitions` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_REFERENCES
-- ============================================
DELETE FROM `employee_references`; ALTER TABLE `employee_references` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_SIBLINGS
-- ============================================
DELETE FROM `employee_siblings`; ALTER TABLE `employee_siblings` AUTO_INCREMENT = 1;

INSERT INTO `employee_siblings` VALUES 
('2', '2', 'Paraiso', 'Darrel Anjilo', 'Malveda', '1999-02-02', '2026-04-04 17:38:18', '2026-04-04 17:38:18');

-- ============================================
-- EMPLOYEE_SKILLS
-- ============================================
DELETE FROM `employee_skills`; ALTER TABLE `employee_skills` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_TRAININGS
-- ============================================
DELETE FROM `employee_trainings`; ALTER TABLE `employee_trainings` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_VOLUNTARY_WORK
-- ============================================
DELETE FROM `employee_voluntary_work`; ALTER TABLE `employee_voluntary_work` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_WORK_EXPERIENCE
-- ============================================
DELETE FROM `employee_work_experience`; ALTER TABLE `employee_work_experience` AUTO_INCREMENT = 1;

INSERT INTO `employee_work_experience` VALUES 
('2', '2', '2021-01-15', '2023-12-30', 'Office Clerk', 'ABC Trading Corporation', '15000.00', 'Regular', 'End of Contract', '2026-04-04 17:38:18', '2026-04-04 17:38:18');

-- ============================================
-- EVALUATION_TEMPLATES
-- ============================================
DELETE FROM `evaluation_templates`; ALTER TABLE `evaluation_templates` AUTO_INCREMENT = 1;
INSERT INTO `evaluation_templates` (template_id, template_name, description, target_position, evaluation_type, kra_weight, behavior_weight, status, created_by) VALUES 
(1, 'General Performance Evaluation', 'Standard evaluation for all office staff', 'All Staff', 'Annual', 80.00, 20.00, 'Active', 1);

-- ============================================
-- EVALUATION_CRITERIA
-- ============================================
DELETE FROM `evaluation_criteria`; ALTER TABLE `evaluation_criteria` AUTO_INCREMENT = 1;
INSERT INTO `evaluation_criteria` (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES 
(1, 'KRA', 'Quality of Work', 'Accuracy and thoroughness of output', 'Error rate, standard compliance', 40.00, 'Scale_1_4', 1),
(1, 'KRA', 'Quantity of Work', 'Volume of work accomplished', 'Tasks completed vs Target', 40.00, 'Scale_1_4', 2),
(1, 'Behavior', 'Punctuality', 'Reporting to work on time', 'Attendance record', 50.00, 'Scale_1_4', 3),
(1, 'Behavior', 'Teamwork', 'Cooperation with colleagues', 'Peer feedback', 50.00, 'Scale_1_4', 4);

SET FOREIGN_KEY_CHECKS = 1;
