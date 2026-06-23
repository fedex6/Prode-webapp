-- Migración: instancia de penales para partidos eliminatorios
-- Ejecutar una sola vez sobre la base existente.

ALTER TABLE matches
    ADD COLUMN can_penalties TINYINT(1) DEFAULT 0,
    ADD COLUMN went_penalties TINYINT(1) DEFAULT 0,
    ADD COLUMN penalty_home INT DEFAULT NULL,
    ADD COLUMN penalty_away INT DEFAULT NULL;

ALTER TABLE predictions
    ADD COLUMN pred_penalty_home INT DEFAULT NULL,
    ADD COLUMN pred_penalty_away INT DEFAULT NULL;
