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

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_clients_telephone ON clients(numero_telephone);
CREATE INDEX idx_clients_user ON clients(user_id);
CREATE INDEX idx_transactions_client ON transactions(client_id);
CREATE INDEX idx_transactions_type ON transactions(type_operation_id);
CREATE INDEX idx_transactions_date ON transactions(date_transaction);
CREATE INDEX idx_baremes_type ON baremes_frais(type_operation_id);