<?php
/**
 * =============================================================================
 * Reports - Excel (.xlsx) Export
 * =============================================================================
 * Exports any report as a true Microsoft Excel .xlsx workbook generated with
 * the Open Office XML format (no external library needed, only ext-zip).
 * Because the file is a real .xlsx, Excel opens it without any format warning.
 *
 * Usage: export_excel.php?type=inventory|faulty|working|lab|activity|issues[&lab_id=&from=&to=&user_id=&status=]
 * =============================================================================
 */

require_once __DIR__ . '/../includes/auth.php';
require_login();
require_can('export_reports');
require_once __DIR__ . '/_data.php';

$type = $_GET['type'] ?? 'inventory';

if ($type === 'issues') {
    $rows    = issues_report();
    $columns = issues_report_columns();
} elseif ($type === 'activity') {
    require_can('view_logs');
    $rows    = activity_report();
    $columns = activity_report_columns();
} else {
    $rows    = computer_report($type);
    $columns = computer_report_columns();
}

$map = [
    'inventory'   => 'Inventory_Report',
    'faulty'      => 'Not_Working_PCs',
    'working'     => 'Working_PCs',
    'maintenance' => 'Has_Some_Issues_PCs',
    'lab'         => 'Lab_Report',
    'activity'    => 'User_Activity',
    'issues'      => 'Issues_History',
];
$filename = ($map[$type] ?? 'Report') . '_' . date('Y-m-d') . '.xlsx';

// -----------------------------------------------------------------------------
// Small Open XML helpers.
// -----------------------------------------------------------------------------

/**
 * Convert a 1-based column number into an Excel column letter (A, B, ..., Z, AA...).
 */
function xlsx_col_name($n)
{
    $name = '';
    while ($n > 0) {
        $mod = ($n - 1) % 26;
        $name = chr(65 + $mod) . $name;
        $n = (int)(($n - 1) / 26);
    }
    return $name;
}

/**
 * Reasonable column widths so the file is readable when opened.
 */
function xlsx_col_widths(array $columns)
{
    $map = [
        'S No' => 6, 'Ticket ID' => 10, 'Created By' => 14, 'Issue Date' => 12,
        'Issue Time' => 10, 'Lab' => 12, 'PC Number' => 12, 'Issue' => 40,
        'Fixed By' => 14, 'Fix Date' => 12, 'Fix Time' => 10, 'Solution' => 40,
        'Status' => 12, 'Computer ID' => 12, 'Description' => 40, 'Remarks' => 30,
    ];
    $widths = [];
    foreach ($columns as $col) {
        $widths[] = $map[$col] ?? 14;
    }
    return $widths;
}

// -----------------------------------------------------------------------------
// Build the worksheet XML.
// -----------------------------------------------------------------------------

$numCols  = count($columns);
$titleRow = ($type === 'issues') ? 1 : 0; // 1 extra row when a title is present
$headerRowNum = $titleRow + 1;
$lastRow  = $headerRowNum + count($rows);

$titleColor  = 'FF008080'; // dark teal, accent 1 (title bar)
$headerColor = 'FF808080'; // white, background 1, darker 50% (header row)

$sheet  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
$sheet .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
$sheet .= '<dimension ref="A1:' . xlsx_col_name($numCols) . $lastRow . '"/>';

// Column widths.
$widths = xlsx_col_widths($columns);
$sheet .= '<cols>';
foreach ($widths as $i => $w) {
    $sheet .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';
}
$sheet .= '</cols>';

$sheet .= '<sheetData>';

// Title row (issues export only), merged across all columns.
if ($titleRow) {
    $sheet .= '<row r="1" ht="34" customHeight="1">';
    $sheet .= '<c r="A1" s="1" t="inlineStr"><is><t>Computer Issue Report</t></is></c>';
    $sheet .= '</row>';
}

// Header row.
$sheet .= '<row r="' . $headerRowNum . '" ht="22" customHeight="1">';
foreach ($columns as $i => $col) {
    $ref = xlsx_col_name($i + 1) . $headerRowNum;
    $sheet .= '<c r="' . $ref . '" s="2" t="inlineStr"><is><t>' . e($col) . '</t></is></c>';
}
$sheet .= '</row>';

// Data rows.
$r = $headerRowNum + 1;
foreach ($rows as $row) {
    $sheet .= '<row r="' . $r . '">';
    foreach ($columns as $i => $col) {
        $ref = xlsx_col_name($i + 1) . $r;
        $sheet .= '<c r="' . $ref . '" s="3" t="inlineStr"><is><t>' . e($row[$col] ?? '') . '</t></is></c>';
    }
    $sheet .= '</row>';
    $r++;
}

$sheet .= '</sheetData>';

// Merge the title row across all columns.
if ($titleRow) {
    $sheet .= '<mergeCells count="1"><mergeCell ref="A1:' . xlsx_col_name($numCols) . '1"/></mergeCells>';
}

$sheet .= '</worksheet>';

// -----------------------------------------------------------------------------
// Static Open XML parts.
// -----------------------------------------------------------------------------

$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    . '<Default Extension="xml" ContentType="application/xml"/>'
    . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
    . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
    . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
    . '</Types>';

$rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
    . '</Relationships>';

$workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
    . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
    . '<sheets><sheet name="Report" sheetId="1" r:id="rId1"/></sheets>'
    . '</workbook>';

$workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
    . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
    . '</Relationships>';

$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
    . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
    . '<fonts count="3">'
    . '<font><sz val="11"/><name val="Calibri"/></font>'
    . '<font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
    . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
    . '</fonts>'
    . '<fills count="4">'
    . '<fill><patternFill patternType="none"/></fill>'
    . '<fill><patternFill patternType="gray125"/></fill>'
    . '<fill><patternFill patternType="solid"><fgColor rgb="' . $titleColor . '"/><bgColor indexed="64"/></patternFill></fill>'
    . '<fill><patternFill patternType="solid"><fgColor rgb="' . $headerColor . '"/><bgColor indexed="64"/></patternFill></fill>'
    . '</fills>'
    . '<borders count="2">'
    . '<border><left/><right/><top/><bottom/><diagonal/></border>'
    . '<border><left style="thin"><color rgb="FF000000"/></left><right style="thin"><color rgb="FF000000"/></right><top style="thin"><color rgb="FF000000"/></top><bottom style="thin"><color rgb="FF000000"/></bottom><diagonal/></border>'
    . '</borders>'
    . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
    . '<cellXfs count="4">'
    . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
    . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
    . '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
    . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="center" wrapText="1"/></xf>'
    . '</cellXfs>'
    . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
    . '</styleSheet>';

// -----------------------------------------------------------------------------
// Bundle the parts into a real .xlsx (a zip archive).
// -----------------------------------------------------------------------------

$zip = new ZipArchive();
$tmp = tempnam(sys_get_temp_dir(), 'xlsx');
$zip->open($tmp, ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml', $contentTypes);
$zip->addFromString('_rels/.rels', $rootRels);
$zip->addFromString('xl/workbook.xml', $workbook);
$zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
$zip->addFromString('xl/styles.xml', $styles);
$zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
$zip->close();

// Prevent any further output from breaking the file.
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmp));
header('Cache-Control: no-cache, no-store, must-revalidate');

readfile($tmp);
unlink($tmp);
exit;
