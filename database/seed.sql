USE raquel_hris;

-- ============================================
-- 1. Branches
-- ============================================
INSERT INTO branches (branch_id, branch_name, location) VALUES
(1, 'Main Office', 'RGC Building Brgy. Mayuwi Sitio 1, Tayabas City, Quezon Province');


-- ============================================
-- 2. Users (password: password123)
-- ============================================
INSERT INTO users (user_id, username, email, password_hash, full_name, role, branch_id, profile_picture, is_active) VALUES
(1, 'admin', 'admin@raquel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Main branch Raquel Admin', 'Admin', NULL, NULL, 1);
/*
(2, 'manager', 'manager@raquel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager Maridel', 'HR Manager', 1, NULL, 1),
(3, 'supervisor', 'supervisor@raquel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Supervisor Christine', 'HR Supervisor', 1, NULL, 1),
(4, 'staff', 'staff@raquel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Fred Andrew', 'HR Staff', 1, NULL, 1);*/

-- ============================================
-- 3. Employees (Core Data)
-- ============================================
/*
INSERT INTO employees (employee_id, first_name, last_name, middle_name, date_of_birth, gender, civil_status, hire_date, job_title, department, branch_id, employment_status, employment_type, is_active) VALUES 
(1, 'John', 'Doe', 'M.', '1981-12-19', 'Female', 'Married', '2023-12-19', 'Manager', 'HR', 2, 'Contractual', 'Full-time', 1),
(2, 'Jane', 'Santos', 'M.', '1994-12-15', 'Male', 'Single', '2021-12-15', 'Specialist', 'Operations', 1, 'Probationary', 'Full-time', 1),
(3, 'Michael', 'Smith', 'M.', '2000-08-27', 'Male', 'Other', '2023-08-27', 'Developer', 'Operations', 2, 'Regular', 'Part-time', 1),
(4, 'Maria', 'Dela Cruz', 'M.', '1999-06-02', 'Male', 'Single', '2022-06-02', 'Coordinator', 'IT', 1, 'Regular', 'Full-time', 1),
(5, 'Robert', 'Brown', 'M.', '1980-01-07', 'Female', 'Single', '2024-01-07', 'Director', 'IT', 1, 'Contractual', 'Part-time', 1),
(6, 'Juan', 'Rizal', 'M.', '1980-07-18', 'Male', 'Married', '2024-07-18', 'Director', 'Operations', 2, 'Contractual', 'Part-time', 1),
(7, 'William', 'Jones', 'M.', '1992-05-06', 'Female', 'Married', '2021-05-06', 'Analyst', 'Operations', 1, 'Probationary', 'Part-time', 1),
(8, 'Jose', 'Bonifacio', 'M.', '1988-04-23', 'Female', 'Widowed', '2023-04-23', 'Manager', 'Sales', 2, 'Regular', 'Full-time', 1),
(9, 'David', 'Garcia', 'M.', '1984-02-16', 'Female', 'Married', '2021-02-16', 'Manager', 'Finance', 1, 'Regular', 'Part-time', 1),
(10, 'Gabriela', 'Silang', 'M.', '1982-10-07', 'Female', 'Single', '2024-10-07', 'Developer', 'IT', 1, 'Contractual', 'Full-time', 1),
(11, 'Richard', 'Miller', 'M.', '1985-01-20', 'Male', 'Married', '2022-01-20', 'Manager', 'Sales', 1, 'Probationary', 'Full-time', 1),
(12, 'Corazon', 'Aquino', 'M.', '1996-04-20', 'Female', 'Widowed', '2022-04-20', 'Manager', 'Operations', 1, 'Probationary', 'Full-time', 1),
(13, 'Joseph', 'Davis', 'M.', '1993-07-05', 'Male', 'Married', '2020-07-05', 'Coordinator', 'HR', 2, 'Contractual', 'Part-time', 1),
(14, 'Emilio', 'Aguinaldo', 'M.', '1982-11-17', 'Female', 'Single', '2023-11-17', 'Director', 'Sales', 1, 'Probationary', 'Part-time', 1),
(15, 'Thomas', 'Martinez', 'M.', '1982-07-17', 'Male', 'Widowed', '2022-07-17', 'Coordinator', 'Finance', 2, 'Probationary', 'Part-time', 1),
(16, 'Melchora', 'Aquino', 'M.', '1995-09-22', 'Female', 'Single', '2020-09-22', 'Coordinator', 'Finance', 2, 'Probationary', 'Part-time', 1),
(17, 'Christopher', 'Lopez', 'M.', '1993-09-04', 'Male', 'Married', '2023-09-04', 'Specialist', 'IT', 2, 'Contractual', 'Part-time', 1),
(18, 'Teresa', 'Reyes', 'M.', '1993-11-14', 'Female', 'Widowed', '2022-11-14', 'Analyst', 'Marketing', 2, 'Regular', 'Full-time', 1),
(19, 'Charles', 'Hernandez', 'M.', '1997-12-20', 'Female', 'Single', '2022-12-20', 'Developer', 'Marketing', 2, 'Probationary', 'Part-time', 1),
(20, 'Gregoria', 'Mabini', 'M.', '1995-06-13', 'Female', 'Separated', '2021-06-13', 'Manager', 'Marketing', 1, 'Regular', 'Part-time', 1),
(21, 'Daniel', 'Clark', 'M.', '1986-02-22', 'Female', 'Separated', '2023-02-22', 'Analyst', 'Operations', 2, 'Probationary', 'Full-time', 1),
(22, 'Marcelo', 'Lewis', 'M.', '1980-10-23', 'Female', 'Married', '2020-10-23', 'Coordinator', 'Operations', 1, 'Contractual', 'Full-time', 1),
(23, 'Matthew', 'Robinson', 'M.', '1991-04-27', 'Male', 'Separated', '2024-04-27', 'Specialist', 'Finance', 1, 'Probationary', 'Part-time', 1),
(24, 'Anthony', 'Walker', 'M.', '1994-05-24', 'Male', 'Married', '2022-05-24', 'Developer', 'IT', 2, 'Probationary', 'Full-time', 1),
(25, 'Joshua', 'Young', 'M.', '2000-04-18', 'Female', 'Separated', '2023-04-18', 'Developer', 'Operations', 2, 'Probationary', 'Full-time', 1),
(26, 'Ashley', 'Allen', 'M.', '1988-01-20', 'Female', 'Separated', '2021-01-20', 'Analyst', 'Finance', 1, 'Probationary', 'Full-time', 1),
(27, 'Brian', 'King', 'M.', '1990-04-03', 'Female', 'Separated', '2024-04-03', 'Coordinator', 'IT', 1, 'Contractual', 'Part-time', 1),
(28, 'Kenneth', 'Wright', 'M.', '1981-01-17', 'Male', 'Single', '2023-01-17', 'Analyst', 'Operations', 1, 'Contractual', 'Part-time', 1),
(29, 'Megan', 'Scott', 'M.', '1988-07-22', 'Male', 'Widowed', '2021-07-22', 'Specialist', 'Operations', 2, 'Probationary', 'Part-time', 1),
(30, 'Melissa', 'Torres', 'M.', '1991-12-05', 'Male', 'Single', '2021-12-05', 'Manager', 'IT', 1, 'Contractual', 'Full-time', 1);

-- ============================================
-- 4. Employee IDs (Government)
-- ============================================
INSERT INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(1, '525028016', '292417715', '238560819', '529835223'),
(2, '190466840', '594972011', '139094608', '371909054'),
(3, '170752519', '696328078', '452748246', '982349607'),
(4, '710236638', '552935461', '200828206', '877206450'),
(5, '741306013', '546841386', '122691053', '314884719'),
(6, '868221293', '452186584', '314117371', '659587766'),
(7, '648878997', '887013137', '349283769', '135797685'),
(8, '434647616', '841485746', '245173125', '902545281'),
(9, '803947414', '558223111', '365546025', '821063515'),
(10, '908352669', '537611208', '379972420', '336890259'),
(11, '472695796', '718062024', '284153753', '855290317'),
(12, '426001719', '132367113', '241252991', '384280380'),
(13, '282658600', '681196366', '905133028', '684130388'),
(14, '990533623', '636431640', '103157377', '718171639'),
(15, '321942000', '100092660', '159038483', '961981000'),
(16, '151875240', '346153507', '850805271', '588000002'),
(17, '333840738', '333304914', '289756054', '122314212'),
(18, '957226903', '875810471', '381990489', '824313255'),
(19, '565885746', '541516832', '598031620', '651805409'),
(20, '211529104', '429342604', '356091724', '875774280'),
(21, '386807935', '516726958', '800426410', '207884886'),
(22, '884750612', '894773250', '861517526', '735064276'),
(23, '546382337', '790938954', '945743076', '201025076'),
(24, '245642002', '174130236', '589574691', '118518012'),
(25, '336592727', '580650121', '807211081', '183980632'),
(26, '662436268', '148852880', '418950193', '929403979'),
(27, '610519964', '339342198', '528398150', '585727001'),
(28, '262009332', '324065837', '764466846', '578515362'),
(29, '524889995', '674667203', '226498452', '258521471'),
(30, '923285692', '830139747', '636236970', '506354116');

-- ============================================
-- 5. Employee Contacts
-- ============================================
INSERT INTO employee_contacts (employee_id, personal_email, mobile_number) VALUES
(1, 'john.doe492@raquel.com', '09745846683'),
(2, 'jane.santos646@raquel.com', '09734171967'),
(3, 'michael.smith727@raquel.com', '09409012054'),
(4, 'maria.dela cruz305@raquel.com', '09799601344'),
(5, 'robert.brown178@raquel.com', '09764028470'),
(6, 'juan.rizal345@raquel.com', '09616418652'),
(7, 'william.jones290@raquel.com', '09869480945'),
(8, 'jose.bonifacio886@raquel.com', '09104724064'),
(9, 'david.garcia413@raquel.com', '09746021402'),
(10, 'gabriela.silang144@raquel.com', '09574353487'),
(11, 'richard.miller244@raquel.com', '09399332043'),
(12, 'corazon.aquino388@raquel.com', '09438071869'),
(13, 'joseph.davis222@raquel.com', '09636681750'),
(14, 'emilio.aguinaldo949@raquel.com', '09335441624'),
(15, 'thomas.martinez534@raquel.com', '09162103488'),
(16, 'melchora.aquino958@raquel.com', '09284306649'),
(17, 'christopher.lopez561@raquel.com', '09641685922'),
(18, 'teresa.reyes416@raquel.com', '09249886591'),
(19, 'charles.hernandez551@raquel.com', '09715376172'),
(20, 'gregoria.mabini763@raquel.com', '09143906361'),
(21, 'daniel.clark444@raquel.com', '09917273378'),
(22, 'marcelo.lewis892@raquel.com', '09471694208'),
(23, 'matthew.robinson369@raquel.com', '09686530999'),
(24, 'anthony.walker718@raquel.com', '09386910447'),
(25, 'joshua.young195@raquel.com', '09321824223'),
(26, 'ashley.allen789@raquel.com', '09672198874'),
(27, 'brian.king264@raquel.com', '09961497118'),
(28, 'kenneth.wright400@raquel.com', '09388644355'),
(29, 'megan.scott622@raquel.com', '09603458928'),
(30, 'melissa.torres495@raquel.com', '09522315436');

-- ============================================
-- 6. Employee Emergency Contacts
-- ============================================
INSERT INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(1, 'Emergency Contact 1', 'Family', '09148419808'),
(2, 'Emergency Contact 2', 'Family', '09652874914'),
(3, 'Emergency Contact 3', 'Family', '09366353069'),
(4, 'Emergency Contact 4', 'Family', '09114718125'),
(5, 'Emergency Contact 5', 'Family', '09397454635'),
(6, 'Emergency Contact 6', 'Family', '09435679580'),
(7, 'Emergency Contact 7', 'Family', '09268842495'),
(8, 'Emergency Contact 8', 'Family', '09228522862'),
(9, 'Emergency Contact 9', 'Family', '09265468128'),
(10, 'Emergency Contact 10', 'Family', '09451810714'),
(11, 'Emergency Contact 11', 'Family', '09961150267'),
(12, 'Emergency Contact 12', 'Family', '09454600628'),
(13, 'Emergency Contact 13', 'Family', '09814802542'),
(14, 'Emergency Contact 14', 'Family', '09487153273'),
(15, 'Emergency Contact 15', 'Family', '09457564092'),
(16, 'Emergency Contact 16', 'Family', '09431415190'),
(17, 'Emergency Contact 17', 'Family', '09648963601'),
(18, 'Emergency Contact 18', 'Family', '09175295719'),
(19, 'Emergency Contact 19', 'Family', '09853012319'),
(20, 'Emergency Contact 20', 'Family', '09539524003'),
(21, 'Emergency Contact 21', 'Family', '09287282101'),
(22, 'Emergency Contact 22', 'Family', '09293802960'),
(23, 'Emergency Contact 23', 'Family', '09927192797'),
(24, 'Emergency Contact 24', 'Family', '09961324206'),
(25, 'Emergency Contact 25', 'Family', '09438323559'),
(26, 'Emergency Contact 26', 'Family', '09118964915'),
(27, 'Emergency Contact 27', 'Family', '09133912925'),
(28, 'Emergency Contact 28', 'Family', '09647838297'),
(29, 'Emergency Contact 29', 'Family', '09694776247'),
(30, 'Emergency Contact 30', 'Family', '09696713027');

-- ============================================
-- 7. Employee Addresses (Residential)
-- ============================================
INSERT INTO employee_addresses (employee_id, address_type, street, city) VALUES
(1, 'Residential', '913 Street Name', 'City'),
(2, 'Residential', '396 Street Name', 'City'),
(3, 'Residential', '399 Street Name', 'City'),
(4, 'Residential', '529 Street Name', 'City'),
(5, 'Residential', '225 Street Name', 'City'),
(6, 'Residential', '757 Street Name', 'City'),
(7, 'Residential', '50 Street Name', 'City'),
(8, 'Residential', '786 Street Name', 'City'),
(9, 'Residential', '144 Street Name', 'City'),
(10, 'Residential', '266 Street Name', 'City'),
(11, 'Residential', '663 Street Name', 'City'),
(12, 'Residential', '688 Street Name', 'City'),
(13, 'Residential', '169 Street Name', 'City'),
(14, 'Residential', '289 Street Name', 'City'),
(15, 'Residential', '429 Street Name', 'City'),
(16, 'Residential', '629 Street Name', 'City'),
(17, 'Residential', '762 Street Name', 'City'),
(18, 'Residential', '470 Street Name', 'City'),
(19, 'Residential', '351 Street Name', 'City'),
(20, 'Residential', '359 Street Name', 'City'),
(21, 'Residential', '137 Street Name', 'City'),
(22, 'Residential', '734 Street Name', 'City'),
(23, 'Residential', '462 Street Name', 'City'),
(24, 'Residential', '577 Street Name', 'City'),
(25, 'Residential', '120 Street Name', 'City'),
(26, 'Residential', '194 Street Name', 'City'),
(27, 'Residential', '63 Street Name', 'City'),
(28, 'Residential', '385 Street Name', 'City'),
(29, 'Residential', '24 Street Name', 'City'),
(30, 'Residential', '683 Street Name', 'City');

-- ============================================
-- 8. Evaluation Templates
-- ============================================
INSERT INTO evaluation_templates (template_id, template_name, description, target_position, status, created_by) VALUES
(1, 'Standard Performance Evaluation', 'Standard quarterly performance evaluation template for all employees.', NULL, 'Active', 2);

INSERT INTO evaluation_criteria (criterion_id, template_id, criterion_name, description, weight, scoring_method, sort_order) VALUES
(1, 1, 'Job Knowledge & Skills', 'Demonstrates understanding of job responsibilities and required skills.', 30.00, 'Scale_1_5', 1),
(2, 1, 'Quality of Work', 'Accuracy, thoroughness, and reliability of work output.', 25.00, 'Scale_1_5', 2),
(3, 1, 'Communication & Teamwork', 'Effectiveness in communication and collaboration with colleagues.', 25.00, 'Scale_1_5', 3),
(4, 1, 'Attendance & Punctuality', 'Consistency of attendance and adherence to work schedule.', 20.00, 'Scale_1_5', 4);

-- ============================================
-- 9. Sample Evaluations
-- ============================================
INSERT INTO evaluations (evaluation_id, employee_id, template_id, evaluation_period_start, evaluation_period_end, submitted_by, status, total_score, performance_level, submitted_date, staff_comments) VALUES
(1, 1, 1, '2026-01-01', '2026-03-31', 4, 'Pending Supervisor', 85.00, 'Above Average', '2026-03-15 10:30:00', 'Employee consistently demonstrates excellent job knowledge.');

INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
(1, 1, 4, 24.00),
(1, 2, 4, 20.00),
(1, 3, 5, 25.00),
(1, 4, 4, 16.00);

-- Approved Evaluation
INSERT INTO evaluations (evaluation_id, employee_id, template_id, evaluation_period_start, evaluation_period_end, submitted_by, endorsed_by, approved_by, status, total_score, performance_level, submitted_date, endorsed_date, approved_date, staff_comments, supervisor_comments, manager_comments) VALUES
(3, 3, 1, '2025-10-01', '2025-12-31', 4, 3, 2, 'Approved', 92.00, 'Excellent', '2026-01-10 09:00:00', '2026-01-12 14:00:00', '2026-01-15 11:00:00', 'Exceptional leadership.', 'Excellent performance.', 'Approved.');

INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
(3, 1, 5, 30.00),
(3, 2, 4, 20.00),
(3, 3, 5, 25.00),
(3, 4, 4, 16.00);

-- ============================================
-- 10. Audit Logs
-- ============================================
INSERT INTO audit_logs (user_id, action_type, entity_type, entity_id, details, ip_address) VALUES
(1, 'LOGIN', 'User', 1, 'Admin logged in successfully.', '127.0.0.1'),
(2, 'CREATE', 'Template', 1, 'Created evaluation template: Standard Performance Evaluation', '127.0.0.1');*/