-- ============================================
-- STRUCTURE DES TABLES
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    email TEXT,
    role TEXT DEFAULT 'client',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

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

CREATE TABLE IF NOT EXISTS prefixes_operateur (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prefixe TEXT NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS types_operations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL UNIQUE,
    code TEXT NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

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

CREATE TABLE IF NOT EXISTS transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL UNIQUE,
    type_operation_id INTEGER NOT NULL,
    client_id INTEGER NOT NULL,
    montant REAL NOT NULL,
    frais_appliques REAL DEFAULT 0,
    montant_total REAL NOT NULL,
    sens TEXT NOT NULL,
    statut TEXT DEFAULT 'effectuee',
    date_transaction DATETIME DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id),
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

CREATE TABLE IF NOT EXISTS gains (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER NOT NULL,
    montant_total_frais REAL NOT NULL,
    periode_debut DATETIME NOT NULL,
    periode_fin DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id)
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
-- DONNÉES INITIALES
-- ============================================

-- Types d'opérations
INSERT OR IGNORE INTO types_operations (nom, code, description) VALUES
('dépôt', 'DEP', 'Dépôt sur compte'),
('retrait', 'RET', 'Retrait depuis compte'),
('transfert', 'TRANS', 'Transfert entre comptes');

-- Préfixes opérateur
INSERT OR IGNORE INTO prefixes_operateur (prefixe, description) VALUES
('033', 'Opérateur A'),
('037', 'Opérateur B');

-- Barèmes pour retrait (type_operation_id = 2, mais après insertion on aura les IDs réels)
-- On utilise les IDs dynamiques via des sous-requêtes pour éviter les soucis de numérotation
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

-- Barèmes pour transfert (mêmes barèmes)
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

-- Barème pour dépôt (frais à 0)
INSERT OR IGNORE INTO baremes_frais (type_operation_id, montant_min, montant_max, frais_fixe)
SELECT id, 0, 999999999, 0 FROM types_operations WHERE code = 'DEP';

-- Admin Frank (numéro 0330000001, mot de passe : frank123)
INSERT OR IGNORE INTO users (username, password, email, role) 
VALUES ('frank', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'frank@gmail.com', 'admin');
INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) 
SELECT id, '0330000001', 'Frank', 'Admin', 0 FROM users WHERE username = 'frank';

-- Admin Tahiry (numéro 0330000002, mot de passe : tahiry123)
INSERT OR IGNORE INTO users (username, password, email, role) 
VALUES ('tahiry', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tahiry@gmail.com', 'admin');
INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) 
SELECT id, '0330000002', 'Tahiry', 'Admin', 0 FROM users WHERE username = 'tahiry';

-- Client test (numéro 0331234567, mot de passe : 1234)
INSERT OR IGNORE INTO users (username, password, email, role) 
VALUES ('0331234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client@test.com', 'client');
INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) 
SELECT id, '0331234567', 'Jean', 'Dupont', 50000 FROM users WHERE username = '0331234567';

-- Autres clients de test
INSERT OR IGNORE INTO users (username, password, email, role) 
VALUES ('0349876543', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'marie@test.com', 'client');
INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) 
SELECT id, '0349876543', 'Marie', 'Martin', 30000 FROM users WHERE username = '0349876543';

INSERT OR IGNORE INTO users (username, password, email, role) 
VALUES ('0371122334', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'paul@test.com', 'client');
INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) 
SELECT id, '0371122334', 'Paul', 'Dubois', 75000 FROM users WHERE username = '0371122334';

INSERT OR IGNORE INTO users (username, password, email, role) 
VALUES ('0385566778', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sophie@test.com', 'client');
INSERT OR IGNORE INTO clients (user_id, numero_telephone, nom, prenom, solde) 
SELECT id, '0385566778', 'Sophie', 'Lefevre', 120000 FROM users WHERE username = '0385566778';