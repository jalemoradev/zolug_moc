<?php
/**
 * Render db/*.md documentation from introspection evidence (read-only, local files).
 * Usage (env): EV_DIR (evidence dir), OUT_DIR (db/ output dir), LABEL
 * Produces: 10-esquema-completo.md, 11-diccionario-de-datos.md,
 *           12-relaciones.md, 13-volumetria-y-calidad.md
 */
function envv($k,$d=null){$v=getenv($k);return ($v===false||$v==='')?$d:$v;}
$EV  = rtrim(envv('EV_DIR'),'/');
$OUT = rtrim(envv('OUT_DIR'),'/');
$LABEL = envv('LABEL','?');
if(!$EV||!$OUT){fwrite(STDERR,"need EV_DIR and OUT_DIR\n");exit(2);}
@mkdir($OUT,0777,true);

function jload($f){ return file_exists($f) ? json_decode(file_get_contents($f),true) : null; }
function nf($n){ return number_format((int)$n); }
function mb($bytes){ $b=(int)$bytes; if($b<1024)return $b.' B'; if($b<1048576)return round($b/1024,1).' KB'; return round($b/1048576,1).' MB'; }
function md_esc($s){ $s=(string)$s; return str_replace(['|',"\n","\r"],['\\|',' ',''],$s); }

$conn   = jload("$EV/connection.json");
$tables = jload("$EV/tables.json") ?? [];
$cols   = jload("$EV/columns.json") ?? [];
$prof   = jload("$EV/column_profiles.json") ?? [];
$qual   = jload("$EV/quality.json") ?? [];

$gen = "> Generado desde evidencia de introspección (`_evidence/`) el ".date('c').".\n".
       "> Servidor: ".($conn['server_version']??'?')." · BD: `".($conn['database']??'?')."` · ".
       "charset BD: ".($conn['db_charset']??'?')." / ".($conn['db_collation']??'?').".\n".
       "> Introspección original: ".($conn['introspected_at']??'?').".";

$colsByTable=[]; foreach($cols as $c){ $colsByTable[$c['TABLE_NAME']][]=$c; }
$tableNames=array_map(fn($t)=>$t['TABLE_NAME'],$tables);

/* ============ 10 — ESQUEMA COMPLETO (DDL) ============ */
$o=[];
$o[]="# $LABEL · DB — Esquema completo (DDL)";
$o[]="";$o[]=$gen;$o[]="";
$o[]="Definición real de las **".count($tableNames)." tablas** (`SHOW CREATE TABLE`). ".
     "Sin claves foráneas declaradas; índices secundarios = ".
     "(ver `12-relaciones.md` / `13-volumetria-y-calidad.md`).";
$o[]="";
$o[]="## Índice de tablas";$o[]="";
$o[]="| Tabla | Engine | Colación | Filas (COUNT) | Comentario |";
$o[]="|---|---|---|---:|---|";
foreach($tables as $t){
  $tn=$t['TABLE_NAME']; $rc=$prof[$tn]['row_count']??'?';
  $o[]="| [`$tn`](#$tn) | {$t['ENGINE']} | ".md_esc($t['TABLE_COLLATION'])." | ".nf($rc)." | ".md_esc($t['TABLE_COMMENT']??'')." |";
}
$o[]="";
foreach($tables as $t){
  $tn=$t['TABLE_NAME'];
  $ddl=@file_get_contents("$EV/ddl/$tn.sql");
  $o[]="## $tn";$o[]="";
  $o[]="- Engine **{$t['ENGINE']}** · colación **".md_esc($t['TABLE_COLLATION'])."** · filas (COUNT) **".nf($prof[$tn]['row_count']??0)."** · ".
       "datos ".mb($t['DATA_LENGTH'])." · índices ".mb($t['INDEX_LENGTH']).
       (($t['AUTO_INCREMENT']??null)!==null ? " · AUTO_INCREMENT ".nf($t['AUTO_INCREMENT']) : "").
       (($t['UPDATE_TIME']??null) ? " · últ. modif. {$t['UPDATE_TIME']}" : "");
  $o[]="";
  $o[]="```sql";$o[]=trim((string)$ddl);$o[]="```";$o[]="";
}
file_put_contents("$OUT/10-esquema-completo.md",implode("\n",$o)."\n");

/* ============ 11 — DICCIONARIO DE DATOS (por columna) ============ */
$o=[];
$o[]="# $LABEL · DB — Diccionario de datos (por columna)";
$o[]="";$o[]=$gen;$o[]="";
$o[]="Para cada tabla: cada columna con tipo real, nullability, default, clave, **nº de valores distintos**, ".
     "nulos/vacíos, min/max (numéricos/fechas) y, para columnas de baja cardinalidad (≤50 distintos), ".
     "**la distribución completa de valores con su frecuencia**. Más abajo, filas de muestra reales.";
$o[]="";
$o[]="> Las contraseñas se enmascaran. Donde un cómputo no se pudo completar por tamaño (p. ej. ".
     "`COUNT(DISTINCT)` sobre texto libre de tablas de millones de filas), aparece `n/d`.";
$o[]="";
foreach($tables as $t){
  $tn=$t['TABLE_NAME'];
  $rc=$prof[$tn]['row_count']??0;
  $o[]="## $tn  ·  ".nf($rc)." filas";$o[]="";
  $o[]="| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |";
  $o[]="|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|";
  $i=0;
  foreach(($colsByTable[$tn]??[]) as $c){
    $i++;
    $cn=$c['COLUMN_NAME'];
    $p=$prof[$tn]['columns'][$cn]??[];
    $dist = array_key_exists('distinct',$p)&&$p['distinct']!==null ? nf($p['distinct']) : 'n/d';
    $nulls= array_key_exists('nulls',$p)&&$p['nulls']!==null ? nf($p['nulls']) : '—';
    $emp  = array_key_exists('empties',$p)&&$p['empties']!==null ? nf($p['empties']) : '—';
    $min  = array_key_exists('min',$p)&&$p['min']!==null ? md_esc(mb_substr((string)$p['min'],0,24)) : '—';
    $max  = array_key_exists('max',$p)&&$p['max']!==null ? md_esc(mb_substr((string)$p['max'],0,24)) : '—';
    $def  = $c['COLUMN_DEFAULT']===null ? '∅' : md_esc(mb_substr((string)$c['COLUMN_DEFAULT'],0,20));
    $o[]="| $i | `$cn` | ".md_esc($c['COLUMN_TYPE'])." | ".($c['IS_NULLABLE']==='YES'?'sí':'no')." | $def | ".
         ($c['COLUMN_KEY']?:'—')." | $dist | $nulls | $emp | $min | $max |";
  }
  $o[]="";
  // value distributions for low-card columns
  $hasDist=false; $db=[];
  foreach(($colsByTable[$tn]??[]) as $c){
    $cn=$c['COLUMN_NAME'];
    $p=$prof[$tn]['columns'][$cn]??[];
    if(!empty($p['values'])){
      $hasDist=true;
      $parts=[];
      foreach($p['values'] as $v){
        $val=$v['val']; $val=($val===null?'NULL':($val===''?'(vacío)':$val));
        $parts[]="`".md_esc(mb_substr((string)$val,0,40))."` ×".nf($v['n']);
      }
      $db[]="- **$cn** (".count($p['values'])." valores): ".implode(' · ',$parts);
    }
  }
  if($hasDist){ $o[]="**Distribución de valores (columnas de baja cardinalidad):**";$o[]="";foreach($db as $l)$o[]=$l; $o[]=""; }
  // samples
  $samp=jload("$EV/samples/$tn.json");
  if(is_array($samp)&&count($samp)){
    $o[]="<details><summary>Filas de muestra ($tn)</summary>";$o[]="";
    $keys=array_keys($samp[0]);
    $o[]="| ".implode(" | ",array_map(fn($k)=>md_esc($k),$keys))." |";
    $o[]="|".str_repeat("---|",count($keys));
    foreach($samp as $row){
      $cells=array_map(fn($k)=>md_esc(mb_substr((string)($row[$k]??''),0,60)),$keys);
      $o[]="| ".implode(" | ",$cells)." |";
    }
    $o[]="";$o[]="</details>";$o[]="";
  }
}
file_put_contents("$OUT/11-diccionario-de-datos.md",implode("\n",$o)."\n");

/* ============ 12 — RELACIONES ============ */
$o=[];
$o[]="# $LABEL · DB — Relaciones (lógicas, medidas)";
$o[]="";$o[]=$gen;$o[]="";
$o[]="**No hay claves foráneas declaradas** (0 FK) ni índices secundarios. Las relaciones son ".
     "*por convención*: la columna `operacion` enlaza las tablas satélite con `t_base`. ".
     "Las cardinalidades de abajo se midieron con `COUNT(*)` vs `COUNT(DISTINCT operacion)`.";
$o[]="";
$baseDistinct=$qual['t_base_distinct_operacion']??null;
if($baseDistinct!==null) $o[]="`t_base` tiene **".nf($baseDistinct)." operaciones distintas** (clave del dominio).";
$o[]="";
$o[]="## Cardinalidad real respecto a `operacion`";$o[]="";
$o[]="| Tabla | Filas | Operaciones distintas | Filas/operación | Interpretación |";
$o[]="|---|---:|---:|---:|---|";
foreach(($qual['duplicate_ratio_vs_t_base']??[]) as $t=>$v){
  $r=$v['rows_per_distinct'];
  $interp = $r===null ? '—' : ($r<=1.001 ? '1:1 (satélite)' : ($r<2 ? 'leve duplicación / multi-fila' : 'multi-fila por operación (log/histórico)'));
  $o[]="| `$t` | ".nf($v['rows_total'])." | ".nf($v['distinct_operacion'])." | ".($r===null?'—':$r.'×')." | $interp |";
}
$o[]="";
// lookup tables (no operacion col)
$lookup=[];
foreach($tableNames as $t){
  $hasOp=false; foreach(($colsByTable[$t]??[]) as $c){ if(strtolower($c['COLUMN_NAME'])==='operacion'){$hasOp=true;break;} }
  if(!$hasOp) $lookup[]=$t;
}
$o[]="## Tablas sin `operacion` (catálogos / globales)";$o[]="";
foreach($lookup as $t){ $o[]="- `$t` — ".nf($prof[$t]['row_count']??0)." filas"; }
$o[]="";
$o[]="> Diagrama lógico: `t_base` (centro) ←`operacion`→ satélites 1:1 (`t_asignacion`, `t_campana`, `t_cartera`, `t_decil`, `t_saldos`) y 1:N (`t_gestiones`, `t_resumen`, `t_acuerdos`, `t_telefonos`, `t_alertas`).";
file_put_contents("$OUT/12-relaciones.md",implode("\n",$o)."\n");

/* ============ 13 — VOLUMETRÍA Y CALIDAD ============ */
$o=[];
$o[]="# $LABEL · DB — Volumetría y calidad de datos";
$o[]="";$o[]=$gen;$o[]="";
$totRows=0;$totData=0;$totIdx=0;
foreach($tables as $t){ $totRows+=(int)($prof[$t['TABLE_NAME']]['row_count']??0); $totData+=(int)$t['DATA_LENGTH']; $totIdx+=(int)$t['INDEX_LENGTH']; }
$o[]="**Totales:** ".nf($totRows)." filas · ".mb($totData)." datos · ".mb($totIdx)." índices · ".count($tableNames)." tablas.";
$o[]="";
$o[]="## Volumetría por tabla";$o[]="";
$o[]="| Tabla | Filas (COUNT) | Filas (IS aprox) | Datos | Índices | AUTO_INCREMENT | Últ. modif. |";
$o[]="|---|---:|---:|---:|---:|---:|---|";
foreach($tables as $t){
  $tn=$t['TABLE_NAME'];
  $o[]="| `$tn` | ".nf($prof[$tn]['row_count']??0)." | ".nf($t['TABLE_ROWS'])." | ".mb($t['DATA_LENGTH'])." | ".mb($t['INDEX_LENGTH'])." | ".
       (($t['AUTO_INCREMENT']??null)!==null?nf($t['AUTO_INCREMENT']):'—')." | ".($t['UPDATE_TIME']??'—')." |";
}
$o[]="";
// empties
$empty=[]; foreach($tables as $t){ if((int)($prof[$t['TABLE_NAME']]['row_count']??0)===0) $empty[]=$t['TABLE_NAME']; }
$o[]="## Tablas vacías";$o[]="";
$o[]=count($empty)? "- ".implode("\n- ",array_map(fn($x)=>"`$x`",$empty)) : "_(ninguna)_";
$o[]="";
$o[]="## Calidad de datos (medida)";$o[]="";
$o[]="### Filas basura `operacion=0` (cabecera de CSV insertada como dato)";$o[]="";
$gz=array_filter($qual['garbage_operacion_zero']??[],fn($n)=>$n>0);
$o[]=count($gz)? "" : "_(ninguna)_";
if(count($gz)){ $o[]="| Tabla | Filas operacion=0 |";$o[]="|---|---:|"; foreach($gz as $t=>$n)$o[]="| `$t` | $n |"; }
$o[]="";
$o[]="### Contraseñas = `md5('0')` (password trivial)";$o[]="";
$o[]="| Columna | md5('0') | Total | % |";
$o[]="|---|---:|---:|---:|";
foreach(($qual['password_md5_zero']??[]) as $k=>$v){
  $pct=$v['total']>0?round(100*$v['md5_of_zero']/$v['total'],1):0;
  $o[]="| `$k` | {$v['md5_of_zero']} | {$v['total']} | {$pct}% |";
}
$o[]="";
$o[]="### Fechas inválidas `0000-00-00`";$o[]="";
$zd=$qual['zero_dates']??[];
if(count($zd)){ $o[]="| Columna | Filas |";$o[]="|---|---:|"; foreach($zd as $k=>$n)$o[]="| `$k` | ".nf($n)." |"; } else { $o[]="_(ninguna)_"; }
$o[]="";
$o[]="### Duplicación por operación (ver también `12-relaciones.md`)";$o[]="";
$o[]="| Tabla | Filas/operación | Lectura |";
$o[]="|---|---:|---|";
foreach(($qual['duplicate_ratio_vs_t_base']??[]) as $t=>$v){
  $r=$v['rows_per_distinct'];
  if($r!==null && $r>1.05){
    $note = $r>2 ? 'multi-fila esperada (log) o duplicados' : 'posibles duplicados por reimport';
    $o[]="| `$t` | {$r}× | $note |";
  }
}
file_put_contents("$OUT/13-volumetria-y-calidad.md",implode("\n",$o)."\n");

echo "OK $LABEL: db/10..13 generados en $OUT\n";
