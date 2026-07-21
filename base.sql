-- ============================================
-- BASE DE DONNÉES MOBILE MONEY
-- Version finale (V1 + V2 + V3)
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

-- 2. CLIENTS (avec épargne)
CREATE TABLE IF NOT EXISTS clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    numero_telephone TEXT NOT NULL UNIQUE,
    nom TEXT,
    prenom TEXT,
    solde REAL DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut TEXT DEFAULT 'actif',
    epargne_pourcentage DECIMAL(5,2) DEFAULT 0,
    solde_epargne DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. PRÉFIXES OPÉRATEUR
CREATE TABLE IF NOT EXISTS prefixes_operateur (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prefixe TEXT NOT NULL UNIQUE,
    description TEXT,
    est_autre_operateur INTEGER DEFAULT 0,
    commission_pourcentage DECIMAL(5,2) DEFAULT 0,
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

-- 6. TRANSACTIONS
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

-- 7. GAINS
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

-- 8. ENVOIS MULTIPLES (optionnel)
CREATE TABLE IF NOT EXISTS envois_multiples (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_reference TEXT NOT NULL,
    montant_total REAL NOT NULL,
    nombre_destinataires INTEGER NOT NULL,
    client_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- 9. PROMOTIONS
CREATE TABLE IF NOT EXISTS promotions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER NOT NULL,
    operateur_prefixe TEXT NOT NULL DEFAULT '032',
    reduction_pourcentage DECIMAL(5,2) NOT NULL CHECK (reduction_pourcentage >= 0 AND reduction_pourcentage <= 100),
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id) ON DELETE CASCADE
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
CREATE INDEX idx_promotions_dates ON promotions(date_debut, date_fin);
CREATE INDEX idx_promotions_type ON promotions(type_operation_id);

-- ============================================
-- TRIGGERS (SQLite)
-- ============================================

CREATE TRIGGER check_user_role BEFORE INSERT ON users BEGIN SELECT CASE WHEN NEW.role NOT IN ('admin', 'client') THEN RAISE(ABORT, 'Role invalide') END; END;
CREATE TRIGGER check_transaction_sens BEFORE INSERT ON transactions BEGIN SELECT CASE WHEN NEW.sens NOT IN ('debit', 'credit') THEN RAISE(ABORT, 'Sens invalide') END; END;
CREATE TRIGGER check_transaction_statut BEFORE INSERT ON transactions BEGIN SELECT CASE WHEN NEW.statut NOT IN ('effectuee', 'annulee', 'en_attente') THEN RAISE(ABORT, 'Statut invalide') END; END;

-- ============================================
-- DONNÉES INITIALES
-- ============================================

-- 1. Administrateurs (032...)
INSERT OR IGNORE INTO users (username, password, email, role) VALUES
('0320408683', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin1@mobilemoney.com', 'admin'),
('0320000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin2@mobilemoney.com', 'admin');

INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde, epargne_pourcentage, solde_epargne) VALUES
((SELECT id FROM users WHERE username = '0320408683'), '0320408683', 'Admin', 'Principal', 0, 0, 0),
((SELECT id FROM users WHERE username = '0320000001'), '0320000001', 'Admin', 'Second', 0, 0, 0);

-- 2. Clients de test (032)
INSERT OR IGNORE INTO users (username, password, email, role) VALUES
('0321234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jean@test.com', 'client'),
('0322345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'marie@test.com', 'client'),
('0323456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'paul@test.com', 'client'),
('0324567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sophie@test.com', 'client');

INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde, epargne_pourcentage, solde_epargne) VALUES
((SELECT id FROM users WHERE username = '0321234567'), '0321234567', 'Jean', 'Dupont', 50000, 10, 0),
((SELECT id FROM users WHERE username = '0322345678'), '0322345678', 'Marie', 'Martin', 30000, 5, 0),
((SELECT id FROM users WHERE username = '0323456789'), '0323456789', 'Paul', 'Dubois', 75000, 20, 0),
((SELECT id FROM users WHERE username = '0324567890'), '0324567890', 'Sophie', 'Lefevre', 120000, 15, 0);

-- 3. Préfixes opérateur (032 = réseau principal)
INSERT OR IGNORE INTO prefixes_operateur (prefixe, description, est_autre_operateur, commission_pourcentage) VALUES
('032', 'Réseau principal', 0, 0),
('031', 'Autre opérateur (Orange)', 1, 2.50),
('033', 'Autre opérateur (A)', 1, 2.50),
('034', 'Autre opérateur (B)', 1, 2.50),
('037', 'Autre opérateur (C)', 1, 2.50),
('038', 'Autre opérateur (D)', 1, 2.50);

-- 4. Types d'opérations
INSERT OR IGNORE INTO types_operations (nom, code, description) VALUES
('dépôt', 'DEP', 'Dépôt sur compte'),
('retrait', 'RET', 'Retrait depuis compte'),
('transfert', 'TRANS', 'Transfert entre comptes');

-- 5. Barèmes de frais (pour retrait et transfert)
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

-- 6. Client inter-opérateur (031)
INSERT OR IGNORE INTO users (username, password, email, role) VALUES
('0311234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inter@test.com', 'client');

INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde, epargne_pourcentage, solde_epargne) VALUES
((SELECT id FROM users WHERE username = '0311234567'), '0311234567', 'Inter', 'Opérateur', 10000, 0, 0);

-- 7. Promotions (exemples)
INSERT OR IGNORE INTO promotions (type_operation_id, operateur_prefixe, reduction_pourcentage, date_debut, date_fin)
SELECT id, '032', 20, datetime('now', '-1 day'), datetime('now', '+7 days') FROM types_operations WHERE code = 'TRANS'
UNION ALL
SELECT id, '032', 50, datetime('now', '-10 days'), datetime('now', '-5 days') FROM types_operations WHERE code = 'TRANS';

-- 8. Transactions de test (exemples)
INSERT OR IGNORE INTO transactions (reference, type_operation_id, client_id, montant, frais_appliques, frais_inclus, montant_total, sens, statut, date_transaction, description, destinataire_original, est_inter_operateur)
SELECT 'TXN-TEST-001', (SELECT id FROM types_operations WHERE code = 'DEP'), (SELECT id FROM clients WHERE numero_telephone = '0321234567'), 15000, 0, 0, 15000, 'credit', 'effectuee', datetime('now', '-2 days'), 'Dépôt test', NULL, 0
UNION ALL
SELECT 'TXN-TEST-002', (SELECT id FROM types_operations WHERE code = 'RET'), (SELECT id FROM clients WHERE numero_telephone = '0321234567'), 5000, 50, 0, 5050, 'debit', 'effectuee', datetime('now', '-1 days'), 'Retrait test', NULL, 0
UNION ALL
SELECT 'TXN-TEST-003', (SELECT id FROM types_operations WHERE code = 'TRANS'), (SELECT id FROM clients WHERE numero_telephone = '0321234567'), 2500, 50, 0, 2550, 'debit', 'effectuee', datetime('now'), 'Transfert inter-opérateur vers 0311234567', '0311234567', 1
UNION ALL
SELECT 'TXN-TEST-004', (SELECT id FROM types_operations WHERE code = 'TRANS'), (SELECT id FROM clients WHERE numero_telephone = '0311234567'), 2500, 0, 0, 2500, 'credit', 'effectuee', datetime('now'), 'Réception de transfert inter-opérateur', NULL, 0;