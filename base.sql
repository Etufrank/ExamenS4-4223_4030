-- ============================================
-- BASE DE DONNÉES MOBILE MONEY - VERSION V1+V2
-- ============================================

-- STRUCTURE DES TABLES (inchangée, identique à avant)
-- ... (copier le même CREATE TABLE que précédemment)

-- ============================================
-- DONNÉES INITIALES
-- ============================================

-- 1. Admins (032...)
INSERT OR IGNORE INTO users (username, password, email, role) VALUES
('0320408683', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin1@mobilemoney.com', 'admin'),
('0320000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin2@mobilemoney.com', 'admin');

INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) VALUES
((SELECT id FROM users WHERE username = '0320408683'), '0320408683', 'Admin', 'Principal', 0),
((SELECT id FROM users WHERE username = '0320000001'), '0320000001', 'Admin', 'Second', 0);

-- 2. Clients de test (032)
INSERT OR IGNORE INTO users (username, password, email, role) VALUES
('0321234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jean@test.com', 'client'),
('0322345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'marie@test.com', 'client'),
('0323456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'paul@test.com', 'client'),
('0324567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sophie@test.com', 'client'),
('0325678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lucas@test.com', 'client'),
('0326789012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'emma@test.com', 'client');

INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) VALUES
((SELECT id FROM users WHERE username = '0321234567'), '0321234567', 'Jean', 'Dupont', 50000),
((SELECT id FROM users WHERE username = '0322345678'), '0322345678', 'Marie', 'Martin', 30000),
((SELECT id FROM users WHERE username = '0323456789'), '0323456789', 'Paul', 'Dubois', 75000),
((SELECT id FROM users WHERE username = '0324567890'), '0324567890', 'Sophie', 'Lefevre', 120000),
((SELECT id FROM users WHERE username = '0325678901'), '0325678901', 'Lucas', 'Moreau', 20000),
((SELECT id FROM users WHERE username = '0326789012'), '0326789012', 'Emma', 'Petit', 60000);

-- 3. Préfixes opérateur (032 = réseau principal)
INSERT OR IGNORE INTO prefixes_operateur (prefixe, description, est_autre_operateur, commission_pourcentage) VALUES
('032', 'Réseau principal', 0, 0),
('031', 'Autre opérateur (Orange)', 1, 3.00),
('033', 'Autre opérateur (A)', 1, 2.50),
('034', 'Autre opérateur (B)', 1, 2.50),
('037', 'Autre opérateur (C)', 1, 2.50),
('038', 'Autre opérateur (D)', 1, 2.50);

-- 4. Types d'opérations
INSERT OR IGNORE INTO types_operations (nom, code, description) VALUES
('dépôt', 'DEP', 'Dépôt sur compte'),
('retrait', 'RET', 'Retrait depuis compte'),
('transfert', 'TRANS', 'Transfert entre comptes');

-- 5. Barèmes de frais (pour retrait ET transfert)
INSERT OR IGNORE INTO baremes_frais (type_operation_id, montant_min, montant_max, frais_fixe)
SELECT id, 100, 1000, 50 FROM types_operations WHERE code = 'RET'
UNION ALL
SELECT id, 1001, 5000, 50 FROM types_operations WHERE code = 'RET'
UNION ALL
SELECT id, 5001, 10000, 100 FROM types_operations WHERE code = 'RET'
UNION ALL
SELECT id, 10001, 25000, 200 FROM types_operations WHERE code = 'RET'
UNION ALL
SELECT id, 25001, 50000, 400 FROM types_operations WHERE code = 'RET'
UNION ALL
SELECT id, 50001, 100000, 800 FROM types_operations WHERE code = 'RET'
UNION ALL
SELECT id, 100001, 250000, 1500 FROM types_operations WHERE code = 'RET'
UNION ALL
SELECT id, 250001, 500000, 1500 FROM types_operations WHERE code = 'RET'
UNION ALL
SELECT id, 500001, 1000000, 2500 FROM types_operations WHERE code = 'RET'
UNION ALL
SELECT id, 1000001, 2000000, 3000 FROM types_operations WHERE code = 'RET'
UNION ALL
-- Pour transfert (mêmes plages)
SELECT id, 100, 1000, 50 FROM types_operations WHERE code = 'TRANS'
UNION ALL
SELECT id, 1001, 5000, 50 FROM types_operations WHERE code = 'TRANS'
UNION ALL
SELECT id, 5001, 10000, 100 FROM types_operations WHERE code = 'TRANS'
UNION ALL
SELECT id, 10001, 25000, 200 FROM types_operations WHERE code = 'TRANS'
UNION ALL
SELECT id, 25001, 50000, 400 FROM types_operations WHERE code = 'TRANS'
UNION ALL
SELECT id, 50001, 100000, 800 FROM types_operations WHERE code = 'TRANS'
UNION ALL
SELECT id, 100001, 250000, 1500 FROM types_operations WHERE code = 'TRANS'
UNION ALL
SELECT id, 250001, 500000, 1500 FROM types_operations WHERE code = 'TRANS'
UNION ALL
SELECT id, 500001, 1000000, 2500 FROM types_operations WHERE code = 'TRANS'
UNION ALL
SELECT id, 1000001, 2000000, 3000 FROM types_operations WHERE code = 'TRANS';

INSERT OR IGNORE INTO baremes_frais (type_operation_id, montant_min, montant_max, frais_fixe)
SELECT id, 0, 999999999, 0 FROM types_operations WHERE code = 'DEP';

-- 6. Client inter-opérateur (031)
INSERT OR IGNORE INTO users (username, password, email, role) VALUES
('0311234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inter@test.com', 'client');

INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) VALUES
((SELECT id FROM users WHERE username = '0311234567'), '0311234567', 'Inter', 'Opérateur', 10000);

-- 7. Transactions de test (exemples)
INSERT OR IGNORE INTO transactions (reference, type_operation_id, client_id, montant, frais_appliques, frais_inclus, montant_total, sens, statut, date_transaction, description, destinataire_original, est_inter_operateur)
SELECT 'TXN-TEST-001', (SELECT id FROM types_operations WHERE code = 'DEP'), (SELECT id FROM clients WHERE numero_telephone = '0321234567'), 15000, 0, 0, 15000, 'credit', 'effectuee', datetime('now', '-2 days'), 'Dépôt test', NULL, 0
UNION ALL
SELECT 'TXN-TEST-002', (SELECT id FROM types_operations WHERE code = 'RET'), (SELECT id FROM clients WHERE numero_telephone = '0321234567'), 5000, 50, 0, 5050, 'debit', 'effectuee', datetime('now', '-1 days'), 'Retrait test', NULL, 0
UNION ALL
SELECT 'TXN-TEST-003', (SELECT id FROM types_operations WHERE code = 'TRANS'), (SELECT id FROM clients WHERE numero_telephone = '0321234567'), 2500, 50, 0, 2550, 'debit', 'effectuee', datetime('now'), 'Transfert inter-opérateur vers 0311234567', '0311234567', 1
UNION ALL
SELECT 'TXN-TEST-004', (SELECT id FROM types_operations WHERE code = 'TRANS'), (SELECT id FROM clients WHERE numero_telephone = '0311234567'), 2500, 0, 0, 2500, 'credit', 'effectuee', datetime('now'), 'Réception de transfert inter-opérateur', NULL, 0;