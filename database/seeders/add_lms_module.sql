-- Add LMS Module and Submodules to the database

-- Insert LMS Module
INSERT INTO app_modules (name, `key`, icon, route, `order`, status, created_at, updated_at) 
VALUES ('Learning & Development', 'lms', 'AcademicCapIcon', NULL, 50, 1, NOW(), NOW());

-- Get the ID of the inserted module
SET @lms_module_id = LAST_INSERT_ID();

-- Insert LMS Submodules
INSERT INTO app_sub_modules (module_id, name, `key`, route, `order`, status, created_at, updated_at) VALUES
(@lms_module_id, 'My Courses', 'my_courses', 'lms.my-courses', 1, 1, NOW(), NOW()),
(@lms_module_id, 'Course Catalog', 'courses', 'hr.lms.index', 2, 1, NOW(), NOW()),
(@lms_module_id, 'Analytics', 'analytics', 'hr.lms.analytics.index', 3, 1, NOW(), NOW()),
(@lms_module_id, 'Question Bank', 'questions', 'hr.lms.questions.index', 4, 1, NOW(), NOW());

-- Display created records
SELECT 'LMS Module Created' AS Status;
SELECT * FROM app_modules WHERE `key` = 'lms';
SELECT * FROM app_sub_modules WHERE module_id = @lms_module_id;
