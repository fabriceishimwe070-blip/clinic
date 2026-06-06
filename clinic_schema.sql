

CREATE DATABASE IF NOT EXISTS clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clinic;




CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    Fname      VARCHAR(120) NOT NULL,
    email      VARCHAR(180) NOT NULL UNIQUE,
    phon       VARCHAR(30)  NOT NULL,
    country    VARCHAR(100) DEFAULT '',
    password   VARCHAR(255) NOT NULL DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


 

CREATE TABLE IF NOT EXISTS admins (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(80)  NOT NULL UNIQUE,
    email      VARCHAR(180) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS divisions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL UNIQUE,
    icon        VARCHAR(20)  DEFAULT '🏥',
    description TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS doctors (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(150) NOT NULL,
    email        VARCHAR(180) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    specialty    VARCHAR(120) DEFAULT '',
    phone        VARCHAR(30)  DEFAULT '',
    bio          TEXT,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS doctor_divisions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id   INT NOT NULL,
    division_id INT NOT NULL,
    UNIQUE KEY uq_dd (doctor_id, division_id),
    FOREIGN KEY (doctor_id)   REFERENCES doctors(id)   ON DELETE CASCADE,
    FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS appointments (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    patient_id   INT NOT NULL,
    doctor_id    INT NOT NULL,
    division_id  INT NOT NULL,
    appt_date    DATE NOT NULL,
    appt_time    TIME NOT NULL,
    message      TEXT,
    status       ENUM('pending','accepted','rejected','completed') DEFAULT 'pending',
    doctor_note  TEXT,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id)  REFERENCES users(id)     ON DELETE CASCADE,
    FOREIGN KEY (doctor_id)   REFERENCES doctors(id)   ON DELETE CASCADE,
    FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id  INT NOT NULL,
    appt_id    INT NOT NULL,
    is_read    TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)      ON DELETE CASCADE,
    FOREIGN KEY (appt_id)   REFERENCES appointments(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ── Video call requests (patient → doctor) ──────────────────────────────
CREATE TABLE IF NOT EXISTS call_requests (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    patient_id  INT          NOT NULL,
    doctor_id   INT          NOT NULL,
    room_url    VARCHAR(300) NOT NULL,          -- full Jitsi URL
    status      ENUM('ringing','answered','missed','declined') DEFAULT 'ringing',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (doctor_id)  REFERENCES doctors(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO admins (username, email, password)
VALUES ('admin', 'admin@medicare.com', '$2y$12$k4L9z2qP7mN8xR3vT6wYuO.f5j1eI0cHbA9dG2nK8pS7tU4mW6yXi');

INSERT IGNORE INTO divisions (name, icon, description) VALUES
('Cardiology',     '❤️',  'Heart & vascular diseases, hypertension, cardiac rehabilitation.'),
('Neurology',      '🧠',  'Stroke, migraine, epilepsy, movement disorders.'),
('Orthopedics',    '🦴',  'Bone, joint, spine issues, sports injuries.'),
('Pulmonology',    '🫁',  'Asthma, COPD, respiratory infections, sleep apnea.'),
('General Medicine','🏥', 'Primary care, fever, diabetes, geriatrics.'),
('Gynecology',     '👶',  'Women''s health, prenatal care, fertility.'),
('Dental',         '🦷',  'Teeth, gums, oral surgery, orthodontics.'),
('Pediatrics',     '🧒',  'Child health, vaccinations, developmental care.');
