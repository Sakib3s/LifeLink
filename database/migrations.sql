-- ============================================================================
--  LifeLink — schema
--  MySQL 8 / MariaDB 11.x · InnoDB · utf8mb4
--  Idempotent: safe to run repeatedly (CREATE TABLE IF NOT EXISTS).
--  Run:  ddev import-db  ·  or:  mysql <db> < database/migrations.sql
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
--  users — one row per account; role-specific data lives in the profile tables
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(120)    NOT NULL,
    email         VARCHAR(160)    NOT NULL,
    phone         VARCHAR(30)         NULL,
    password_hash VARCHAR(255)    NOT NULL,
    role          ENUM('donor','patient','blood_bank','admin') NOT NULL,
    status        ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_phone (phone),
    KEY idx_users_role (role),
    KEY idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  donors
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS donors (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id             BIGINT UNSIGNED NOT NULL,
    blood_group         ENUM('O-','O+','A-','A+','B-','B+','AB-','AB+') NOT NULL,
    gender              ENUM('male','female','other') NOT NULL,
    date_of_birth       DATE            NOT NULL,
    weight_kg           DECIMAL(5,2)        NULL,
    address             VARCHAR(255)    NOT NULL,
    city                VARCHAR(120)    NOT NULL,
    latitude            DECIMAL(10,7)       NULL,
    longitude           DECIMAL(10,7)       NULL,
    availability_status ENUM('available','unavailable') NOT NULL DEFAULT 'available',
    is_verified         TINYINT(1)      NOT NULL DEFAULT 0,
    verified_by         BIGINT UNSIGNED     NULL,
    verified_at         TIMESTAMP           NULL,
    last_donation_date  DATE                NULL,
    total_donations     INT UNSIGNED    NOT NULL DEFAULT 0,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_donors_user (user_id),
    KEY idx_donors_blood_group (blood_group),
    KEY idx_donors_city (city),
    KEY idx_donors_availability (availability_status),
    KEY idx_donors_verified (is_verified),
    KEY idx_donors_verified_by (verified_by),
    KEY idx_donors_match_filter (blood_group, availability_status, is_verified),
    CONSTRAINT fk_donors_user     FOREIGN KEY (user_id)     REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_donors_verifier FOREIGN KEY (verified_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  patients
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS patients (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    address    VARCHAR(255)    NOT NULL,
    city       VARCHAR(120)    NOT NULL,
    latitude   DECIMAL(10,7)       NULL,
    longitude  DECIMAL(10,7)       NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_patients_user (user_id),
    KEY idx_patients_city (city),
    CONSTRAINT fk_patients_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  blood_banks
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS blood_banks (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    bank_name   VARCHAR(160)    NOT NULL,
    license_no  VARCHAR(80)         NULL,
    address     VARCHAR(255)    NOT NULL,
    city        VARCHAR(120)    NOT NULL,
    latitude    DECIMAL(10,7)       NULL,
    longitude   DECIMAL(10,7)       NULL,
    phone       VARCHAR(30)     NOT NULL,
    is_verified TINYINT(1)      NOT NULL DEFAULT 0,
    verified_by BIGINT UNSIGNED     NULL,
    verified_at TIMESTAMP           NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blood_banks_user (user_id),
    KEY idx_blood_banks_city (city),
    KEY idx_blood_banks_verified (is_verified),
    KEY idx_blood_banks_verified_by (verified_by),
    CONSTRAINT fk_blood_banks_user     FOREIGN KEY (user_id)     REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_blood_banks_verifier FOREIGN KEY (verified_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  blood_requests
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS blood_requests (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    patient_id     BIGINT UNSIGNED NOT NULL,
    blood_group    ENUM('O-','O+','A-','A+','B-','B+','AB-','AB+') NOT NULL,
    units_required INT UNSIGNED    NOT NULL DEFAULT 1,
    urgency        ENUM('critical','high','normal') NOT NULL DEFAULT 'normal',
    hospital_name  VARCHAR(180)    NOT NULL,
    address        VARCHAR(255)    NOT NULL,
    city           VARCHAR(120)    NOT NULL,
    latitude       DECIMAL(10,7)       NULL,
    longitude      DECIMAL(10,7)       NULL,
    needed_by      DATETIME           NULL,
    status         ENUM('open','matched','fulfilled','cancelled','expired') NOT NULL DEFAULT 'open',
    notes          TEXT               NULL,
    created_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_requests_patient (patient_id),
    KEY idx_requests_status (status),
    KEY idx_requests_blood_group (blood_group),
    KEY idx_requests_city (city),
    KEY idx_requests_urgency (urgency),
    KEY idx_requests_status_created (status, created_at),
    CONSTRAINT fk_requests_patient FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  matches — a compatible donor surfaced for a request
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS matches (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    blood_request_id BIGINT UNSIGNED NOT NULL,
    donor_id         BIGINT UNSIGNED NOT NULL,
    distance_km      DECIMAL(7,2)       NULL,
    status           ENUM('pending','accepted','declined','expired') NOT NULL DEFAULT 'pending',
    notified_at      TIMESTAMP          NULL,
    responded_at     TIMESTAMP          NULL,
    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_matches_request_donor (blood_request_id, donor_id),
    KEY idx_matches_donor (donor_id),
    KEY idx_matches_status (status),
    CONSTRAINT fk_matches_request FOREIGN KEY (blood_request_id) REFERENCES blood_requests (id) ON DELETE CASCADE,
    CONSTRAINT fk_matches_donor   FOREIGN KEY (donor_id)         REFERENCES donors (id)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  donations — a recorded blood donation event
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS donations (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    donor_id         BIGINT UNSIGNED NOT NULL,
    blood_bank_id    BIGINT UNSIGNED     NULL,
    blood_request_id BIGINT UNSIGNED     NULL,
    blood_group      ENUM('O-','O+','A-','A+','B-','B+','AB-','AB+') NOT NULL,
    units            INT UNSIGNED    NOT NULL DEFAULT 1,
    donation_date    DATE            NOT NULL,
    notes            TEXT               NULL,
    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_donations_donor (donor_id),
    KEY idx_donations_bank (blood_bank_id),
    KEY idx_donations_request (blood_request_id),
    KEY idx_donations_date (donation_date),
    CONSTRAINT fk_donations_donor   FOREIGN KEY (donor_id)         REFERENCES donors (id)         ON DELETE CASCADE,
    CONSTRAINT fk_donations_bank    FOREIGN KEY (blood_bank_id)    REFERENCES blood_banks (id)    ON DELETE SET NULL,
    CONSTRAINT fk_donations_request FOREIGN KEY (blood_request_id) REFERENCES blood_requests (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  inventory — units on hand per blood bank per group
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inventory (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    blood_bank_id   BIGINT UNSIGNED NOT NULL,
    blood_group     ENUM('O-','O+','A-','A+','B-','B+','AB-','AB+') NOT NULL,
    units_available INT UNSIGNED    NOT NULL DEFAULT 0,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_inventory_bank_group (blood_bank_id, blood_group),
    KEY idx_inventory_blood_group (blood_group),
    CONSTRAINT fk_inventory_bank FOREIGN KEY (blood_bank_id) REFERENCES blood_banks (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  notifications
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      BIGINT UNSIGNED NOT NULL,
    type         VARCHAR(60)     NOT NULL,
    title        VARCHAR(160)    NOT NULL,
    message      VARCHAR(500)    NOT NULL,
    related_type VARCHAR(60)         NULL,
    related_id   BIGINT UNSIGNED     NULL,
    is_read      TINYINT(1)      NOT NULL DEFAULT 0,
    created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notifications_user_read (user_id, is_read),
    KEY idx_notifications_created (created_at),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  eligibility_checks — stored result of the donor self-assessment checklist
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS eligibility_checks (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    donor_id       BIGINT UNSIGNED NOT NULL,
    checklist_json JSON            NOT NULL,
    result         ENUM('eligible','not_eligible','needs_review') NOT NULL,
    checked_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_eligibility_donor (donor_id),
    KEY idx_eligibility_result (result),
    CONSTRAINT fk_eligibility_donor FOREIGN KEY (donor_id) REFERENCES donors (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  audit_logs — admin/security action trail
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED     NULL,
    action      VARCHAR(80)     NOT NULL,
    target_type VARCHAR(60)         NULL,
    target_id   BIGINT UNSIGNED     NULL,
    details     TEXT               NULL,
    ip_address  VARCHAR(45)        NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_user (user_id),
    KEY idx_audit_action (action),
    KEY idx_audit_target (target_type, target_id),
    KEY idx_audit_created (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  settings — admin-editable key/value system configuration
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_key   VARCHAR(80)  NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Baseline settings (INSERT IGNORE keeps this idempotent).
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('match_default_radius_km',      '25'),
    ('match_max_donors_per_request', '25'),
    ('urgency_critical_hours',       '6'),
    ('urgency_high_hours',           '24'),
    ('request_auto_expire_hours',    '72');

-- ----------------------------------------------------------------------------
--  rate_limits — generic fixed-window counter (login/register throttling)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rate_limits (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    bucket       VARCHAR(191)    NOT NULL,
    hits         INT UNSIGNED    NOT NULL DEFAULT 0,
    window_start TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rate_limits_bucket (bucket)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
