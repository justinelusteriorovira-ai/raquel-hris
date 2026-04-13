SET FOREIGN_KEY_CHECKS = 0;
USE raquel_hris;

-- ============================================
-- USERS
-- ============================================
DELETE FROM `users`; ALTER TABLE `users` AUTO_INCREMENT = 1;

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `full_name`, `role`, `branch_id`, `profile_picture`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES 
(1, 'admin', 'admin@raquel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Main branch Raquel Admin', 'Admin', 1, null, 1, NOW(), NOW(), NULL),
(2, 'HR Manager', 'manager@raquel.com', '$2y$10$s0lw8zV2epVYQgYqJN6xaeA0Do4NKAaRCm.KDgU4M146JemUlAu2q', 'HR Manager', 'HR Manager', 1, null, 1, NOW(), NOW(), NULL),
(3, 'HR Supervisor', 'supervisor@raquel.com', '$2y$10$nbSJVgKm4IiPVJpqvTRi.ORrKmouVZcuEpUi0RWyPKd.AlA75N3Lq', 'HR Supervisor', 'HR Supervisor', 1, null, 1, NOW(), NOW(), NULL),
(4, 'HR Staff', 'staff@raquel.com', '$2y$10$LyJ5uD7EY7V9RSbHo70nQucFtuHh3Pn/RET7JYHuVKGGEZoStHaia', 'HR Staff', 'HR Staff', 1, null, 1, NOW(), NOW(), NULL);

-- ============================================
-- BRANCHES
-- ============================================
DELETE FROM `branches`; ALTER TABLE `branches` AUTO_INCREMENT = 1;

INSERT INTO `branches` (`branch_id`, `branch_name`, `location`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES 
(1, 'Raquel Pawnshop Main Office', 'San Diego St., Tayabas City, Quezon', 1, NOW(), NOW(), NULL),
(2, 'Paracale', 'Sta Cruz St. Purok Narra, Barangay Poblacion Norte, Paracale, Camarines Norte', 1, NOW(), NOW(), NULL),
(3, 'San Pascual', 'Aquino Avenue, Brgy. Poblacion San Pascual, Batangas', 1, NOW(), NOW(), NULL),
(4, 'Laurel', 'Poblacion Tres, Laurel, Batangas', 1, NOW(), NOW(), NULL),
(5, 'San Andres', 'Fernandez St. Brgy. Poblacion, San Andres, Quezon', 1, NOW(), NOW(), NULL),
(6, 'Mulanay', 'F. Nañadiego st., Brgy. 2 Poblacion, Mulanay Quezon', 1, NOW(), NOW(), NULL),
(7, 'Mogpog', 'Mendez St. Mogpog Marinduque', 1, NOW(), NOW(), NULL),
(8, 'Tiaong', 'Doña Tating Street, Brgy. Poblacion 2 Tiaong, Quezon', 1, NOW(), NOW(), NULL),
(9, 'Tanauan', '64 Pres. JP Laurel Highway, Tanauan City, Batangas', 1, NOW(), NOW(), NULL),
(10, 'Real', 'Purok Rose, Poblacion 1, Real Quezon', 1, NOW(), NOW(), NULL),
(11, 'Lopez', 'Judge Olega Street, Cor San Franciso Brgy Rizal Poblacion Lopez, Quezon', 1, NOW(), NOW(), NULL),
(12, 'Cabuyao', 'National Road, Banlic, Cabuyao City, Laguna', 1, NOW(), NOW(), NULL),
(13, 'Agdangan', 'Aguilar Street Corner Quezon Avenue Pob.1 Agdangan, Quezon', 1, NOW(), NOW(), NULL),
(14, 'Sta. Cruz, M.', 'Brgy. Maharlika, Sta. Cruz Marinduque', 1, NOW(), NOW(), NULL),
(15, 'San Pedro', 'National Road, Brgy. Nueva, San Pedro City, Laguna', 1, NOW(), NOW(), NULL),
(16, 'Los Baños', 'National Road, Batong Malaki, Los Baños Laguna', 1, NOW(), NOW(), NULL),
(17, 'Lipa City (TM Kalaw)', 'TM. Kalaw St., Brgy. 5, Lipa City, Batangas', 1, NOW(), NOW(), NULL),
(18, 'Lipa City (Lipa 1)', 'Block 4, Kapitan Simeon St., Barangay 4, Lipa City, Batangas', 1, NOW(), NOW(), NULL),
(19, 'Dasmarinas', 'Blk K5 Lot 2 San Antonio De Padua II, Dasmarinas Cavite', 1, NOW(), NOW(), NULL),
(20, 'Lapu-Lapu (Pajo)', 'Quezon National Highway, Pajo Lapu-Lapu City, Cebu', 1, NOW(), NOW(), NULL),
(21, 'Lapu-Lapu (Rizal)', 'Punta Rizal Street, Lapu-Lapu City, Cebu', 1, NOW(), NOW(), NULL),
(22, 'Cebu (Labangon)', 'Palmero building, tres de abril Labangon, Cebu City, Cebu', 1, NOW(), NOW(), NULL),
(23, 'CDO (Capistrano)', 'Corner Capistrano-Yacapin Streets, Brgy. 10, Cagayan de Oro City', 1, NOW(), NOW(), NULL),
(24, 'CDO (Carmen)', 'Unit 16, President Building, Elipe Park, Kauswagan Road, Carmen, Cagayan de Oro City', 1, NOW(), NOW(), NULL),
(25, 'Butuan', '1028 Lopez Jaena Street, Brgy. Sikatuna, Butuan City, Agusan del Norte', 1, NOW(), NOW(), NULL),
(26, 'Taytay', '233 Rizal Avenue, Brgy. San Juan, Taytay, Rizal', 1, NOW(), NOW(), NULL);

-- ============================================
-- DEPARTMENTS
-- ============================================
DELETE FROM `departments`; ALTER TABLE `departments` AUTO_INCREMENT = 1;

INSERT INTO `departments` (`department_id`, `department_name`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES 
(1, 'Human Resources', 'HR Management and Recruitment', 1, NOW(), NOW(), NULL),
(2, 'Accounting', 'Financial Documentation and Bookkeeping', 1, NOW(), NOW(), NULL),
(3, 'Operations', 'Daily Pawnshop Operations and Branch Management', 1, NOW(), NOW(), NULL),
(4, 'IT', 'Information Technology and Systems Support', 1, NOW(), NOW(), NULL),
(5, 'Marketing', 'Promotions and Brand Management', 1, NOW(), NOW(), NULL),
(6, 'Sales', 'Customer Acquisition and Sales Strategy', 1, NOW(), NOW(), NULL),
(7, 'Customer Service', 'Client Inquiry and Problem Resolution', 1, NOW(), NOW(), NULL),
(8, 'Finance', 'Financial Planning and Analysis', 1, NOW(), NOW(), NULL),
(9, 'Legal', 'Legal Compliance and Contracts', 1, NOW(), NOW(), NULL),
(10, 'Research and Development', 'System Innovation and Process Improvement', 1, NOW(), NOW(), NULL);

-- ============================================
-- EMPLOYEES
-- ============================================
DELETE FROM `employees`; ALTER TABLE `employees` AUTO_INCREMENT = 1;

-- Creating Names Data via Temporary Source Tables
CREATE TEMPORARY TABLE male_names_source (idx INT, f VARCHAR(100), m VARCHAR(100), l VARCHAR(100));
INSERT INTO male_names_source VALUES 
(1, 'Adrian', 'Cruz', 'Santos'), (2, 'Mark Anthony', 'Rivera', 'Lopez'), (3, 'Joshua Miguel', 'Torres', 'Garcia'), (4, 'Daniel Jose', 'Mendoza', 'Reyes'), (5, 'Kevin Paul', 'Flores', 'Ramos'),
(6, 'John Carlo', 'Bautista', 'Diaz'), (7, 'Christian Angelo', 'Villanueva', 'Cruz'), (8, 'Ryan James', 'Navarro', 'Santos'), (9, 'Nathaniel David', 'Castillo', 'Lopez'), (10, 'Patrick John', 'Fernandez', 'Ramos'),
(11, 'Carl Vincent', 'Morales', 'Diaz'), (12, 'Dennis Mark', 'Salazar', 'Reyes'), (13, 'Bryan Luis', 'Herrera', 'Cruz'), (14, 'Elijah Paul', 'Gomez', 'Torres'), (15, 'Justin Kyle', 'Aquino', 'Santos'),
(16, 'Aaron Michael', 'Chavez', 'Garcia'), (17, 'Jerome Carlo', 'Dela Cruz', 'Reyes'), (18, 'Ivan Joseph', 'Santos', 'Flores'), (19, 'Leo Martin', 'Valdez', 'Cruz'), (20, 'Noel James', 'Castro', 'Ramos'),
(21, 'Ralph Adrian', 'Dominguez', 'Garcia'), (22, 'Vince Patrick', 'Guerrero', 'Diaz'), (23, 'Kenneth Louis', 'Cabrera', 'Santos'), (24, 'Allan Mark', 'Gutierrez', 'Lopez'), (25, 'Paolo Vincent', 'Mendoza', 'Cruz'),
(26, 'Rico James', 'Torres', 'Santos'), (27, 'Francis Kyle', 'Ramos', 'Diaz'), (28, 'Samuel John', 'Flores', 'Garcia'), (29, 'Cedric Mark', 'Villanueva', 'Cruz'), (30, 'Arnold James', 'Herrera', 'Santos'),
(31, 'Jayson Paul', 'Reyes', 'Garcia'), (32, 'Emmanuel Cruz', 'Torres', 'Diaz'), (33, 'Gilbert Mark', 'Santos', 'Lopez'), (34, 'Hector James', 'Ramos', 'Cruz'), (35, 'Ronald Paul', 'Diaz', 'Garcia'),
(36, 'Joel Vincent', 'Flores', 'Santos'), (37, 'Marvin Kyle', 'Torres', 'Cruz'), (38, 'Edgar Mark', 'Garcia', 'Ramos'), (39, 'Roberto James', 'Reyes', 'Diaz'), (40, 'Cesar Paul', 'Santos', 'Cruz'),
(41, 'Angelo Mark', 'Torres', 'Garcia'), (42, 'Dominic Kyle', 'Cruz', 'Ramos'), (43, 'Xavier James', 'Santos', 'Diaz'), (44, 'Julian Mark', 'Garcia', 'Torres'), (45, 'Gabriel Paul', 'Cruz', 'Reyes'),
(46, 'Victor James', 'Santos', 'Lopez'), (47, 'Lawrence Kyle', 'Ramos', 'Diaz'), (48, 'Alfred Mark', 'Torres', 'Garcia'), (49, 'Benedict Paul', 'Santos', 'Cruz'), (50, 'Harold James', 'Garcia', 'Ramos'),
(51, 'Tristan Mark', 'Cruz', 'Santos'), (52, 'Warren Paul', 'Torres', 'Diaz'), (53, 'Lester James', 'Garcia', 'Cruz'), (54, 'Eugene Mark', 'Santos', 'Reyes'), (55, 'Terence Paul', 'Ramos', 'Cruz'),
(56, 'Felix James', 'Torres', 'Garcia'), (57, 'Diego Mark', 'Santos', 'Cruz'), (58, 'Oscar Paul', 'Garcia', 'Ramos'), (59, 'Patrick James', 'Cruz', 'Torres'), (60, 'Julius Mark', 'Santos', 'Garcia'),
(61, 'Mario Paul', 'Torres', 'Cruz'), (62, 'Alvin James', 'Garcia', 'Santos'), (63, 'Nestor Mark', 'Cruz', 'Ramos'), (64, 'Bobby Paul', 'Torres', 'Garcia'), (65, 'Ronnie James', 'Santos', 'Cruz'),
(66, 'Dante Mark', 'Garcia', 'Torres'), (67, 'Kelvin Paul', 'Cruz', 'Santos'), (68, 'Noel Mark', 'Torres', 'Garcia'), (69, 'Rico Paul', 'Santos', 'Cruz'), (70, 'Benjie James', 'Garcia', 'Torres'),
(71, 'Glenn Mark', 'Cruz', 'Santos'), (72, 'Ariel Paul', 'Torres', 'Garcia'), (73, 'Louie James', 'Santos', 'Cruz'), (74, 'Jimmy Mark', 'Garcia', 'Torres'), (75, 'Erwin Paul', 'Cruz', 'Santos'),
(76, 'Sonny James', 'Torres', 'Garcia'), (77, 'Carlo Mark', 'Santos', 'Cruz'), (78, 'Bryan Paul', 'Garcia', 'Torres'), (79, 'Arvin James', 'Cruz', 'Santos'), (80, 'Victor Mark', 'Torres', 'Garcia'),
(81, 'Neil Paul', 'Santos', 'Cruz'), (82, 'Mark Anthony', 'Garcia', 'Torres'), (83, 'John Paul', 'Cruz', 'Santos'), (84, 'Eric Mark', 'Torres', 'Garcia'), (85, 'Jason Paul', 'Santos', 'Cruz'),
(86, 'Kevin Mark', 'Garcia', 'Torres'), (87, 'Jerome Paul', 'Cruz', 'Santos'), (88, 'Dennis Mark', 'Torres', 'Garcia'), (89, 'Ryan Paul', 'Santos', 'Cruz'), (90, 'Joshua Mark', 'Garcia', 'Torres'),
(91, 'Daniel Paul', 'Cruz', 'Santos'), (92, 'Christian Mark', 'Torres', 'Garcia'), (93, 'Adrian Paul', 'Santos', 'Cruz'), (94, 'Nathan Mark', 'Garcia', 'Torres'), (95, 'Patrick Paul', 'Cruz', 'Santos'),
(96, 'Carl Mark', 'Torres', 'Garcia'), (97, 'Justin Paul', 'Santos', 'Cruz'), (98, 'Aaron Mark', 'Garcia', 'Torres'), (99, 'Ivan Paul', 'Cruz', 'Santos'), (100, 'Leo Mark', 'Torres', 'Garcia'),
(101, 'Noel Paul', 'Santos', 'Cruz'), (102, 'Ralph Mark', 'Garcia', 'Torres'), (103, 'Vince Paul', 'Cruz', 'Santos'), (104, 'Kenneth Mark', 'Torres', 'Garcia'), (105, 'Allan Paul', 'Santos', 'Cruz'),
(106, 'Paolo Mark', 'Garcia', 'Torres'), (107, 'Rico Mark', 'Cruz', 'Santos'), (108, 'Francis Paul', 'Torres', 'Garcia'), (109, 'Samuel Mark', 'Santos', 'Cruz'), (110, 'Cedric Paul', 'Garcia', 'Torres'),
(111, 'Arnold Mark', 'Cruz', 'Santos'), (112, 'Jayson Paul', 'Torres', 'Garcia'), (113, 'Emmanuel Mark', 'Santos', 'Cruz'), (114, 'Gilbert Paul', 'Garcia', 'Torres'), (115, 'Hector Mark', 'Cruz', 'Santos'),
(116, 'Ronald Paul', 'Torres', 'Garcia'), (117, 'Joel Mark', 'Santos', 'Cruz'), (118, 'Marvin Paul', 'Garcia', 'Torres'), (119, 'Edgar Mark', 'Cruz', 'Santos'), (120, 'Roberto Paul', 'Torres', 'Garcia'),
(121, 'Cesar Mark', 'Santos', 'Cruz'), (122, 'Angelo Paul', 'Garcia', 'Torres'), (123, 'Dominic Mark', 'Cruz', 'Santos'), (124, 'Xavier Paul', 'Torres', 'Garcia'), (125, 'Julian Mark', 'Santos', 'Cruz'),
(126, 'Gabriel Paul', 'Garcia', 'Torres'), (127, 'Victor Mark', 'Cruz', 'Santos'), (128, 'Lawrence Paul', 'Torres', 'Garcia'), (129, 'Alfred Mark', 'Santos', 'Cruz'), (130, 'Benedict Paul', 'Garcia', 'Torres');

CREATE TEMPORARY TABLE female_names_source (idx INT, f VARCHAR(100), m VARCHAR(100), l VARCHAR(100));
INSERT INTO female_names_source VALUES 
(1, 'Maria Angela', 'Santos', 'Cruz'), (2, 'Anne Nicole', 'Reyes', 'Garcia'), (3, 'Sophia Marie', 'Torres', 'Lopez'), (4, 'Isabella Joy', 'Mendoza', 'Ramos'), (5, 'Jasmine Claire', 'Flores', 'Diaz'),
(6, 'Camille Grace', 'Bautista', 'Reyes'), (7, 'Angelica Mae', 'Villanueva', 'Cruz'), (8, 'Katrina Anne', 'Navarro', 'Santos'), (9, 'Bianca Louise', 'Castillo', 'Lopez'), (10, 'Patricia Joy', 'Fernandez', 'Ramos'),
(11, 'Carla Denise', 'Morales', 'Diaz'), (12, 'Denise Mae', 'Salazar', 'Reyes'), (13, 'Princess Joy', 'Herrera', 'Cruz'), (14, 'Alyssa Faith', 'Gomez', 'Torres'), (15, 'Justine Mae', 'Aquino', 'Santos'),
(16, 'Erica Nicole', 'Chavez', 'Garcia'), (17, 'Jerome Anne Dela', 'Cruz', 'Reyes'), (18, 'Ivana Joy', 'Santos', 'Flores'), (19, 'Leah Marie', 'Valdez', 'Cruz'), (20, 'Noelle Grace', 'Castro', 'Ramos'),
(21, 'Rachel Anne', 'Dominguez', 'Garcia'), (22, 'Vanessa Joy', 'Guerrero', 'Diaz'), (23, 'Kristine Mae', 'Cabrera', 'Santos'), (24, 'Althea Joy', 'Gutierrez', 'Lopez'), (25, 'Paula Grace', 'Mendoza', 'Cruz'),
(26, 'Rica Joy', 'Torres', 'Santos'), (27, 'Frances Mae', 'Ramos', 'Diaz'), (28, 'Samantha Joy', 'Flores', 'Garcia'), (29, 'Cecile Anne', 'Villanueva', 'Cruz'), (30, 'Arlene Joy', 'Herrera', 'Santos'),
(31, 'Jessa Mae', 'Reyes', 'Garcia'), (32, 'Emmanuelle Cruz', 'Torres', 'Diaz'), (33, 'Glaiza Joy', 'Santos', 'Lopez'), (34, 'Hazel Mae', 'Ramos', 'Cruz'), (35, 'Rowena Joy', 'Diaz', 'Garcia'),
(36, 'Jolina Mae', 'Flores', 'Santos'), (37, 'Maricel Joy', 'Torres', 'Cruz'), (38, 'Eden Mae', 'Garcia', 'Ramos'), (39, 'Roberta Joy', 'Reyes', 'Diaz'), (40, 'Celeste Mae', 'Santos', 'Cruz'),
(41, 'Angela Joy', 'Torres', 'Garcia'), (42, 'Dominique Mae', 'Cruz', 'Ramos'), (43, 'Xyra Joy', 'Santos', 'Diaz'), (44, 'Julia Mae', 'Garcia', 'Torres'), (45, 'Gabrielle Joy', 'Cruz', 'Reyes'),
(46, 'Victoria Mae', 'Santos', 'Lopez'), (47, 'Lara Joy', 'Ramos', 'Diaz'), (48, 'Alessandra Mae', 'Torres', 'Garcia'), (49, 'Bernadette Joy', 'Santos', 'Cruz'), (50, 'Hannah Mae', 'Garcia', 'Ramos'),
(51, 'Trisha Joy', 'Cruz', 'Santos'), (52, 'Wendy Mae', 'Torres', 'Diaz'), (53, 'Leslie Joy', 'Garcia', 'Cruz'), (54, 'Eunice Mae', 'Santos', 'Reyes'), (55, 'Teresa Joy', 'Ramos', 'Cruz'),
(56, 'Felicia Mae', 'Torres', 'Garcia'), (57, 'Diana Joy', 'Santos', 'Cruz'), (58, 'Olivia Mae', 'Garcia', 'Ramos'), (59, 'Patricia Joy', 'Cruz', 'Torres'), (60, 'Juliana Mae', 'Santos', 'Garcia'),
(61, 'Mariah Joy', 'Torres', 'Cruz'), (62, 'Alvina Mae', 'Garcia', 'Santos'), (63, 'Nessa Joy', 'Cruz', 'Ramos'), (64, 'Bea Mae', 'Torres', 'Garcia'), (65, 'Rina Joy', 'Santos', 'Cruz'),
(66, 'Dana Mae', 'Garcia', 'Torres'), (67, 'Kyla Joy', 'Cruz', 'Santos'), (68, 'Noemi Mae', 'Torres', 'Garcia'), (69, 'Rica Joy', 'Santos', 'Cruz'), (70, 'Benita Mae', 'Garcia', 'Torres'),
(71, 'Glenda Joy', 'Cruz', 'Santos'), (72, 'Ariane Mae', 'Torres', 'Garcia'), (73, 'Louise Joy', 'Santos', 'Cruz'), (74, 'Jemima Mae', 'Garcia', 'Torres'), (75, 'Erika Joy', 'Cruz', 'Santos'),
(76, 'Sonia Mae', 'Torres', 'Garcia'), (77, 'Carla Joy', 'Santos', 'Cruz'), (78, 'Bryanna Mae', 'Garcia', 'Torres'), (79, 'Arlene Joy', 'Cruz', 'Santos'), (80, 'Vivian Mae', 'Torres', 'Garcia'),
(81, 'Nina Joy', 'Santos', 'Cruz'), (82, 'Marielle Mae', 'Garcia', 'Torres'), (83, 'Joanna Joy', 'Cruz', 'Santos'), (84, 'Eliza Mae', 'Torres', 'Garcia'), (85, 'Janine Joy', 'Santos', 'Cruz'),
(86, 'Kaye Mae', 'Garcia', 'Torres'), (87, 'Jerica Joy', 'Cruz', 'Santos'), (88, 'Dianne Mae', 'Torres', 'Garcia'), (89, 'Rhea Joy', 'Santos', 'Cruz'), (90, 'Joanna Mae', 'Garcia', 'Torres'),
(91, 'Danielle Joy', 'Cruz', 'Santos'), (92, 'Christine Mae', 'Torres', 'Garcia'), (93, 'Adriana Joy', 'Santos', 'Cruz'), (94, 'Nathalie Mae', 'Garcia', 'Torres'), (95, 'Patricia Joy', 'Cruz', 'Santos'),
(96, 'Carla Mae', 'Torres', 'Garcia'), (97, 'Justine Joy', 'Santos', 'Cruz'), (98, 'Angela Mae', 'Garcia', 'Torres'), (99, 'Ivana Joy', 'Cruz', 'Santos'), (100, 'Leah Mae', 'Torres', 'Garcia'),
(101, 'Noelle Joy', 'Santos', 'Cruz'), (102, 'Rachel Mae', 'Garcia', 'Torres'), (103, 'Vanessa Joy', 'Cruz', 'Santos'), (104, 'Kristine Mae', 'Torres', 'Garcia'), (105, 'Althea Joy', 'Santos', 'Cruz'),
(106, 'Paula Mae', 'Garcia', 'Torres'), (107, 'Rica Mae', 'Cruz', 'Santos'), (108, 'Frances Joy', 'Torres', 'Garcia'), (109, 'Samantha Mae', 'Santos', 'Cruz'), (110, 'Cecile Joy', 'Garcia', 'Torres'),
(111, 'Arlene Mae', 'Cruz', 'Santos'), (112, 'Jessa Joy', 'Torres', 'Garcia'), (113, 'Emmanuelle Mae', 'Santos', 'Cruz'), (114, 'Glaiza Joy', 'Garcia', 'Torres'), (115, 'Hazel Mae', 'Cruz', 'Santos'),
(116, 'Rowena Joy', 'Torres', 'Garcia'), (117, 'Jolina Mae', 'Santos', 'Cruz'), (118, 'Maricel Joy', 'Garcia', 'Torres'), (119, 'Eden Mae', 'Cruz', 'Santos'), (120, 'Roberta Joy', 'Torres', 'Garcia'),
(121, 'Celeste Mae', 'Santos', 'Cruz'), (122, 'Angela Joy', 'Garcia', 'Torres'), (123, 'Dominique Mae', 'Cruz', 'Santos'), (124, 'Xyra Joy', 'Torres', 'Garcia'), (125, 'Julia Mae', 'Santos', 'Cruz'),
(126, 'Gabrielle Joy', 'Garcia', 'Torres'), (127, 'Victoria Mae', 'Cruz', 'Santos'), (128, 'Lara Joy', 'Torres', 'Garcia'), (129, 'Alessandra Mae', 'Santos', 'Cruz'), (130, 'Bernadette Joy', 'Garcia', 'Torres');

-- Inserting Employees using the Name Pool
-- Logic: Even Depts = Female, Odd Depts = Male.
-- Index is calculated to cover all 130 names for each gender across 26 branches.
INSERT INTO `employees` (`first_name`, `last_name`, `middle_name`, `name_extension`, `date_of_birth`, `place_of_birth`, `gender`, `civil_status`, `hire_date`, `job_title`, `department_id`, `branch_id`, `employment_status`, `employment_type`, `profile_picture`, `is_active`) 
SELECT 
    IF(d.department_id % 2 = 1, m.f, f.f) as first_name,
    IF(d.department_id % 2 = 1, m.l, f.l) as last_name,
    IF(d.department_id % 2 = 1, m.m, f.m) as middle_name,
    'None' as name_extension,
    DATE_ADD('1975-01-01', INTERVAL (b.branch_id * d.department_id) DAY) as date_of_birth,
    'Philippines' as place_of_birth,
    IF(d.department_id % 2 = 1, 'Male', 'Female') as gender,
    IF(b.branch_id % 3 = 0, 'Married', 'Single') as civil_status,
    DATE_ADD('2018-01-01', INTERVAL (b.branch_id + d.department_id) DAY) as hire_date,
    CONCAT(d.department_name, ' ', ELT(b.branch_id % 3 + 1, 'Associate', 'Specialist', 'Lead')) as job_title,
    d.department_id,
    b.branch_id,
    'Regular',
    'Full-time',
    'assets/img/avatars/default.png',
    1
FROM (SELECT branch_id FROM branches) b
CROSS JOIN departments d
LEFT JOIN male_names_source m ON (d.department_id % 2 = 1 AND m.idx = (b.branch_id - 1) * 5 + (d.department_id - 1) / 2 + 1)
LEFT JOIN female_names_source f ON (d.department_id % 2 = 0 AND f.idx = (b.branch_id - 1) * 5 + (d.department_id / 2 - 1) + 1);

-- ============================================
-- EMPLOYEE_ADDRESSES
-- ============================================
DELETE FROM `employee_addresses`; ALTER TABLE `employee_addresses` AUTO_INCREMENT = 1;

INSERT INTO `employee_addresses` (`employee_id`, `address_type`, `house_no`, `street`, `subdivision`, `barangay`, `city`, `province`, `zip_code`) 
SELECT 
    e.employee_id,
    'Permanent',
    CONCAT(LPAD(e.employee_id, 3, '1'), ' ', ELT(e.employee_id % 5 + 1, 'Purok', 'Phase', 'Block', 'Lot', 'Zone')),
    ELT(e.employee_id % 10 + 1, 'Rizal St.', 'Mabini St.', 'Bonifacio Ave.', 'Quezon St.', 'Luna St.', 'Aguinaldo St.', 'Mabini St.', 'Aurora Blvd.', 'Taft Ave.', 'Macapagal Blvd.'),
    'Countryside Subdivision',
    'Barangay Poblacion',
    b.location,
    'Quezon',
    '4301'
FROM employees e
JOIN branches b ON e.branch_id = b.branch_id;

-- ============================================
-- EMPLOYEE_CONTACTS
-- ============================================
DELETE FROM `employee_contacts`; ALTER TABLE `employee_contacts` AUTO_INCREMENT = 1;

INSERT INTO `employee_contacts` (`employee_id`, `telephone_number`, `mobile_number`, `personal_email`) 
SELECT 
    e.employee_id,
    CONCAT('042-', LPAD(e.employee_id + 500, 3, '0'), '-7890'),
    CONCAT('09', LPAD(e.employee_id * 1234567, 9, '0')),
    CONCAT(LOWER(REPLACE(e.first_name, ' ', '')), '.', LOWER(e.last_name), e.employee_id, '@raquel-pawnshop.com.ph')
FROM employees e;

-- ============================================
-- EMPLOYEE_EMERGENCY_CONTACTS
-- ============================================
DELETE FROM `employee_emergency_contacts`; ALTER TABLE `employee_emergency_contacts` AUTO_INCREMENT = 1;

INSERT INTO `employee_emergency_contacts` (`employee_id`, `contact_name`, `relationship`, `contact_number`) 
SELECT 
    e.employee_id,
    CONCAT(ELT(e.employee_id % 5 + 1, 'Pedro', 'Ana', 'Luis', 'Sofia', 'Ben'), ' ', e.last_name),
    ELT(e.employee_id % 3 + 1, 'Spouse', 'Parent', 'Sibling'),
    CONCAT('09', LPAD(e.employee_id * 9876543, 9, '0'))
FROM employees e;

-- ============================================
-- EMPLOYEE_DETAILS
-- ============================================
DELETE FROM `employee_details`; ALTER TABLE `employee_details` AUTO_INCREMENT = 1;

INSERT INTO `employee_details` (`employee_id`, `height_m`, `weight_kg`, `blood_type`, `citizenship`) 
SELECT 
    e.employee_id,
    1.65 + (e.employee_id % 10) / 100,
    55.0 + (e.employee_id % 20),
    ELT(e.employee_id % 4 + 1, 'A+', 'B+', 'AB+', 'O+'),
    'Filipino'
FROM employees e;

-- ============================================
-- EMPLOYEE_GOVERNMENT_IDS
-- ============================================
DELETE FROM `employee_government_ids`; ALTER TABLE `employee_government_ids` AUTO_INCREMENT = 1;

INSERT INTO `employee_government_ids` (`employee_id`, `sss_number`, `philhealth_number`, `pagibig_number`, `tin_number`) 
SELECT 
    e.employee_id,
    CONCAT('33-', LPAD(e.employee_id, 7, '0'), '-', e.employee_id % 9),
    CONCAT('12-', LPAD(e.employee_id, 9, '0'), '-', e.employee_id % 9),
    CONCAT('1234-', LPAD(e.employee_id, 4, '0'), '-', e.employee_id % 9, '000'),
    CONCAT(LPAD(e.employee_id, 3, '1'), '-', LPAD(e.employee_id + 100, 3, '2'), '-', LPAD(e.employee_id + 200, 3, '3'), '-000')
FROM employees e;

-- ============================================
-- EMPLOYEE_WORK_EXPERIENCE
-- ============================================
DELETE FROM `employee_work_experience`; ALTER TABLE `employee_work_experience` AUTO_INCREMENT = 1;

INSERT INTO `employee_work_experience` (`employee_id`, `date_from`, `date_to`, `job_title`, `company_name`, `monthly_salary`, `appointment_status`, `reason_for_leaving`) 
SELECT 
    e.employee_id,
    DATE_SUB(e.hire_date, INTERVAL 5 YEAR),
    DATE_SUB(e.hire_date, INTERVAL 1 YEAR),
    CONCAT('Junior ', e.job_title),
    'Previous Local Business Inc.',
    CASE 
        WHEN e.department_id IN (1, 4, 9) THEN 35000.00
        WHEN e.department_id IN (2, 8) THEN 30000.00
        ELSE 22000.00
    END,
    'Permanent',
    'Career Development'
FROM employees e;

SET FOREIGN_KEY_CHECKS = 1;
