-- Prode Mundial 2026 - Schema de base de datos
CREATE DATABASE IF NOT EXISTS prode_mundial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prode_mundial;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    home_team VARCHAR(100) NOT NULL,
    away_team VARCHAR(100) NOT NULL,
    home_flag VARCHAR(10) DEFAULT '',
    away_flag VARCHAR(10) DEFAULT '',
    match_date DATETIME NOT NULL,
    stage VARCHAR(80) DEFAULT 'Fase de Grupos',
    group_name VARCHAR(10) DEFAULT NULL,
    venue VARCHAR(100) DEFAULT '',
    home_score INT DEFAULT NULL,
    away_score INT DEFAULT NULL,
    is_finished TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    match_id INT NOT NULL,
    home_score INT NOT NULL,
    away_score INT NOT NULL,
    points INT DEFAULT 0,
    scored TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_prediction (user_id, match_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value LONGTEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Admin por defecto (password: admin123)
INSERT INTO users (username, display_name, password, is_admin) VALUES
('admin', 'Administrador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Configuración por defecto
INSERT INTO settings (setting_key, setting_value) VALUES
('app_title', 'Prode Mundial 2026'),
('primary_color', '#2964a0'),
('primary_color_dark', '#153c8f'),
('primary_color_light', '#e8ecf5'),
('accent_color', '#f59e0b');

-- Partidos del Mundial 2026 (selección de partidos principales)
INSERT INTO matches (home_team, away_team, home_flag, away_flag, match_date, stage, group_name, venue) VALUES
-- Grupo A
('México', 'Ecuador', '🇲🇽', '🇪🇨', '2026-06-13 16:00:00', 'Fase de Grupos', 'A', 'SoFi Stadium, Los Ángeles'),
('EEUU', 'Bolivia', '🇺🇸', '🇧🇴', '2026-06-14 19:00:00', 'Fase de Grupos', 'A', 'MetLife Stadium, Nueva York'),
('EEUU', 'Ecuador', '🇺🇸', '🇪🇨', '2026-06-18 16:00:00', 'Fase de Grupos', 'A', 'AT&T Stadium, Dallas'),
('México', 'Bolivia', '🇲🇽', '🇧🇴', '2026-06-18 19:00:00', 'Fase de Grupos', 'A', 'Rose Bowl, Pasadena'),
('EEUU', 'México', '🇺🇸', '🇲🇽', '2026-06-22 19:00:00', 'Fase de Grupos', 'A', 'Estadio Azteca, Ciudad de México'),
('Ecuador', 'Bolivia', '🇪🇨', '🇧🇴', '2026-06-22 16:00:00', 'Fase de Grupos', 'A', 'Levi''s Stadium, San Francisco'),

-- Grupo B
('Argentina', 'Guinea', '🇦🇷', '🇬🇳', '2026-06-13 13:00:00', 'Fase de Grupos', 'B', 'MetLife Stadium, Nueva York'),
('Marruecos', 'Ucrania', '🇲🇦', '🇺🇦', '2026-06-14 13:00:00', 'Fase de Grupos', 'B', 'Hard Rock Stadium, Miami'),
('Argentina', 'Ucrania', '🇦🇷', '🇺🇦', '2026-06-17 16:00:00', 'Fase de Grupos', 'B', 'Cowboys Stadium, Dallas'),
('Marruecos', 'Guinea', '🇲🇦', '🇬🇳', '2026-06-17 19:00:00', 'Fase de Grupos', 'B', 'Lincoln Financial, Filadelfia'),
('Argentina', 'Marruecos', '🇦🇷', '🇲🇦', '2026-06-21 19:00:00', 'Fase de Grupos', 'B', 'MetLife Stadium, Nueva York'),
('Ucrania', 'Guinea', '🇺🇦', '🇬🇳', '2026-06-21 16:00:00', 'Fase de Grupos', 'B', 'BC Place, Vancouver'),

-- Grupo C
('Francia', 'Arabia Saudita', '🇫🇷', '🇸🇦', '2026-06-14 16:00:00', 'Fase de Grupos', 'C', 'BC Place, Vancouver'),
('Australia', 'Nigeria', '🇦🇺', '🇳🇬', '2026-06-14 22:00:00', 'Fase de Grupos', 'C', 'Empower Field, Denver'),
('Francia', 'Nigeria', '🇫🇷', '🇳🇬', '2026-06-18 13:00:00', 'Fase de Grupos', 'C', 'Gillette Stadium, Boston'),
('Australia', 'Arabia Saudita', '🇦🇺', '🇸🇦', '2026-06-19 16:00:00', 'Fase de Grupos', 'C', 'SoFi Stadium, Los Ángeles'),
('Francia', 'Australia', '🇫🇷', '🇦🇺', '2026-06-23 19:00:00', 'Fase de Grupos', 'C', 'BC Place, Vancouver'),
('Nigeria', 'Arabia Saudita', '🇳🇬', '🇸🇦', '2026-06-23 16:00:00', 'Fase de Grupos', 'C', 'Hard Rock Stadium, Miami'),

-- Grupo D
('España', 'Serbia', '🇪🇸', '🇷🇸', '2026-06-15 16:00:00', 'Fase de Grupos', 'D', 'Rose Bowl, Pasadena'),
('Brasil', 'Japón', '🇧🇷', '🇯🇵', '2026-06-15 19:00:00', 'Fase de Grupos', 'D', 'MetLife Stadium, Nueva York'),
('España', 'Japón', '🇪🇸', '🇯🇵', '2026-06-19 13:00:00', 'Fase de Grupos', 'D', 'SoFi Stadium, Los Ángeles'),
('Brasil', 'Serbia', '🇧🇷', '🇷🇸', '2026-06-19 19:00:00', 'Fase de Grupos', 'D', 'AT&T Stadium, Dallas'),
('España', 'Brasil', '🇪🇸', '🇧🇷', '2026-06-23 22:00:00', 'Fase de Grupos', 'D', 'Hard Rock Stadium, Miami'),
('Japón', 'Serbia', '🇯🇵', '🇷🇸', '2026-06-23 22:00:00', 'Fase de Grupos', 'D', 'Empower Field, Denver'),

-- Grupo E
('Alemania', 'Escocia', '🇩🇪', '🏴󠁧󠁢󠁳󠁣󠁴󠁿', '2026-06-15 13:00:00', 'Fase de Grupos', 'E', 'Gillette Stadium, Boston'),
('Portugal', 'Corea del Sur', '🇵🇹', '🇰🇷', '2026-06-16 16:00:00', 'Fase de Grupos', 'E', 'Lincoln Financial, Filadelfia'),
('Alemania', 'Corea del Sur', '🇩🇪', '🇰🇷', '2026-06-20 13:00:00', 'Fase de Grupos', 'E', 'MetLife Stadium, Nueva York'),
('Portugal', 'Escocia', '🇵🇹', '🏴󠁧󠁢󠁳󠁣󠁴󠁿', '2026-06-20 19:00:00', 'Fase de Grupos', 'E', 'Rose Bowl, Pasadena'),
('Alemania', 'Portugal', '🇩🇪', '🇵🇹', '2026-06-24 19:00:00', 'Fase de Grupos', 'E', 'SoFi Stadium, Los Ángeles'),
('Escocia', 'Corea del Sur', '🏴󠁧󠁢󠁳󠁣󠁴󠁿', '🇰🇷', '2026-06-24 19:00:00', 'Fase de Grupos', 'E', 'AT&T Stadium, Dallas'),

-- Grupo F
('Inglaterra', 'Túnez', '🏴󠁧󠁢󠁥󠁮󠁧󠁿', '🇹🇳', '2026-06-16 13:00:00', 'Fase de Grupos', 'F', 'Hard Rock Stadium, Miami'),
('Colombia', 'Eslovenia', '🇨🇴', '🇸🇮', '2026-06-16 19:00:00', 'Fase de Grupos', 'F', 'SoFi Stadium, Los Ángeles'),
('Inglaterra', 'Eslovenia', '🏴󠁧󠁢󠁥󠁮󠁧󠁿', '🇸🇮', '2026-06-20 16:00:00', 'Fase de Grupos', 'F', 'AT&T Stadium, Dallas'),
('Colombia', 'Túnez', '🇨🇴', '🇹🇳', '2026-06-21 13:00:00', 'Fase de Grupos', 'F', 'Empower Field, Denver'),
('Inglaterra', 'Colombia', '🏴󠁧󠁢󠁥󠁮󠁧󠁿', '🇨🇴', '2026-06-25 19:00:00', 'Fase de Grupos', 'F', 'MetLife Stadium, Nueva York'),
('Túnez', 'Eslovenia', '🇹🇳', '🇸🇮', '2026-06-25 19:00:00', 'Fase de Grupos', 'F', 'BC Place, Vancouver');
