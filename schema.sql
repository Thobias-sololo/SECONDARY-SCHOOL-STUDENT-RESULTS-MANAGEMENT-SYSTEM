CREATE DATABASE IF NOT EXISTS school_results;
USE school_results;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','teacher','student') NOT NULL,
    phone VARCHAR(40) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE streams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    admission_no VARCHAR(60) NOT NULL UNIQUE,
    first_name VARCHAR(80) NOT NULL,
    middle_name VARCHAR(80) NULL,
    last_name VARCHAR(80) NOT NULL,
    gender ENUM('Male','Female') NOT NULL,
    dob DATE NULL,
    form_id INT NOT NULL,
    stream_id INT NOT NULL,
    guardian_name VARCHAR(120) NULL,
    guardian_phone VARCHAR(40) NULL,
    address VARCHAR(255) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (form_id) REFERENCES forms(id),
    FOREIGN KEY (stream_id) REFERENCES streams(id)
);

CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    staff_no VARCHAR(60) NOT NULL UNIQUE,
    full_name VARCHAR(120) NOT NULL,
    gender ENUM('Male','Female') NOT NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(160) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    code VARCHAR(30) NOT NULL UNIQUE,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active'
);

CREATE TABLE teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    UNIQUE KEY unique_teacher_subject (teacher_id, subject_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE teacher_classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    form_id INT NOT NULL,
    stream_id INT NOT NULL,
    UNIQUE KEY unique_teacher_class (teacher_id, form_id, stream_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (form_id) REFERENCES forms(id),
    FOREIGN KEY (stream_id) REFERENCES streams(id)
);

CREATE TABLE terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year_label VARCHAR(20) NOT NULL UNIQUE,
    is_active TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grade VARCHAR(5) NOT NULL,
    min_mark DECIMAL(5,2) NOT NULL,
    max_mark DECIMAL(5,2) NOT NULL,
    remark VARCHAR(80) NOT NULL
);

CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NULL,
    term_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    marks DECIMAL(5,2) NOT NULL,
    status ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_result (student_id, subject_id, term_id, academic_year_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    FOREIGN KEY (term_id) REFERENCES terms(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NULL,
    role_target ENUM('all','admin','teacher','student') NOT NULL DEFAULT 'all',
    user_id INT NULL,
    title VARCHAR(160) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE notification_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_read (notification_id, user_id),
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password, role) VALUES
('System Admin', 'admin@school.test', 'admin123', 'admin');

INSERT INTO forms (name) VALUES ('Form 1'), ('Form 2'), ('Form 3'), ('Form 4');
INSERT INTO streams (name) VALUES ('A'), ('B'), ('C'), ('D');
INSERT INTO terms (name) VALUES ('Term 1'), ('Term 2'), ('Term 3'), ('Term 4');
INSERT INTO academic_years (year_label, is_active) VALUES ('2026', 1);

INSERT INTO subjects (name, code) VALUES
('Mathematics', 'MATH'),
('English', 'ENG'),
('Kiswahili', 'KIS'),
('Biology', 'BIO'),
('Chemistry', 'CHEM'),
('Physics', 'PHY'),
('Geography', 'GEO'),
('History', 'HIST'),
('Civics', 'CIV'),
('Bookkeeping', 'BKEEP'),
('Computer Studies', 'COMP');

INSERT INTO grades (grade, min_mark, max_mark, remark) VALUES
('A', 75, 100, 'Excellent'),
('B', 65, 74, 'Very Good'),
('C', 50, 64, 'Good'),
('D', 30, 49, 'Pass'),
('F', 0, 29, 'Fail');
