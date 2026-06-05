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

-- Mundial 2026 (USA/Canadá/México) - Fase de Grupos (72 partidos)
-- Fixture oficial FIFA. Fecha y HORA en zona horaria de Argentina (UTC-3, America/Argentina/Buenos_Aires).
-- Nota: los partidos que arrancan 00:00/01:00 ARG caen al día siguiente respecto del día local norteamericano.
-- Sedes con nombre habitual del estadio + ciudad.

INSERT INTO matches (home_team, away_team, home_flag, away_flag, match_date, stage, group_name, venue) VALUES
('México', 'Sudáfrica', '🇲🇽', '🇿🇦', '2026-06-11 16:00:00', 'Fase de Grupos', 'A', 'Estadio Azteca, Ciudad de México'),
('Corea del Sur', 'República Checa', '🇰🇷', '🇨🇿', '2026-06-11 23:00:00', 'Fase de Grupos', 'A', 'Estadio Akron, Guadalajara'),
('Canadá', 'Bosnia y Herzegovina', '🇨🇦', '🇧🇦', '2026-06-12 16:00:00', 'Fase de Grupos', 'B', 'BMO Field, Toronto'),
('Estados Unidos', 'Paraguay', '🇺🇸', '🇵🇾', '2026-06-12 22:00:00', 'Fase de Grupos', 'D', 'SoFi Stadium, Los Ángeles'),
('Qatar', 'Suiza', '🇶🇦', '🇨🇭', '2026-06-13 16:00:00', 'Fase de Grupos', 'B', 'Levi''s Stadium, San Francisco'),
('Brasil', 'Marruecos', '🇧🇷', '🇲🇦', '2026-06-13 19:00:00', 'Fase de Grupos', 'C', 'MetLife Stadium, Nueva Jersey'),
('Haití', 'Escocia', '🇭🇹', '🏴󠁧󠁢󠁳󠁣󠁴󠁿', '2026-06-13 22:00:00', 'Fase de Grupos', 'C', 'Gillette Stadium, Boston'),
('Australia', 'Turquía', '🇦🇺', '🇹🇷', '2026-06-14 01:00:00', 'Fase de Grupos', 'D', 'BC Place, Vancouver'),
('Alemania', 'Curazao', '🇩🇪', '🇨🇼', '2026-06-14 14:00:00', 'Fase de Grupos', 'E', 'NRG Stadium, Houston'),
('Países Bajos', 'Japón', '🇳🇱', '🇯🇵', '2026-06-14 17:00:00', 'Fase de Grupos', 'F', 'AT&T Stadium, Dallas'),
('Costa de Marfil', 'Ecuador', '🇨🇮', '🇪🇨', '2026-06-14 20:00:00', 'Fase de Grupos', 'E', 'Lincoln Financial Field, Philadelphia'),
('Suecia', 'Túnez', '🇸🇪', '🇹🇳', '2026-06-14 23:00:00', 'Fase de Grupos', 'F', 'Estadio BBVA, Monterrey'),
('España', 'Cabo Verde', '🇪🇸', '🇨🇻', '2026-06-15 13:00:00', 'Fase de Grupos', 'H', 'Mercedes-Benz Stadium, Atlanta'),
('Bélgica', 'Egipto', '🇧🇪', '🇪🇬', '2026-06-15 16:00:00', 'Fase de Grupos', 'G', 'Lumen Field, Seattle'),
('Arabia Saudita', 'Uruguay', '🇸🇦', '🇺🇾', '2026-06-15 19:00:00', 'Fase de Grupos', 'H', 'Hard Rock Stadium, Miami'),
('Irán', 'Nueva Zelanda', '🇮🇷', '🇳🇿', '2026-06-15 22:00:00', 'Fase de Grupos', 'G', 'SoFi Stadium, Los Ángeles'),
('Francia', 'Senegal', '🇫🇷', '🇸🇳', '2026-06-16 16:00:00', 'Fase de Grupos', 'I', 'MetLife Stadium, Nueva Jersey'),
('Irak', 'Noruega', '🇮🇶', '🇳🇴', '2026-06-16 19:00:00', 'Fase de Grupos', 'I', 'Gillette Stadium, Boston'),
('Argentina', 'Argelia', '🇦🇷', '🇩🇿', '2026-06-16 22:00:00', 'Fase de Grupos', 'J', 'Arrowhead Stadium, Kansas City'),
('Austria', 'Jordania', '🇦🇹', '🇯🇴', '2026-06-17 01:00:00', 'Fase de Grupos', 'J', 'Levi''s Stadium, San Francisco'),
('Portugal', 'RD de Congo', '🇵🇹', '🇨🇩', '2026-06-17 14:00:00', 'Fase de Grupos', 'K', 'NRG Stadium, Houston'),
('Inglaterra', 'Croacia', '🏴󠁧󠁢󠁥󠁮󠁧󠁿', '🇭🇷', '2026-06-17 17:00:00', 'Fase de Grupos', 'L', 'AT&T Stadium, Dallas'),
('Ghana', 'Panamá', '🇬🇭', '🇵🇦', '2026-06-17 20:00:00', 'Fase de Grupos', 'L', 'BMO Field, Toronto'),
('Uzbekistán', 'Colombia', '🇺🇿', '🇨🇴', '2026-06-17 23:00:00', 'Fase de Grupos', 'K', 'Estadio Azteca, Ciudad de México'),
('República Checa', 'Sudáfrica', '🇨🇿', '🇿🇦', '2026-06-18 13:00:00', 'Fase de Grupos', 'A', 'Mercedes-Benz Stadium, Atlanta'),
('Suiza', 'Bosnia y Herzegovina', '🇨🇭', '🇧🇦', '2026-06-18 16:00:00', 'Fase de Grupos', 'B', 'SoFi Stadium, Los Ángeles'),
('Canadá', 'Qatar', '🇨🇦', '🇶🇦', '2026-06-18 19:00:00', 'Fase de Grupos', 'B', 'BC Place, Vancouver'),
('México', 'Corea del Sur', '🇲🇽', '🇰🇷', '2026-06-18 22:00:00', 'Fase de Grupos', 'A', 'Estadio Akron, Guadalajara'),
('Estados Unidos', 'Australia', '🇺🇸', '🇦🇺', '2026-06-19 16:00:00', 'Fase de Grupos', 'D', 'Lumen Field, Seattle'),
('Escocia', 'Marruecos', '🏴󠁧󠁢󠁳󠁣󠁴󠁿', '🇲🇦', '2026-06-19 19:00:00', 'Fase de Grupos', 'C', 'Gillette Stadium, Boston'),
('Brasil', 'Haití', '🇧🇷', '🇭🇹', '2026-06-19 21:30:00', 'Fase de Grupos', 'C', 'Lincoln Financial Field, Philadelphia'),
('Turquía', 'Paraguay', '🇹🇷', '🇵🇾', '2026-06-20 00:00:00', 'Fase de Grupos', 'D', 'Levi''s Stadium, San Francisco'),
('Países Bajos', 'Suecia', '🇳🇱', '🇸🇪', '2026-06-20 14:00:00', 'Fase de Grupos', 'F', 'NRG Stadium, Houston'),
('Alemania', 'Costa de Marfil', '🇩🇪', '🇨🇮', '2026-06-20 17:00:00', 'Fase de Grupos', 'E', 'BMO Field, Toronto'),
('Ecuador', 'Curazao', '🇪🇨', '🇨🇼', '2026-06-20 23:00:00', 'Fase de Grupos', 'E', 'Arrowhead Stadium, Kansas City'),
('Túnez', 'Japón', '🇹🇳', '🇯🇵', '2026-06-21 01:00:00', 'Fase de Grupos', 'F', 'Estadio BBVA, Monterrey'),
('España', 'Arabia Saudita', '🇪🇸', '🇸🇦', '2026-06-21 13:00:00', 'Fase de Grupos', 'H', 'Mercedes-Benz Stadium, Atlanta'),
('Bélgica', 'Irán', '🇧🇪', '🇮🇷', '2026-06-21 16:00:00', 'Fase de Grupos', 'G', 'SoFi Stadium, Los Ángeles'),
('Uruguay', 'Cabo Verde', '🇺🇾', '🇨🇻', '2026-06-21 19:00:00', 'Fase de Grupos', 'H', 'Hard Rock Stadium, Miami'),
('Nueva Zelanda', 'Egipto', '🇳🇿', '🇪🇬', '2026-06-21 22:00:00', 'Fase de Grupos', 'G', 'BC Place, Vancouver'),
('Argentina', 'Austria', '🇦🇷', '🇦🇹', '2026-06-22 14:00:00', 'Fase de Grupos', 'J', 'AT&T Stadium, Dallas'),
('Francia', 'Irak', '🇫🇷', '🇮🇶', '2026-06-22 18:00:00', 'Fase de Grupos', 'I', 'Lincoln Financial Field, Philadelphia'),
('Noruega', 'Senegal', '🇳🇴', '🇸🇳', '2026-06-22 21:00:00', 'Fase de Grupos', 'I', 'MetLife Stadium, Nueva Jersey'),
('Jordania', 'Argelia', '🇯🇴', '🇩🇿', '2026-06-23 00:00:00', 'Fase de Grupos', 'J', 'Levi''s Stadium, San Francisco'),
('Portugal', 'Uzbekistán', '🇵🇹', '🇺🇿', '2026-06-23 14:00:00', 'Fase de Grupos', 'K', 'NRG Stadium, Houston'),
('Inglaterra', 'Ghana', '🏴󠁧󠁢󠁥󠁮󠁧󠁿', '🇬🇭', '2026-06-23 17:00:00', 'Fase de Grupos', 'L', 'Gillette Stadium, Boston'),
('Panamá', 'Croacia', '🇵🇦', '🇭🇷', '2026-06-23 20:00:00', 'Fase de Grupos', 'L', 'BMO Field, Toronto'),
('Colombia', 'RD de Congo', '🇨🇴', '🇨🇩', '2026-06-23 23:00:00', 'Fase de Grupos', 'K', 'Estadio Akron, Guadalajara'),
('Suiza', 'Canadá', '🇨🇭', '🇨🇦', '2026-06-24 16:00:00', 'Fase de Grupos', 'B', 'BC Place, Vancouver'),
('Bosnia y Herzegovina', 'Qatar', '🇧🇦', '🇶🇦', '2026-06-24 16:00:00', 'Fase de Grupos', 'B', 'Lumen Field, Seattle'),
('Marruecos', 'Haití', '🇲🇦', '🇭🇹', '2026-06-24 19:00:00', 'Fase de Grupos', 'C', 'Mercedes-Benz Stadium, Atlanta'),
('Brasil', 'Escocia', '🇧🇷', '🏴󠁧󠁢󠁳󠁣󠁴󠁿', '2026-06-24 19:00:00', 'Fase de Grupos', 'C', 'Hard Rock Stadium, Miami'),
('Sudáfrica', 'Corea del Sur', '🇿🇦', '🇰🇷', '2026-06-24 22:00:00', 'Fase de Grupos', 'A', 'Estadio BBVA, Monterrey'),
('República Checa', 'México', '🇨🇿', '🇲🇽', '2026-06-24 22:00:00', 'Fase de Grupos', 'A', 'Estadio Azteca, Ciudad de México'),
('Curazao', 'Costa de Marfil', '🇨🇼', '🇨🇮', '2026-06-25 17:00:00', 'Fase de Grupos', 'E', 'Lincoln Financial Field, Philadelphia'),
('Ecuador', 'Alemania', '🇪🇨', '🇩🇪', '2026-06-25 17:00:00', 'Fase de Grupos', 'E', 'MetLife Stadium, Nueva Jersey'),
('Japón', 'Suecia', '🇯🇵', '🇸🇪', '2026-06-25 20:00:00', 'Fase de Grupos', 'F', 'AT&T Stadium, Dallas'),
('Túnez', 'Países Bajos', '🇹🇳', '🇳🇱', '2026-06-25 20:00:00', 'Fase de Grupos', 'F', 'Arrowhead Stadium, Kansas City'),
('Paraguay', 'Australia', '🇵🇾', '🇦🇺', '2026-06-25 23:00:00', 'Fase de Grupos', 'D', 'Levi''s Stadium, San Francisco'),
('Turquía', 'Estados Unidos', '🇹🇷', '🇺🇸', '2026-06-25 23:00:00', 'Fase de Grupos', 'D', 'SoFi Stadium, Los Ángeles'),
('Noruega', 'Francia', '🇳🇴', '🇫🇷', '2026-06-26 16:00:00', 'Fase de Grupos', 'I', 'Gillette Stadium, Boston'),
('Senegal', 'Irak', '🇸🇳', '🇮🇶', '2026-06-26 16:00:00', 'Fase de Grupos', 'I', 'BMO Field, Toronto'),
('Cabo Verde', 'Arabia Saudita', '🇨🇻', '🇸🇦', '2026-06-26 21:00:00', 'Fase de Grupos', 'H', 'NRG Stadium, Houston'),
('Uruguay', 'España', '🇺🇾', '🇪🇸', '2026-06-26 21:00:00', 'Fase de Grupos', 'H', 'Estadio Akron, Guadalajara'),
('Egipto', 'Irán', '🇪🇬', '🇮🇷', '2026-06-27 00:00:00', 'Fase de Grupos', 'G', 'Lumen Field, Seattle'),
('Nueva Zelanda', 'Bélgica', '🇳🇿', '🇧🇪', '2026-06-27 00:00:00', 'Fase de Grupos', 'G', 'BC Place, Vancouver'),
('Croacia', 'Ghana', '🇭🇷', '🇬🇭', '2026-06-27 18:00:00', 'Fase de Grupos', 'L', 'Lincoln Financial Field, Philadelphia'),
('Panamá', 'Inglaterra', '🇵🇦', '🏴󠁧󠁢󠁥󠁮󠁧󠁿', '2026-06-27 18:00:00', 'Fase de Grupos', 'L', 'MetLife Stadium, Nueva Jersey'),
('Colombia', 'Portugal', '🇨🇴', '🇵🇹', '2026-06-27 20:30:00', 'Fase de Grupos', 'K', 'Hard Rock Stadium, Miami'),
('RD de Congo', 'Uzbekistán', '🇨🇩', '🇺🇿', '2026-06-27 20:30:00', 'Fase de Grupos', 'K', 'Mercedes-Benz Stadium, Atlanta'),
('Argelia', 'Austria', '🇩🇿', '🇦🇹', '2026-06-27 23:00:00', 'Fase de Grupos', 'J', 'Arrowhead Stadium, Kansas City'),
('Jordania', 'Argentina', '🇯🇴', '🇦🇷', '2026-06-27 23:00:00', 'Fase de Grupos', 'J', 'AT&T Stadium, Dallas');
