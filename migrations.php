<?php
// Sistema de migraciones de base de datos.
// Este archivo SÍ se sobreescribe en cada actualización desde GitHub
// (a diferencia de db.php), por lo que es el lugar correcto para esta lógica.

// Aplica los archivos .sql de /migrations que todavía no se hayan ejecutado.
// Devuelve un array de strings describiendo el resultado de cada migración aplicada.
function runPendingMigrations(string $migrationsDir): array {
    $db = getDB();
    $db->exec('CREATE TABLE IF NOT EXISTS schema_migrations (
        filename VARCHAR(255) PRIMARY KEY,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $applied = $db->query('SELECT filename FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
    $files = glob(rtrim($migrationsDir, '/') . '/*.sql');
    sort($files);

    $results = [];
    foreach ($files as $file) {
        $name = basename($file);
        if (in_array($name, $applied, true)) continue;

        $sql = preg_replace('/^--.*$/m', '', file_get_contents($file));
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        try {
            foreach ($statements as $stmt) {
                $db->exec($stmt);
            }
            $db->prepare('INSERT INTO schema_migrations (filename) VALUES (?)')->execute([$name]);
            $results[] = "✅ Migración aplicada: {$name}";
        } catch (Exception $e) {
            $results[] = "❌ Error en migración {$name}: " . $e->getMessage();
            break; // detener ante el primer error para no dejar el esquema a medio aplicar
        }
    }
    return $results;
}
