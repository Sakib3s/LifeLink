-- ============================================================================
--  LifeLink — demo seed data
--  Re-runnable: it removes any previous *@lifelink.test accounts first
--  (FK ON DELETE CASCADE clears the dependent rows), then re-inserts.
--
--  Every demo account password is:  Password123!
--  Run:  mysql <db> < database/seed.sql   ·  or:  ddev mysql < database/seed.sql
-- ============================================================================

SET NAMES utf8mb4;

SET @pw := '$2y$12$CNVL/R7Ycg./Ay3JYMvruOhuZuEPjoUWthH3WLeW4bo.M.LiP0Azm';

-- Wipe previous demo data (cascades to donors/patients/blood_banks/requests/…)
DELETE FROM users WHERE email LIKE '%@lifelink.test';

-- ── Accounts ────────────────────────────────────────────────────────────────
INSERT INTO users (name, email, phone, password_hash, role, status) VALUES
    ('LifeLink Admin',        'admin@lifelink.test',          '+8801700000001', @pw, 'admin',      'active'),
    ('Rahim Uddin',           'donor.o.neg@lifelink.test',    '+8801700000010', @pw, 'donor',      'active'),
    ('Karim Chowdhury',       'donor.a.pos@lifelink.test',    '+8801700000011', @pw, 'donor',      'active'),
    ('Nusrat Jahan',          'donor.b.pos@lifelink.test',    '+8801700000012', @pw, 'donor',      'active'),
    ('Tanvir Ahmed',          'donor.ab.pos@lifelink.test',   '+8801700000013', @pw, 'donor',      'active'),
    ('Amina Begum',           'patient.amina@lifelink.test',  '+8801700000020', @pw, 'patient',    'active'),
    ('Shahidul Islam',        'patient.shahid@lifelink.test', '+8801700000021', @pw, 'patient',    'active'),
    ('City Central Blood Bank','citycentral@lifelink.test',   '+8802000000030', @pw, 'blood_bank', 'active'),
    ('Green Life Blood Bank',  'greenlife@lifelink.test',     '+8802000000031', @pw, 'blood_bank', 'active');

-- ── Donor profiles ─────────────────────────────────────────────────────────
INSERT INTO donors
    (user_id, blood_group, gender, date_of_birth, weight_kg, address, city, latitude, longitude,
     availability_status, is_verified, verified_by, verified_at, last_donation_date, total_donations)
VALUES
    ((SELECT id FROM users WHERE email='donor.o.neg@lifelink.test'),
     'O-', 'male', '1992-04-11', 72.5, '14 Mirpur Road', 'Dhaka', 23.7509000, 90.3653000,
     'available', 1, (SELECT id FROM users WHERE email='admin@lifelink.test'), NOW(), '2025-05-20', 6),

    ((SELECT id FROM users WHERE email='donor.a.pos@lifelink.test'),
     'A+', 'male', '1988-11-02', 80.0, '5 Gulshan Ave', 'Dhaka', 23.7925000, 90.4078000,
     'available', 1, (SELECT id FROM users WHERE email='admin@lifelink.test'), NOW(), '2025-03-02', 3),

    ((SELECT id FROM users WHERE email='donor.b.pos@lifelink.test'),
     'B+', 'female', '1995-07-19', 58.0, '22 Zindabazar', 'Sylhet', 24.8949000, 91.8687000,
     'available', 1, (SELECT id FROM users WHERE email='admin@lifelink.test'), NOW(), NULL, 1),

    ((SELECT id FROM users WHERE email='donor.ab.pos@lifelink.test'),
     'AB+', 'male', '1990-01-30', 68.0, '9 GEC Circle', 'Chattogram', 22.3600000, 91.8300000,
     'unavailable', 0, NULL, NULL, NULL, 0);

-- ── Patient profiles ──────────────────────────────────────────────────────
INSERT INTO patients (user_id, address, city, latitude, longitude) VALUES
    ((SELECT id FROM users WHERE email='patient.amina@lifelink.test'),  '31 Green Road', 'Dhaka',  23.7480000, 90.3850000),
    ((SELECT id FROM users WHERE email='patient.shahid@lifelink.test'), '7 Ambarkhana',  'Sylhet', 24.9040000, 91.8730000);

-- ── Blood bank profiles ───────────────────────────────────────────────────
INSERT INTO blood_banks
    (user_id, bank_name, license_no, address, city, latitude, longitude, phone, is_verified, verified_by, verified_at)
VALUES
    ((SELECT id FROM users WHERE email='citycentral@lifelink.test'),
     'City Central Blood Bank', 'BB-DHK-1042', '2 Topkhana Road', 'Dhaka', 23.7290000, 90.4110000,
     '+8802000000030', 1, (SELECT id FROM users WHERE email='admin@lifelink.test'), NOW()),

    ((SELECT id FROM users WHERE email='greenlife@lifelink.test'),
     'Green Life Blood Bank', NULL, '18 Panchlaish', 'Chattogram', 22.3610000, 91.8210000,
     '+8802000000031', 0, NULL, NULL);

-- ── Inventory (verified bank) ─────────────────────────────────────────────
INSERT INTO inventory (blood_bank_id, blood_group, units_available)
SELECT b.id, g.grp, g.units
FROM blood_banks b
JOIN (
    SELECT 'O-' AS grp, 4 AS units UNION ALL
    SELECT 'O+', 12 UNION ALL
    SELECT 'A+', 9  UNION ALL
    SELECT 'A-', 2  UNION ALL
    SELECT 'B+', 7  UNION ALL
    SELECT 'B-', 1  UNION ALL
    SELECT 'AB+', 3 UNION ALL
    SELECT 'AB-', 0
) g
WHERE b.user_id = (SELECT id FROM users WHERE email='citycentral@lifelink.test');

-- ── Sample emergency requests ────────────────────────────────────────────
INSERT INTO blood_requests
    (patient_id, blood_group, units_required, urgency, hospital_name, address, city, latitude, longitude, needed_by, status, notes)
VALUES
    ((SELECT p.id FROM patients p JOIN users u ON u.id=p.user_id WHERE u.email='patient.amina@lifelink.test'),
     'O+', 2, 'critical', 'Dhaka Medical College Hospital', 'Bakshibazar', 'Dhaka', 23.7250000, 90.3970000,
     DATE_ADD(NOW(), INTERVAL 6 HOUR), 'open', 'Post-partum haemorrhage, need packed red cells urgently.'),

    ((SELECT p.id FROM patients p JOIN users u ON u.id=p.user_id WHERE u.email='patient.shahid@lifelink.test'),
     'B+', 1, 'high', 'Sylhet MAG Osmani Medical College', 'Kazolshah', 'Sylhet', 24.8990000, 91.8670000,
     DATE_ADD(NOW(), INTERVAL 24 HOUR), 'open', 'Scheduled surgery tomorrow morning.');

-- ── One historical donation ─────────────────────────────────────────────
INSERT INTO donations (donor_id, blood_bank_id, blood_request_id, blood_group, units, donation_date, notes)
SELECT
    (SELECT d.id FROM donors d JOIN users u ON u.id=d.user_id WHERE u.email='donor.o.neg@lifelink.test'),
    (SELECT b.id FROM blood_banks b JOIN users u ON u.id=b.user_id WHERE u.email='citycentral@lifelink.test'),
    NULL, 'O-', 1, '2025-05-20', 'Routine whole-blood donation.';
