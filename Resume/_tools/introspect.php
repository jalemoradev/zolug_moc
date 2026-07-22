<?php
/**
 * Read-only MySQL/MariaDB introspection for legacy documentation.
 *
 * STRICTLY READ-ONLY. Issues only SHOW / SELECT / DESCRIBE / information_schema.
 * No INSERT / UPDATE / DELETE / DDL / TRUNCATE anywhere in this file.
 *
 * Config via environment variables (kept out of argv):
 *   DB_HOST DB_PORT DB_USER DB_PASS DB_NAME OUT_DIR LABEL [SAMPLE_LIMIT] [LOWCARD_MAX]
 *
 * Output (all under OUT_DIR):
 *   connection.json          server + db facts
 *   tables.json              per-table engine/rows/sizes/collation/timestamps
 *   columns.json             information_schema.COLUMNS (full)
 *   indexes.json             information_schema.STATISTICS
 *   ddl/<table>.sql          SHOW CREATE TABLE
 *   samples/<table>.json     SELECT * LIMIT N (sanitized: password-like cols masked)
 *   column_profiles.json     per column: distinct, nulls, empties, min/max, low-card value list
 *   quality.json             garbage rows (operacion=0), md5('0') passwords, zero-dates, dup ratios
 *   RESUMEN.md               human-readable digest
 *   _run.log                 timeline + any per-query errors
 */

date_default_timezone_set('America/Bogota');

function envv($k, $def = null) { $v = getenv($k); return ($v === false || $v === '') ? $def : $v; }

$HOST  = envv('DB_HOST');
$PORT  = (int) envv('DB_PORT', '3306');
$USER  = envv('DB_USER');
$PASS  = envv('DB_PASS', '');
$NAME  = envv('DB_NAME');
$OUT   = rtrim(envv('OUT_DIR', '.'), '/');
$LABEL = envv('LABEL', $NAME);
$SAMPLE_LIMIT = (int) envv('SAMPLE_LIMIT', '5');
$LOWCARD_MAX  = (int) envv('LOWCARD_MAX', '50');

if (!$HOST || !$USER || !$NAME) {
    fwrite(STDERR, "Missing DB_HOST/DB_USER/DB_NAME env vars\n");
    exit(2);
}

@mkdir($OUT, 0777, true);
@mkdir("$OUT/ddl", 0777, true);
@mkdir("$OUT/samples", 0777, true);

$log = [];
$t0 = microtime(true);
function logline(&$log, $msg) { $log[] = date('H:i:s') . "  " . $msg; }

logline($log, "Connecting to $LABEL ($HOST:$PORT / db=$NAME) as $USER");

mysqli_report(MYSQLI_REPORT_OFF); // we handle errors manually; never throw/abort

$GLOBALS['__cfg'] = compact('HOST', 'USER', 'PASS', 'NAME', 'PORT');

function db_connect(&$log) {
    $c = $GLOBALS['__cfg'];
    $cn = mysqli_init();
    $cn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 15);
    $ok = @$cn->real_connect($c['HOST'], $c['USER'], $c['PASS'], $c['NAME'], $c['PORT']);
    if (!$ok) return null;
    @$cn->set_charset('utf8mb4');
    // Server-side per-statement time cap: heavy aggregates abort CLEANLY (catchable error)
    // instead of the server killing the socket ("MySQL server has gone away").
    @$cn->query("SET SESSION max_statement_time=120");
    $GLOBALS['__cn'] = $cn;
    return $cn;
}

$cn = db_connect($log);
if (!$cn) {
    logline($log, "CONNECT FAILED: (" . mysqli_connect_errno() . ") " . mysqli_connect_error());
    file_put_contents("$OUT/_run.log", implode("\n", $log) . "\n");
    fwrite(STDERR, "Connect failed for $LABEL: " . mysqli_connect_error() . "\n");
    exit(1);
}
logline($log, "Connected OK (max_statement_time=120s)");

/**
 * Run a read-only query. Resilient to connection drops: on a connection-level
 * error (2006 gone away / 2013 lost connection) it reconnects and retries ONCE.
 * Always uses the current global connection so a reconnect is transparent to callers.
 */
function q($ignored, $sql, &$log) {
    $cn = $GLOBALS['__cn'];
    $res = @$cn->query($sql);
    if ($res === false) {
        $errno = $cn->errno;
        if ($errno === 2006 || $errno === 2013 || $errno === 0) {
            logline($log, "RECONNECT after errno=$errno on: " . substr($sql, 0, 120));
            $cn = db_connect($log);
            if ($cn) { $res = @$cn->query($sql); }
        }
        if ($res === false) {
            logline($log, "QUERY ERR: " . ($cn ? $cn->error : 'no conn') . " | SQL: " . substr($sql, 0, 200));
            return [];
        }
    }
    if ($res === true) return [];
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $res->free();
    return $rows;
}
function q1($ignored, $sql, &$log) { $r = q($ignored, $sql, $log); return $r[0] ?? null; }
function esc($ignored, $s) { return $GLOBALS['__cn']->real_escape_string($s); }

$NAME_ESC = esc($cn, $NAME);

// ---- connection.json ----
$ver = q1($cn, "SELECT VERSION() AS v", $log);
$now = q1($cn, "SELECT NOW() AS n, @@time_zone AS tz, @@version_comment AS vc, DATABASE() AS db", $log);
$dbmeta = q1($cn, "SELECT DEFAULT_CHARACTER_SET_NAME cs, DEFAULT_COLLATION_NAME col
                   FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='$NAME_ESC'", $log);
$connection = [
    'label' => $LABEL,
    'host' => $HOST, 'port' => $PORT, 'database' => $NAME, 'user' => $USER,
    'server_version' => $ver['v'] ?? null,
    'version_comment' => $now['vc'] ?? null,
    'server_now' => $now['n'] ?? null,
    'server_time_zone' => $now['tz'] ?? null,
    'db_charset' => $dbmeta['cs'] ?? null,
    'db_collation' => $dbmeta['col'] ?? null,
    'introspected_at' => date('c'),
];
file_put_contents("$OUT/connection.json", json_encode($connection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
logline($log, "Server: " . ($ver['v'] ?? '?'));

// ---- tables.json ----
$tables = q($cn, "SELECT TABLE_NAME, ENGINE, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH,
                         AUTO_INCREMENT, TABLE_COLLATION, CREATE_TIME, UPDATE_TIME, TABLE_COMMENT
                  FROM information_schema.TABLES
                  WHERE TABLE_SCHEMA='$NAME_ESC' AND TABLE_TYPE='BASE TABLE'
                  ORDER BY TABLE_NAME", $log);
file_put_contents("$OUT/tables.json", json_encode($tables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$tableNames = array_map(fn($t) => $t['TABLE_NAME'], $tables);
logline($log, "Tables: " . count($tableNames) . " -> " . implode(', ', $tableNames));

// ---- columns.json ----
$columns = q($cn, "SELECT TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE,
                          DATA_TYPE, COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE,
                          CHARACTER_SET_NAME, COLLATION_NAME, COLUMN_KEY, EXTRA, COLUMN_COMMENT
                   FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA='$NAME_ESC'
                   ORDER BY TABLE_NAME, ORDINAL_POSITION", $log);
file_put_contents("$OUT/columns.json", json_encode($columns, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// group columns by table
$colsByTable = [];
foreach ($columns as $c) { $colsByTable[$c['TABLE_NAME']][] = $c; }

// ---- indexes.json ----
$indexes = q($cn, "SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, CARDINALITY, INDEX_TYPE
                   FROM information_schema.STATISTICS
                   WHERE TABLE_SCHEMA='$NAME_ESC'
                   ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX", $log);
file_put_contents("$OUT/indexes.json", json_encode($indexes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// ---- DDL per table ----
foreach ($tableNames as $t) {
    $te = esc($cn, $t);
    $row = q1($cn, "SHOW CREATE TABLE `$te`", $log);
    if ($row) {
        $ddl = $row['Create Table'] ?? ($row['Create View'] ?? '');
        file_put_contents("$OUT/ddl/$t.sql", $ddl . "\n");
    }
}
logline($log, "DDL dumped for " . count($tableNames) . " tables");

// helper: is a column name password-like (mask in samples)
function isSecret($col) {
    $c = strtolower($col);
    return strpos($c, 'pass') !== false || strpos($c, 'pwd') !== false || $c === 'userpass';
}

// ---- samples per table (sanitized) ----
foreach ($tableNames as $t) {
    $te = esc($cn, $t);
    $rows = q($cn, "SELECT * FROM `$te` LIMIT $SAMPLE_LIMIT", $log);
    foreach ($rows as &$r) {
        foreach ($r as $k => $v) {
            if (isSecret($k) && $v !== null) $r[$k] = '***MASKED(len=' . strlen($v) . ')***';
        }
    }
    unset($r);
    file_put_contents("$OUT/samples/$t.json", json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
logline($log, "Samples dumped (LIMIT $SAMPLE_LIMIT, secrets masked)");

// ---- column profiles ----
$profiles = [];
foreach ($tableNames as $t) {
    $te = esc($cn, $t);
    $cntRow = q1($cn, "SELECT COUNT(*) AS c FROM `$te`", $log);
    $total = (int) ($cntRow['c'] ?? 0);
    $profiles[$t] = ['row_count' => $total, 'columns' => []];
    foreach (($colsByTable[$t] ?? []) as $c) {
        $col = $c['COLUMN_NAME'];
        $ce = esc($cn, $col);
        $isText = in_array(strtolower($c['DATA_TYPE']), ['varchar','char','text','tinytext','mediumtext','longtext','enum','set']);
        $prof = [
            'data_type' => $c['COLUMN_TYPE'],
            'nullable' => $c['IS_NULLABLE'],
            'default' => $c['COLUMN_DEFAULT'],
            'key' => $c['COLUMN_KEY'],
            'charset' => $c['CHARACTER_SET_NAME'],
        ];
        if ($total > 0 && !isSecret($col)) {
            $agg = q1($cn, "SELECT COUNT(DISTINCT `$ce`) AS d, SUM(`$ce` IS NULL) AS nulls FROM `$te`", $log);
            $prof['distinct'] = isset($agg['d']) ? (int)$agg['d'] : null;
            $prof['nulls'] = isset($agg['nulls']) ? (int)$agg['nulls'] : null;
            if ($isText) {
                $emp = q1($cn, "SELECT SUM(`$ce`='') AS e FROM `$te`", $log);
                $prof['empties'] = isset($emp['e']) ? (int)$emp['e'] : null;
            } else {
                $mm = q1($cn, "SELECT MIN(`$ce`) AS mn, MAX(`$ce`) AS mx FROM `$te`", $log);
                $prof['min'] = $mm['mn'] ?? null;
                $prof['max'] = $mm['mx'] ?? null;
            }
            // low-cardinality value distribution
            if (isset($prof['distinct']) && $prof['distinct'] > 0 && $prof['distinct'] <= $LOWCARD_MAX) {
                $vals = q($cn, "SELECT `$ce` AS val, COUNT(*) AS n FROM `$te` GROUP BY `$ce` ORDER BY n DESC LIMIT $LOWCARD_MAX", $log);
                $prof['values'] = $vals;
            }
        }
        $profiles[$t]['columns'][$col] = $prof;
    }
}
file_put_contents("$OUT/column_profiles.json", json_encode($profiles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
logline($log, "Column profiles computed");

// ---- quality checks ----
$quality = ['garbage_operacion_zero' => [], 'zero_dates' => [], 'password_md5_zero' => [], 'duplicate_ratio_vs_t_base' => []];

// which tables have an 'operacion' column?
foreach ($tableNames as $t) {
    $hasOp = false;
    foreach (($colsByTable[$t] ?? []) as $c) { if (strtolower($c['COLUMN_NAME']) === 'operacion') { $hasOp = true; break; } }
    if ($hasOp) {
        $te = esc($cn, $t);
        $r = q1($cn, "SELECT COUNT(*) AS c FROM `$te` WHERE operacion='0' OR operacion=0", $log);
        $quality['garbage_operacion_zero'][$t] = (int)($r['c'] ?? 0);
    }
}

// zero dates per date/datetime column
foreach ($columns as $c) {
    if (in_array(strtolower($c['DATA_TYPE']), ['date','datetime','timestamp'])) {
        $t = $c['TABLE_NAME']; $col = $c['COLUMN_NAME'];
        $te = esc($cn, $t); $ce = esc($cn, $col);
        $r = q1($cn, "SELECT COUNT(*) AS c FROM `$te` WHERE `$ce`='0000-00-00' OR `$ce`='0000-00-00 00:00:00'", $log);
        $n = (int)($r['c'] ?? 0);
        if ($n > 0) $quality['zero_dates']["$t.$col"] = $n;
    }
}

// md5('0') password detection in user-like tables
$md5zero = md5('0'); // cf3...; computed locally, compared in SQL as literal
foreach ($tableNames as $t) {
    foreach (($colsByTable[$t] ?? []) as $c) {
        if (isSecret($c['COLUMN_NAME'])) {
            $te = esc($cn, $t); $ce = esc($cn, $c['COLUMN_NAME']);
            $tot = q1($cn, "SELECT COUNT(*) AS c FROM `$te`", $log);
            $hit = q1($cn, "SELECT COUNT(*) AS c FROM `$te` WHERE `$ce`='$md5zero'", $log);
            $quality['password_md5_zero']["$t.{$c['COLUMN_NAME']}"] = [
                'total' => (int)($tot['c'] ?? 0),
                'md5_of_zero' => (int)($hit['c'] ?? 0),
            ];
        }
    }
}

// duplicate ratio: tables with 'operacion' vs t_base distinct operacion (logical 1:1 expectation)
if (in_array('t_base', $tableNames)) {
    $base = q1($cn, "SELECT COUNT(DISTINCT operacion) AS d FROM t_base", $log);
    $baseDistinct = (int)($base['d'] ?? 0);
    $quality['t_base_distinct_operacion'] = $baseDistinct;
    foreach ($tableNames as $t) {
        if ($t === 't_base') continue;
        $hasOp = false;
        foreach (($colsByTable[$t] ?? []) as $c) { if (strtolower($c['COLUMN_NAME']) === 'operacion') { $hasOp = true; break; } }
        if ($hasOp && $baseDistinct > 0) {
            $te = esc($cn, $t);
            $r = q1($cn, "SELECT COUNT(*) AS rows_total, COUNT(DISTINCT operacion) AS d FROM `$te`", $log);
            $rowsTotal = (int)($r['rows_total'] ?? 0);
            $dist = (int)($r['d'] ?? 0);
            $quality['duplicate_ratio_vs_t_base'][$t] = [
                'rows_total' => $rowsTotal,
                'distinct_operacion' => $dist,
                'rows_per_distinct' => $dist > 0 ? round($rowsTotal / $dist, 3) : null,
            ];
        }
    }
}
file_put_contents("$OUT/quality.json", json_encode($quality, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
logline($log, "Quality checks done");

// ---- RESUMEN.md ----
$md = [];
$md[] = "# Evidencia de introspección — $LABEL";
$md[] = "";
$md[] = "> Generado: " . date('c') . " · Servidor: " . ($connection['server_version'] ?? '?') .
        " · TZ servidor: " . ($connection['server_time_zone'] ?? '?');
$md[] = "> Conexión read-only a `$NAME` en `$HOST:$PORT` como `$USER`.";
$md[] = "";
$md[] = "## Tablas (" . count($tableNames) . ")";
$md[] = "";
$md[] = "| Tabla | Engine | Filas (aprox IS) | Filas (COUNT) | Datos | Índices | Colación | Última modif. |";
$md[] = "|---|---|---:|---:|---:|---:|---|---|";
foreach ($tables as $t) {
    $tn = $t['TABLE_NAME'];
    $cnt = $profiles[$tn]['row_count'] ?? '?';
    $dl = number_format((int)$t['DATA_LENGTH']);
    $il = number_format((int)$t['INDEX_LENGTH']);
    $md[] = "| `$tn` | {$t['ENGINE']} | " . number_format((int)$t['TABLE_ROWS']) . " | " . number_format((int)$cnt) .
            " | $dl | $il | {$t['TABLE_COLLATION']} | " . ($t['UPDATE_TIME'] ?? '—') . " |";
}
$md[] = "";
$md[] = "## Calidad de datos";
$md[] = "";
$md[] = "**Filas basura `operacion=0`:** ";
foreach ($quality['garbage_operacion_zero'] as $t => $n) { if ($n > 0) $md[] = "- `$t`: $n"; }
$md[] = "";
$md[] = "**Passwords = md5('0'):**";
foreach ($quality['password_md5_zero'] as $k => $v) { $md[] = "- `$k`: {$v['md5_of_zero']} de {$v['total']}"; }
$md[] = "";
if (!empty($quality['zero_dates'])) {
    $md[] = "**Fechas 0000-00-00:**";
    foreach ($quality['zero_dates'] as $k => $n) { $md[] = "- `$k`: $n"; }
    $md[] = "";
}
$md[] = "**Ratio filas/operación-distinta (esperado ~1.0):**";
foreach (($quality['duplicate_ratio_vs_t_base'] ?? []) as $t => $v) {
    $md[] = "- `$t`: {$v['rows_per_distinct']}× ({$v['rows_total']} filas / {$v['distinct_operacion']} ops)";
}
$md[] = "";
$md[] = "Archivos crudos: `connection.json`, `tables.json`, `columns.json`, `indexes.json`, `column_profiles.json`, `quality.json`, `ddl/*.sql`, `samples/*.json`.";
file_put_contents("$OUT/RESUMEN.md", implode("\n", $md) . "\n");

$elapsed = round(microtime(true) - $t0, 1);
logline($log, "DONE in {$elapsed}s");
file_put_contents("$OUT/_run.log", implode("\n", $log) . "\n");

echo "OK $LABEL: " . count($tableNames) . " tablas, evidencia en $OUT ({$elapsed}s)\n";
@$GLOBALS['__cn']->close();
