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
('1', 'Raquel Pawnshop Main Office', 'San Diego St., Tayabas City, Quezon', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('2', 'Paracale', 'Sta Cruz St. Purok Narra, Barangay Poblacion Norte, Paracale, Camarines Norte', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('3', 'San Pascual', 'Aquino Avenue, Brgy. Poblacion San Pascual, Batangas', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('4', 'Laurel', 'Poblacion Tres, Laurel, Batangas', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('5', 'San Andres', 'Fernandez St. Brgy. Poblacion, San Andres, Quezon', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('6', 'Mulanay', 'F. Nañadiego st., Brgy. 2 Poblacion, Mulanay Quezon', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('7', 'Mogpog', 'Mendez St. Mogpog Marinduque', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('8', 'Tiaong', 'Doña Tating Street, Brgy. Poblacion 2 Tiaong, Quezon', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('9', 'Tanauan', '64 Pres. JP Laurel Highway, Tanauan City, Batangas', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('10', 'Real', 'Purok Rose, Poblacion 1, Real Quezon', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('11', 'Lopez', 'Judge Olega Street, Cor San Franciso Brgy Rizal Poblacion Lopez, Quezon', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('12', 'Cabuyao', 'National Road, Banlic, Cabuyao City, Laguna', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('13', 'Agdangan', 'Aguilar Street Corner Quezon Avenue Pob.1 Agdangan, Quezon', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('14', 'Sta. Cruz, M.', 'Brgy. Maharlika, Sta. Cruz Marinduque', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('15', 'San Pedro', 'National Road, Brgy. Nueva, San Pedro City, Laguna', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('16', 'Los Baños', 'National Road, Batong Malaki, Los Baños Laguna', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('17', 'Lipa City (TM Kalaw)', 'TM. Kalaw St., Brgy. 5, Lipa City, Batangas', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('18', 'Lipa City (Lipa 1)', 'Block 4, Kapitan Simeon St., Barangay 4, Lipa City, Batangas', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('19', 'Dasmarinas', 'Blk K5 Lot 2 San Antonio De Padua II, Dasmarinas Cavite', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('20', 'Lapu-Lapu (Pajo)', 'Quezon National Highway, Pajo Lapu-Lapu City, Cebu', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('21', 'Lapu-Lapu (Rizal)', 'Punta Rizal Street, Lapu-Lapu City, Cebu', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('22', 'Cebu (Labangon)', 'Palmero building, tres de abril Labangon, Cebu City, Cebu', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('23', 'CDO (Capistrano)', 'Corner Capistrano-Yacapin Streets, Brgy. 10, Cagayan de Oro City', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('24', 'CDO (Carmen)', 'Unit 16, President Building, Elipe Park, Kauswagan Road, Carmen, Cagayan de Oro City', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('25', 'Butuan', '1028 Lopez Jaena Street, Brgy. Sikatuna, Butuan City, Agusan del Norte', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL),
('26', 'Taytay', '233 Rizal Avenue, Brgy. San Juan, Taytay, Rizal', '1', '2026-04-05 12:47:00', '2026-04-05 12:47:00', NULL);

-- ============================================
-- DEPARTMENTS
-- ============================================
DELETE FROM `departments`; ALTER TABLE `departments` AUTO_INCREMENT = 1;

INSERT INTO `departments` VALUES 
('1', 'Human Resources', 'HR Department', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('2', 'Accounting', 'Accounting Department', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('3', 'Operations', 'Operations Department', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('4', 'IT', 'IT Department', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('5', 'Marketing', 'Marketing Department', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('6', 'Sales', 'Sales Department', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('7', 'Customer Service', 'Customer Service Department', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('8', 'Finance', 'Finance Department', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('9', 'Legal', 'Legal Department', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL),
('10', 'Research and Development', 'Research and Development Department', '1', '2026-04-04 17:10:36', '2026-04-04 17:10:36', NULL);

-- ============================================
-- EMPLOYEES
-- ============================================
DELETE FROM `employees`; ALTER TABLE `employees` AUTO_INCREMENT = 1;

INSERT INTO `employees` VALUES 
('1', 'Jan Paul', 'Merilles', 'Luna', '', '1995-03-20', 'Lucena City', 'Male', 'Single', '2024-03-01', 'Data Analyst', '4', '1', 'Regular', 'Full-time', NULL, '1', '2026-04-05 11:57:00', '2026-04-05 11:57:00', NULL),
('2', 'Fred Andrew', 'Franca', 'Santos', '', '1993-07-10', 'Batangas City', 'Male', 'Single', '2024-03-15', 'Systems Administrator', '4', '11', 'Regular', 'Full-time', NULL, '1', '2026-04-05 11:57:00', '2026-04-05 11:57:00', NULL),
('3', 'Mark Wilson', 'De Torres', 'Gomez', '', '1994-11-25', 'Cavite City', 'Male', 'Single', '2024-04-01', 'Accountant', '8', '1', 'Regular', 'Full-time', NULL, '1', '2026-04-05 11:57:00', '2026-04-05 11:57:00', NULL),
('4', 'Prince Allyrice', 'Capili', 'Calapit', '', '1996-05-15', 'Manila City', 'Male', 'Single', '2024-04-15', 'Marketing Specialist', '5', '1', 'Regular', 'Full-time', NULL, '1', '2026-04-05 11:57:00', '2026-04-05 11:57:00', NULL),
('5', 'Jhondarryl', 'Hernandez', 'Reyes', '', '1997-08-22', 'Quezon City', 'Male', 'Single', '2024-05-01', 'Software Developer', '4', '1', 'Regular', 'Full-time', NULL, '1', '2026-04-05 11:57:00', '2026-04-05 11:57:00', NULL);
-- ============================================
-- EMPLOYEE_ADDRESSES
-- ============================================
DELETE FROM `employee_addresses`; ALTER TABLE `employee_addresses` AUTO_INCREMENT = 1;

INSERT INTO `employee_addresses` VALUES 
('1', '1', 'Residential', '789', 'High St', 'Hillside', 'San Roque', 'Lucena City', 'Quezon', '4301', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('2', '1', 'Permanent', '789', 'High St', 'Hillside', 'San Roque', 'Lucena City', 'Quezon', '4301', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('3', '2', 'Residential', '101', 'Pine St', 'Woodland', 'Sta Rita', 'Batangas City', 'Batangas', '4200', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('4', '2', 'Permanent', '101', 'Pine St', 'Woodland', 'Sta Rita', 'Batangas City', 'Batangas', '4200', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('5', '3', 'Residential', '202', 'Oak St', 'Greenview', 'Imus', 'Cavite City', 'Cavite', '4100', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('6', '3', 'Permanent', '202', 'Oak St', 'Greenview', 'Imus', 'Cavite City', 'Cavite', '4100', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('7', '4', 'Residential', '303', 'Mango Ave', 'Sunset Village', 'San Jose', 'Manila', 'Metro Manila', '1002', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('8', '4', 'Permanent', '303', 'Mango Ave', 'Sunset Village', 'San Jose', 'Manila', 'Metro Manila', '1002', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('9', '5', 'Residential', '404', 'Sampaguita St', 'Garden Homes', 'Fairview', 'Quezon City', 'Metro Manila', '1121', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('10', '5', 'Permanent', '404', 'Sampaguita St', 'Garden Homes', 'Fairview', 'Quezon City', 'Metro Manila', '1121', '2026-04-05 11:57:00', '2026-04-05 11:57:00');

-- ============================================
-- EMPLOYEE_CHILDREN
-- ============================================
DELETE FROM `employee_children`; ALTER TABLE `employee_children` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_CONTACTS
-- ============================================
DELETE FROM `employee_contacts`; ALTER TABLE `employee_contacts` AUTO_INCREMENT = 1;

INSERT INTO `employee_contacts` VALUES 
('1', '1', '042-710-1234', '0918-111-1111', 'janpaul@example.com', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('2', '2', '043-123-4567', '0919-222-2222', 'fred@example.com', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('3', '3', '046-123-4567', '0921-333-3333', 'mark@example.com', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('4', '4', '02-555-6789', '0922-444-4444', 'prince@example.com', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('5', '5', '02-777-8888', '0923-555-5555', 'jhondarryl@example.com', '2026-04-05 11:57:00', '2026-04-05 11:57:00');

-- ============================================
-- EMPLOYEE_DETAILS
-- ============================================
DELETE FROM `employee_details`; ALTER TABLE `employee_details` AUTO_INCREMENT = 1;

INSERT INTO `employee_details` VALUES 
('1', '1', '1.7', '65', 'B', 'Filipino', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('2', '2', '1.72', '68', 'O', 'Filipino', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('3', '3', '1.74', '70', 'A', 'Filipino', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('4', '4', '1.68', '62', 'AB', 'Filipino', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('5', '5', '1.75', '72', 'O', 'Filipino', '2026-04-05 11:57:00', '2026-04-05 11:57:00');

-- ============================================
-- EMPLOYEE_DISCLOSURES
-- ============================================
DELETE FROM `employee_disclosures`; ALTER TABLE `employee_disclosures` AUTO_INCREMENT = 1;

INSERT INTO `employee_disclosures` VALUES 
('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');

-- ============================================
-- EMPLOYEE_EDUCATION
-- ============================================
DELETE FROM `employee_education`; ALTER TABLE `employee_education` AUTO_INCREMENT = 1;

INSERT INTO `employee_education` VALUES 
('', '', '', '', '', '', '', '', '', '', '', '');

-- ============================================
-- EMPLOYEE_ELIGIBILITY
-- ============================================
DELETE FROM `employee_eligibility`; ALTER TABLE `employee_eligibility` AUTO_INCREMENT = 1;

-- ============================================
-- EMPLOYEE_EMERGENCY_CONTACTS
-- ============================================
DELETE FROM `employee_emergency_contacts`; ALTER TABLE `employee_emergency_contacts` AUTO_INCREMENT = 1;

INSERT INTO `employee_emergency_contacts` VALUES 
('1', '1', 'Paul Merilles', 'Father', '0918-222-2222', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('2', '2', 'Andrew Franca', 'Father', '0919-333-3333', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('3', '3', 'Wilson De Torres', 'Father', '0921-444-4444', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('4', '4', 'Allyrice Capili', 'Mother', '0922-555-5555', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('5', '5', 'Maria Hernandez', 'Mother', '0923-666-6666', '2026-04-05 11:57:00', '2026-04-05 11:57:00');

-- ============================================
-- EMPLOYEE_FAMILY
-- ============================================
DELETE FROM `employee_family`; ALTER TABLE `employee_family` AUTO_INCREMENT = 1;

INSERT INTO `employee_family` VALUES 
('', '', '', '', '', '', '', '', '', '');

-- ============================================
-- EMPLOYEE_GOVERNMENT_IDS
-- ============================================
DELETE FROM `employee_government_ids`; ALTER TABLE `employee_government_ids` AUTO_INCREMENT = 1;

INSERT INTO `employee_government_ids` VALUES 
('1', '1', '12-1111111-0', '111111111111', '111111111111', '111-111-111-000', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('2', '2', '23-2222222-0', '222222222222', '222222222222', '222-222-222-000', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('3', '3', '34-3333333-0', '333333333333', '333333333333', '333-333-333-000', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('4', '4', '45-4444444-0', '444444444444', '444444444444', '444-444-444-000', '2026-04-05 11:57:00', '2026-04-05 11:57:00'),
('5', '5', '56-5555555-0', '555555555555', '555555555555', '555-555-555-000', '2026-04-05 11:57:00', '2026-04-05 11:57:00');

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
('', '', '', '', '', '', '', '');

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
('', '', '', '', '', '', '', '', '', '', '');

-- ============================================
-- EVALUATION_TEMPLATES
-- ============================================
DELETE FROM `evaluation_templates`; ALTER TABLE `evaluation_templates` AUTO_INCREMENT = 1;
INSERT INTO `evaluation_templates` (template_id, template_name, description, target_position, evaluation_type, kra_weight, behavior_weight, status, created_by) VALUES 
('', '', '', '', '', '', '', '', '');

-- ============================================
-- EVALUATION_CRITERIA
-- ============================================
DELETE FROM `evaluation_criteria`; ALTER TABLE `evaluation_criteria` AUTO_INCREMENT = 1;
INSERT INTO `evaluation_criteria` (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES 
('', '', '', '', '', '', '', '');

SET FOREIGN_KEY_CHECKS = 1;
