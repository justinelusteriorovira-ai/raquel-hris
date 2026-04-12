SET FOREIGN_KEY_CHECKS = 0;
USE raquel_hris;

-- ============================================
-- 1. EVALUATION TEMPLATES
-- ============================================
DELETE FROM `evaluation_templates`; ALTER TABLE `evaluation_templates` AUTO_INCREMENT = 1;
INSERT INTO `evaluation_templates` (`template_id`, `template_name`, `description`, `target_position`, `evaluation_type`, `kra_weight`, `behavior_weight`, `form_code`, `status`, `created_by`) VALUES 
(1, 'Annual Performance Review (Rank & File)', 'Standard annual performance evaluation for all non-managerial staff.', 'All', 'Annual', 80.00, 20.00, 'HRD Form-013.01', 'Active', 2);

-- ============================================
-- 2. EVALUATION CRITERIA
-- ============================================
DELETE FROM `evaluation_criteria`; ALTER TABLE `evaluation_criteria` AUTO_INCREMENT = 1;
-- KRA Section (80%)
INSERT INTO `evaluation_criteria` (`template_id`, `section`, `criterion_name`, `description`, `weight`, `scoring_method`, `sort_order`) VALUES 
(1, 'KRA', 'Task Efficiency', 'Completes assigned tasks within designated timelines and quality standards.', 10.00, 'Scale_1_4', 1),
(1, 'KRA', 'Accuracy & Quality', 'Maintains high level of accuracy in documentation and transactions.', 15.00, 'Scale_1_4', 2),
(1, 'KRA', 'Punctuality & Attendance', 'Meets company standards for attendance and reporting on time.', 10.00, 'Scale_1_4', 3),
(1, 'KRA', 'Compliance', 'Adheres to company policies, SOPs, and government regulations.', 15.00, 'Scale_1_4', 4),
(1, 'KRA', 'Client Experience', 'Provides service beyond expectations to internal and external clients.', 15.00, 'Scale_1_4', 5),
(1, 'KRA', 'Resource Management', 'Effectively uses and maintains company assets and supplies.', 15.00, 'Scale_1_4', 6);

-- Behavior Section (20%)
INSERT INTO `evaluation_criteria` (`template_id`, `section`, `criterion_name`, `description`, `weight`, `scoring_method`, `sort_order`) VALUES 
(1, 'Behavior', 'Positive Attitude', 'Displays a positive and professional attitude at work.', 2.50, 'Scale_1_4', 7),
(1, 'Behavior', 'Respect & Ethics', 'Shows respect to colleagues and maintains ethical standards.', 2.50, 'Scale_1_4', 8),
(1, 'Behavior', 'Accountability', 'Takes responsibility for actions and job outcomes.', 2.50, 'Scale_1_4', 9),
(1, 'Behavior', 'Teamwork', 'Collaborates effectively with department members.', 2.50, 'Scale_1_4', 10),
(1, 'Behavior', 'Integrity', 'Demonstrates honesty and uprightness in all dealings.', 2.50, 'Scale_1_4', 11),
(1, 'Behavior', 'Continuous Improvement', 'Actively seeks ways to improve skills and processes.', 2.50, 'Scale_1_4', 12),
(1, 'Behavior', 'Commitment', 'Shows dedication to the tasks and company goals.', 2.50, 'Scale_1_4', 13),
(1, 'Behavior', 'Communication', 'Communicates clearly and professionally with stakeholders.', 2.50, 'Scale_1_4', 14);

-- ============================================
-- 3. EVALUATIONS & SCORES
-- ============================================
DELETE FROM `evaluations`; ALTER TABLE `evaluations` AUTO_INCREMENT = 1;
DELETE FROM `evaluation_scores`; ALTER TABLE `evaluation_scores` AUTO_INCREMENT = 1;

-- Generating Evaluations for all 260 Employees
-- Using a temporary procedure to handle calculation logic in SQL
DROP PROCEDURE IF EXISTS GeneratePerformanceData;
DELIMITER //
CREATE PROCEDURE GeneratePerformanceData()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE emp_id INT;
    DECLARE cur CURSOR FOR SELECT employee_id FROM employees;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO emp_id;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Create Evaluation Record
        -- Logic: Most are Meets/Exceeds, some are Outstanding, few are Needs Improvement
        -- Use emp_id % behavior to vary results
        SET @total_kra = 0;
        SET @total_behavior = 0;
        
        -- Insert Evaluation Base
        INSERT INTO `evaluations` (`employee_id`, `template_id`, `evaluation_type`, `evaluation_period_start`, `evaluation_period_end`, `submitted_by`, `endorsed_by`, `approved_by`, `status`, `submitted_date`, `approved_date`)
        VALUES (emp_id, 1, 'Annual', '2025-01-01', '2025-12-31', 4, 3, 2, 'Approved', DATE_SUB(NOW(), INTERVAL (emp_id % 30) DAY), DATE_SUB(NOW(), INTERVAL (emp_id % 20) DAY));
        
        SET @last_eval_id = LAST_INSERT_ID();

        -- Insert KRA Scores (Weights: 10, 15, 10, 15, 15, 15)
        -- Score 1-4
        -- Bias Score: 
        -- emp_id % 10 = 0 -> Outstanding (3.7 - 4.0)
        -- emp_id % 7 = 0 -> Exceeds (2.8 - 3.5)
        -- emp_id % 20 = 0 -> Needs Improvement (1.5 - 1.9)
        -- others -> Meets (2.1 - 2.5)

        SET @base_score = 2.4;
        IF emp_id % 15 = 0 THEN SET @base_score = 3.8;
        ELSEIF emp_id % 8 = 0 THEN SET @base_score = 3.2;
        ELSEIF emp_id % 25 = 0 THEN SET @base_score = 1.7;
        END IF;

        -- Score 1: Task Efficiency (10%)
        SET @s1 = @base_score + (RAND() * 0.4 - 0.2); IF @s1 > 4 THEN SET @s1 = 4; END IF;
        INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES (@last_eval_id, 1, @s1, @s1 * 0.10);
        
        -- Score 2: Accuracy (15%)
        SET @s2 = @base_score + (RAND() * 0.4 - 0.2); IF @s2 > 4 THEN SET @s2 = 4; END IF;
        INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES (@last_eval_id, 2, @s2, @s2 * 0.15);
        
        -- Score 3: Punctuality (10%)
        SET @s3 = @base_score + (RAND() * 0.4 - 0.2); IF @s3 > 4 THEN SET @s3 = 4; END IF;
        INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES (@last_eval_id, 3, @s3, @s3 * 0.10);
        
        -- Score 4: Compliance (15%)
        SET @s4 = @base_score + (RAND() * 0.4 - 0.2); IF @s4 > 4 THEN SET @s4 = 4; END IF;
        INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES (@last_eval_id, 4, @s4, @s4 * 0.15);
        
        -- Score 5: Client Exp (15%)
        SET @s5 = @base_score + (RAND() * 0.4 - 0.2); IF @s5 > 4 THEN SET @s5 = 4; END IF;
        INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES (@last_eval_id, 5, @s5, @s5 * 0.15);
        
        -- Score 6: Resource Mgmt (15%)
        SET @s6 = @base_score + (RAND() * 0.4 - 0.2); IF @s6 > 4 THEN SET @s6 = 4; END IF;
        INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES (@last_eval_id, 6, @s6, @s6 * 0.15);

        -- Behavior Scores (8 criteria, 2.5% each = 20% total)
        SET @bh_total = 0;
        SET @i = 7;
        WHILE @i <= 14 DO
            SET @sb = @base_score + (RAND() * 0.4 - 0.2); IF @sb > 4 THEN SET @sb = 4; END IF;
            INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES (@last_eval_id, @i, @sb, @sb * 0.025);
            SET @bh_total = @bh_total + @sb;
            SET @i = @i + 1;
        END WHILE;

        -- Update Evaluation subtotals and final score
        SELECT SUM(weighted_score) INTO @kra_sub FROM evaluation_scores WHERE evaluation_id = @last_eval_id AND criterion_id <= 6;
        SELECT SUM(weighted_score) INTO @bh_sub FROM evaluation_scores WHERE evaluation_id = @last_eval_id AND criterion_id >= 7;
        SET @final_score = @kra_sub + @bh_sub;
        SET @bh_avg = @bh_total / 8;

        SET @perf_level = 'Meets Expectations';
        IF @final_score >= 3.60 THEN SET @perf_level = 'Outstanding';
        ELSEIF @final_score >= 2.60 THEN SET @perf_level = 'Exceeds Expectations';
        ELSEIF @final_score < 2.00 THEN SET @perf_level = 'Needs Improvement';
        END IF;

        UPDATE evaluations SET 
            kra_subtotal = @kra_sub, 
            behavior_average = @bh_avg, 
            total_score = (@final_score / 4) * 100, -- Convert to percentage for UI consistency
            performance_level = @perf_level
        WHERE evaluation_id = @last_eval_id;

        -- Career Movement for Outstanding Performers
        IF @perf_level = 'Outstanding' THEN
            SELECT job_title, branch_id INTO @old_job, @old_branch FROM employees WHERE employee_id = emp_id;
            INSERT INTO career_movements (employee_id, movement_type, previous_position, new_position, previous_branch_id, effective_date, reason, logged_by, approved_by, approval_status, is_applied)
            VALUES (emp_id, 'Promotion', @old_job, CONCAT(REPLACE(@old_job, 'Associate', 'Specialist'), ' (Promoted)'), @old_branch, DATE_ADD(NOW(), INTERVAL 7 DAY), 'Exceptional performance in 2025 Annual Review.', 2, 2, 'Approved', 0);
        END IF;

    END LOOP;
    CLOSE cur;
END //
DELIMITER ;

CALL GeneratePerformanceData();
DROP PROCEDURE IF EXISTS GeneratePerformanceData;

-- ============================================
-- 4. APPLY MOVEMENTS (where applicable)
-- ============================================
-- Update those that should already be specialist due to hiring logic if we want, 
-- but we'll let the system handle "is_applied" logic later.

SET FOREIGN_KEY_CHECKS = 1;
