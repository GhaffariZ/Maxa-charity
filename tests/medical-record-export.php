<?php
declare(strict_types=1);

require __DIR__ . '/../public_html/dashboard/medical-record-export.php';

$row = [
    'id' => 1, 'branch_name' => 'شعبه آزمایشی', 'full_name' => '=1+1', 'phone' => '09123456789',
    'identity_code' => '0012345678', 'birth_date' => '1991-10-07', 'is_foreign' => 0, 'age' => 0,
    'gender' => 'female', 'province' => 'تهران', 'city' => 'تهران', 'cancer_type' => 'نمونه',
    'diagnosis_status' => '', 'description' => "شرح <آزمایش> & نمونه\x01", 'status' => 'new',
    'created_at' => '2026-10-26 12:30:00',
];

function expect_export(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}

function workbook_sheet(array $rows): string {
    $stream = medical_xlsx_build($rows);
    $zip = stream_get_contents($stream);
    fclose($stream);
    expect_export(str_starts_with($zip, "PK\x03\x04"), 'Download must be a ZIP-based XLSX workbook');
    $offset = 0;
    while (substr($zip, $offset, 4) === "PK\x03\x04") {
        $header = unpack('Vsignature/vversion/vflags/vmethod/vtime/vdate/Vcrc/Vcompressed/Vsize/vname/vextra', substr($zip, $offset, 30));
        $name = substr($zip, $offset + 30, $header['name']);
        $bodyOffset = $offset + 30 + $header['name'] + $header['extra'];
        if ($name === 'xl/worksheets/sheet1.xml') return substr($zip, $bodyOffset, $header['size']);
        $offset = $bodyOffset + $header['compressed'];
    }
    throw new RuntimeException('First worksheet was missing');
}

$xml = workbook_sheet(array_fill(0, 201, $row));
$doc = new DOMDocument();
expect_export($doc->loadXML($xml), 'Worksheet must be valid XML');
$xpath = new DOMXPath($doc);
$xpath->registerNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
expect_export($xpath->query('//*[local-name()="row"]')->length === 202, 'Export is not capped at 200 records');
expect_export($xpath->query('//*[local-name()="row"][1]/*[local-name()="c"]')->length === 16, 'All column headers');
expect_export($xpath->query('//*[local-name()="t" and text()="0012345678"]')->length === 201, 'Leading zeros preserved');
expect_export($xpath->query('//*[local-name()="t" and text()="=1+1"]')->length === 201, 'Formula-like input is literal text');
expect_export($xpath->query('//*[local-name()="f"]')->length === 0, 'No formulas from user content');
expect_export(str_contains($xml, '&lt;آزمایش&gt; &amp; نمونه'), 'XML escaping');
expect_export(!str_contains($xml, "\x01"), 'Invalid XML control characters removed');

expect_export($doc->loadXML(workbook_sheet([])), 'Empty worksheet is valid');

// Existing records may predate the identity/birth-date migration.
unset($row['identity_code'], $row['is_foreign'], $row['birth_date']);
 $legacy = workbook_sheet([$row]);
expect_export($doc->loadXML($legacy), 'Legacy record export');
echo "medical record Excel export: OK\n";
