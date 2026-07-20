-- ============================================
-- BASE DE DONNÉES MOBILE MONEY
-- Version finale (V1 + V2)
-- ============================================

-- ============================================
-- STRUCTURE DES TABLES
-- ============================================

-- 1. UTILISATEURS
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    email TEXT,
    role TEXT DEFAULT 'client',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. CLIENTS
CREATE TABLE IF NOT EXISTS clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    numero_telephone TEXT NOT NULL UNIQUE,
    nom TEXT,
    prenom TEXT,
    solde REAL DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut TEXT DEFAULT 'actif',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. PRÉFIXES OPÉRATEUR (V2 : ajout des champs est_autre_operateur et commission_pourcentage)
CREATE TABLE IF NOT EXISTS prefixes_operateur (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prefixe TEXT NOT NULL UNIQUE,
    description TEXT,
    est_autre_operateur INTEGER DEFAULT 0,
    commission_pourcentage DECIMAL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 4. TYPES D'OPÉRATIONS
CREATE TABLE IF NOT EXISTS types_operations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL UNIQUE,
    code TEXT NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 5. BARÈMES DE FRAIS
CREATE TABLE IF NOT EXISTS baremes_frais (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER NOT NULL,
    montant_min REAL NOT NULL,
    montant_max REAL NOT NULL,
    frais_fixe REAL DEFAULT 0,
    frais_pourcentage REAL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id) ON DELETE CASCADE,
    CHECK (montant_min <= montant_max),
    CHECK (frais_fixe >= 0 AND frais_pourcentage >= 0)
);

-- 6. TRANSACTIONS (V2 : ajout de frais_inclus, est_inter_operateur, destinataire_original)
CREATE TABLE IF NOT EXISTS transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL UNIQUE,
    type_operation_id INTEGER NOT NULL,
    client_id INTEGER NOT NULL,
    montant REAL NOT NULL,
    frais_appliques REAL DEFAULT 0,
    frais_inclus INTEGER DEFAULT 0,
    montant_total REAL NOT NULL,
    sens TEXT NOT NULL,
    statut TEXT DEFAULT 'effectuee',
    date_transaction DATETIME DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    destinataire_original TEXT,
    est_inter_operateur INTEGER DEFAULT 0,
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id),
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

-- 7. GAINS (V2 : ajout de est_inter_operateur)
CREATE TABLE IF NOT EXISTS gains (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER NOT NULL,
    est_inter_operateur INTEGER DEFAULT 0,
    montant_total_frais REAL NOT NULL,
    periode_debut DATETIME NOT NULL,
    periode_fin DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id)
);

-- 8. ENVOIS MULTIPLES (V2)
CREATE TABLE IF NOT EXISTS envois_multiples (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_reference TEXT NOT NULL,
    montant_total REAL NOT NULL,
    nombre_destinataires INTEGER NOT NULL,
    client_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- ============================================
-- INDEX
-- ============================================

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_clients_telephone ON clients(numero_telephone);
CREATE INDEX idx_clients_user ON clients(user_id);
CREATE INDEX idx_transactions_client ON transactions(client_id);
CREATE INDEX idx_transactions_type ON transactions(type_operation_id);
CREATE INDEX idx_transactions_date ON transactions(date_transaction);
CREATE INDEX idx_baremes_type ON baremes_frais(type_operation_id);

-- ============================================
-- TRIGGERS (pour SQLite)
-- ============================================

-- Vérifier le rôle des utilisateurs
CREATE TRIGGER check_user_role BEFORE INSERT ON users BEGIN SELECT CASE WHEN NEW.role NOT IN ('admin', 'client') THEN RAISE(ABORT, 'Role invalide') END; END;

-- Vérifier le sens des transactions
CREATE TRIGGER check_transaction_sens BEFORE INSERT ON transactions BEGIN SELECT CASE WHEN NEW.sens NOT IN ('debit', 'credit') THEN RAISE(ABORT, 'Sens invalide') END; END;

-- Vérifier le statut des transactions
CREATE TRIGGER check_transaction_statut BEFORE INSERT ON transactions BEGIN SELECT CASE WHEN NEW.statut NOT IN ('effectuee', 'annulee', 'en_attente') THEN RAISE(ABORT, 'Statut invalide') END; END;

-- ============================================
-- DONNÉES INITIALES
-- ============================================

-- 1. Admins
INSERT OR IGNORE INTO users (username, password, email, role) VALUES
('frank', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'frank@gmail.com', 'admin'),
('tahiry', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tahiry@gmail.com', 'admin');

-- 2. Clients (liés aux admins)
INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) VALUES
((SELECT id FROM users WHERE username = 'frank'), '0330000001', 'Frank', 'Admin', 0),
((SELECT id FROM users WHERE username = 'tahiry'), '0330000002', 'Tahiry', 'Admin', 0);

-- 3. Clients de test
INSERT OR IGNORE INTO users (username, password, email, role) VALUES
('0331234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jean@test.com', 'client'),
('0349876543', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'marie@test.com', 'client'),
('0371122334', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'paul@test.com', 'client'),
('0385566778', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sophie@test.com', 'client');

INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) VALUES
((SELECT id FROM users WHERE username = '0331234567'), '0331234567', 'Jean', 'Dupont', 50000),
((SELECT id FROM users WHERE username = '0349876543'), '0349876543', 'Marie', 'Martin', 30000),
((SELECT id FROM users WHERE username = '0371122334'), '0371122334', 'Paul', 'Dubois', 75000),
((SELECT id FROM users WHERE username = '0385566778'), '0385566778', 'Sophie', 'Lefevre', 120000);

-- 4. Préfixes opérateur (V2 : avec est_autre_operateur et commission_pourcentage)
INSERT OR IGNORE INTO prefixes_operateur (prefixe, description, est_autre_operateur, commission_pourcentage) VALUES
('033', 'Opérateur A (même réseau)', 0, 0),
('034', 'Opérateur B (même réseau)', 0, 0),
('037', 'Opérateur C (même réseau)', 0, 0),
('038', 'Opérateur D (même réseau)', 0, 0),
('032', 'Autre opérateur (Telma)', 1, 2.50),
('031', 'Autre opérateur (Orange)', 1, 3.00);

-- 5. Types d'opérations
INSERT OR IGNORE INTO types_operations (nom, code, description) VALUES
('dépôt', 'DEP', 'Dépôt sur compte'),
('retrait', 'RET', 'Retrait depuis compte'),
('transfert', 'TRANS', 'Transfert entre comptes');

-- 6. Barèmes de frais (pour retrait et transfert)
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
SELECT id, 1000001, 2000000, 3000 FROM types_operations WHERE code = 'RET';

INSERT OR IGNORE INTO baremes_frais (type_operation_id, montant_min, montant_max, frais_fixe)
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

-- 7. Client inter-opérateur (pour les tests V2)
INSERT OR IGNORE INTO users (username, password, email, role) VALUES
('0327654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inter@test.com', 'client');

INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) VALUES
((SELECT id FROM users WHERE username = '0327654321'), '0327654321', 'Inter', 'Opérateur', 10000);

-- 8. Transactions de test (V2 : avec est_inter_operateur, frais_inclus, destinataire_original)
INSERT OR IGNORE INTO transactions (reference, type_operation_id, client_id, montant, frais_appliques, frais_inclus, montant_total, sens, statut, date_transaction, description, destinataire_original, est_inter_operateur)
SELECT 'TXN-TEST-001', (SELECT id FROM types_operations WHERE code = 'DEP'), (SELECT id FROM clients WHERE numero_telephone = '0331234567'), 10000, 0, 0, 10000, 'credit', 'effectuee', datetime('now', '-2 days'), 'Dépôt test', NULL, 0;

INSERT OR IGNORE INTO transactions (reference, type_operation_id, client_id, montant, frais_appliques, frais_inclus, montant_total, sens, statut, date_transaction, description, destinataire_original, est_inter_operateur)
SELECT 'TXN-TEST-002', (SELECT id FROM types_operations WHERE code = 'RET'), (SELECT id FROM clients WHERE numero_telephone = '0331234567'), 5000, 50, 0, 5050, 'debit', 'effectuee', datetime('now', '-1 days'), 'Retrait test', NULL, 0;

INSERT OR IGNORE INTO transactions (reference, type_operation_id, client_id, montant, frais_appliques, frais_inclus, montant_total, sens, statut, date_transaction, description, destinataire_original, est_inter_operateur)
SELECT 'TXN-TEST-003', (SELECT id FROM types_operations WHERE code = 'TRANS'), (SELECT id FROM clients WHERE numero_telephone = '0331234567'), 2000, 100, 0, 2100, 'debit', 'effectuee', datetime('now'), 'Transfert inter-opérateur vers 0327654321', '0327654321', 1;

INSERT OR IGNORE INTO transactions (reference, type_operation_id, client_id, montant, frais_appliques, frais_inclus, montant_total, sens, statut, date_transaction, description, destinataire_original, est_inter_operateur)
SELECT 'TXN-TEST-004', (SELECT id FROM types_operations WHERE code = 'TRANS'), (SELECT id FROM clients WHERE numero_telephone = '0327654321'), 2000, 0, 0, 2000, 'credit', 'effectuee', datetime('now'), 'Réception de transfert inter-opérateur', NULL, 0;