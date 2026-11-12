CREATE DATABASE IF NOT EXISTS simple_crud
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE simple_crud;

-- Drop in child-first order so the script can be re-run safely.
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS teachers;

CREATE TABLE teachers (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  department VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE students (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  course     VARCHAR(100) NOT NULL,
  teacher_id INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_teacher
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO teachers (name, email, department) VALUES
  ('Prof. Ana Reyes',  'ana.reyes@example.com',  'Information Technology'),
  ('Prof. Ben Lim',    'ben.lim@example.com',    'Computer Science');

INSERT INTO students (name, email, course, teacher_id) VALUES
  ('Juan Dela Cruz', 'juan@example.com',  'BSIT', 1),
  ('Maria Santos',   'maria@example.com', 'BSCS', 2);
