-- call_schema_patch.sql
-- Run this if upgrading from an older version that does not yet have the call_requests table.
-- Safe to run multiple times (uses IF NOT EXISTS).

USE clinic;

CREATE TABLE IF NOT EXISTS call_requests (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    patient_id  INT          NOT NULL,
    doctor_id   INT          NOT NULL,
    room_url    VARCHAR(300) NOT NULL,
    status      ENUM('ringing','answered','missed','declined') DEFAULT 'ringing',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (doctor_id)  REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Indexes for fast polling queries
CREATE INDEX IF NOT EXISTS idx_calls_doctor_status ON call_requests(doctor_id, status);
CREATE INDEX IF NOT EXISTS idx_calls_patient       ON call_requests(patient_id, created_at);
