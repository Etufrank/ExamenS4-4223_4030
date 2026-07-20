-- ============================================
-- TABLE DES TYPES D'OPÉRATIONS
-- ============================================
CREATE TABLE IF NOT EXISTS types_operations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL UNIQUE,
    code TEXT NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE DES BARÈMES DE FRAIS
-- ============================================
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

-- ============================================
-- INDEX
-- ============================================
CREATE INDEX idx_baremes_type ON baremes_frais(type_operation_id);

-- ============================================
-- DONNÉES INITIALES
-- ============================================

-- 1. Types d'opérations
INSERT OR IGNORE INTO types_operations (nom, code, description) VALUES
('dépôt', 'DEP', 'Dépôt sur compte'),
('retrait', 'RET', 'Retrait depuis compte'),
('transfert', 'TRANS', 'Transfert entre comptes');

-- 2. Barèmes pour RETRAIT (on utilise des sous-requêtes pour récupérer l'ID de 'RET')
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

-- 3. Barèmes pour TRANSFERT (mêmes valeurs)
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

-- 4. Barème pour DÉPÔT (frais à 0)
INSERT OR IGNORE INTO baremes_frais (type_operation_id, montant_min, montant_max, frais_fixe)
SELECT id, 0, 999999999, 0 FROM types_operations WHERE code = 'DEP';