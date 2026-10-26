<?php
declare(strict_types=1);

function medical_xlsx_escape(mixed $value): string
{
    $text = (string) $value;
    $text = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text) ?? '';
    return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** @param array{stream: resource, hash: HashContext, size: int} $sheet */
function medical_xlsx_write(array &$sheet, string $xml): void
{
    $length = strlen($xml);
    if (fwrite($sheet['stream'], $xml) !== $length) {
        throw new RuntimeException('Could not prepare the spreadsheet.');
    }
    hash_update($sheet['hash'], $xml);
    $sheet['size'] += $length;
}

/** @return resource */
function medical_xlsx_temp()
{
    $stream = fopen('php://temp/maxmemory:4194304', 'w+b');
    if ($stream === false) {
        throw new RuntimeException('Could not create the spreadsheet.');
    }
    return $stream;
}

function medical_xlsx_column(int $index): string
{
    $label = '';
    while ($index > 0) {
        $index--;
        $label = chr(65 + ($index % 26)) . $label;
        $index = intdiv($index, 26);
    }
    return $label;
}

/** @param resource $zip @param list<string> $directory @param resource $contents */
function medical_xlsx_add_entry($zip, array &$directory, int &$offset, string $name, $contents, int $size, int $crc): void
{
    $nameLength = strlen($name);
    $localHeader = pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 0, $crc, $size, $size, $nameLength, 0);
    if (fwrite($zip, $localHeader . $name) !== strlen($localHeader) + $nameLength ||
        stream_copy_to_stream($contents, $zip) !== $size) {
        throw new RuntimeException('Could not finish the spreadsheet.');
    }
    $directory[] = pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 0,
        $crc, $size, $size, $nameLength, 0, 0, 0, 0, 0, $offset) . $name;
    $offset += strlen($localHeader) + $nameLength + $size;
}

/** @param resource $zip @param list<string> $directory @param resource $contents */
function medical_xlsx_add_xml($zip, array &$directory, int &$offset, string $name, $contents): void
{
    rewind($contents);
    $size = fstat($contents)['size'];
    $context = hash_init('crc32b');
    hash_update_stream($context, $contents);
    $crc = (int) hexdec(hash_final($context));
    rewind($contents);
    medical_xlsx_add_entry($zip, $directory, $offset, $name, $contents, $size, $crc);
}

/**
 * Stream database rows into a standards-compliant XLSX without PHP's optional zip extension.
 * @param iterable<array<string,mixed>> $records Rows already restricted by dashboard permissions/search.
 */
function medical_xlsx_build(iterable $records)
{
    $headers = ['شماره پرونده', 'شعبه', 'نام بیمار', 'موبایل', 'نوع شناسه', 'کد ملی / فراگیر',
        'تاریخ تولد (میلادی)', 'سن', 'جنسیت', 'استان', 'شهر', 'نوع سرطان', 'وضعیت تشخیص', 'توضیحات', 'وضعیت پرونده', 'تاریخ ثبت'];
    $status = ['new' => 'جدید', 'reviewing' => 'در حال بررسی', 'contacted' => 'تماس گرفته شد', 'closed' => 'بسته شده'];
    $gender = ['male' => 'مرد', 'female' => 'زن', 'unspecified' => 'نامشخص'];
    $sheetFiles = [];
    $sheetNumber = 0;
    $rowNumber = 1;

    $startSheet = static function () use (&$sheetNumber, &$rowNumber, $headers): array {
        $sheetNumber++;
        $rowNumber = 1;
        $sheet = ['stream' => medical_xlsx_temp(), 'hash' => hash_init('crc32b'), 'size' => 0];
        medical_xlsx_write($sheet, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0" rightToLeft="1"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="20"/><cols>');
        for ($i = 1; $i <= count($headers); $i++) {
            $width = in_array($i, [6, 7, 14], true) ? 24 : 18;
            medical_xlsx_write($sheet, '<col min="' . $i . '" max="' . $i . '" width="' . $width . '" customWidth="1"/>');
        }
        medical_xlsx_write($sheet, '</cols><sheetData><row r="1" ht="28" customHeight="1">');
        foreach ($headers as $i => $header) {
            $cell = medical_xlsx_column($i + 1) . '1';
            medical_xlsx_write($sheet, '<c r="' . $cell . '" s="1" t="inlineStr"><is><t>' . medical_xlsx_escape($header) . '</t></is></c>');
        }
        medical_xlsx_write($sheet, '</row>');
        return $sheet;
    };

    $sheet = $startSheet();
    $count = 0;
    foreach ($records as $record) {
        if ($count > 0 && $count % 60000 === 0) {
            medical_xlsx_write($sheet, '</sheetData><autoFilter ref="A1:P' . $rowNumber . '"/><pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/></worksheet>');
            $sheetFiles[] = $sheet;
            $sheet = $startSheet();
        }
        $rowNumber++;
        $values = [$record['id'] ?? '', $record['branch_name'] ?? '', $record['full_name'] ?? '', $record['phone'] ?? '',
            !empty($record['is_foreign']) ? 'اتباع / کد فراگیر' : 'ایرانی / کد ملی',
            $record['identity_code'] ?? '', $record['birth_date'] ?? '', $record['age'] ?? '',
            $gender[$record['gender'] ?? ''] ?? ($record['gender'] ?? ''), $record['province'] ?? '', $record['city'] ?? '',
            $record['cancer_type'] ?? '', $record['diagnosis_status'] ?? '', $record['description'] ?? '',
            $status[$record['status'] ?? ''] ?? ($record['status'] ?? ''), $record['created_at'] ?? ''];
        medical_xlsx_write($sheet, '<row r="' . $rowNumber . '">');
        foreach ($values as $i => $value) {
            $reference = medical_xlsx_column($i + 1) . $rowNumber;
            medical_xlsx_write($sheet, '<c r="' . $reference . '" t="inlineStr"><is><t xml:space="preserve">'
                . medical_xlsx_escape($value) . '</t></is></c>');
        }
        medical_xlsx_write($sheet, '</row>');
        $count++;
    }
    medical_xlsx_write($sheet, '</sheetData><autoFilter ref="A1:P' . $rowNumber . '"/><pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/></worksheet>');
    $sheetFiles[] = $sheet;

    $sheetNames = [];
    foreach ($sheetFiles as $i => $unused) {
        $sheetNames[] = '<sheet name="پرونده‌های پزشکی ' . ($i + 1) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
    }
    $contentTypes = '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
    foreach ($sheetFiles as $i => $unused) {
        $contentTypes .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
    }
    $contentTypes .= '</Types>';
    $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>'
        . implode('', $sheetNames) . '</sheets></workbook>';
    $workbookRels = '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    foreach ($sheetFiles as $i => $unused) {
        $workbookRels .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
    }
    $workbookRels .= '<Relationship Id="rId' . (count($sheetFiles) + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    $rootRels = '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Aptos"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Aptos"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF008F8A"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';

    $zip = medical_xlsx_temp();
    $directory = [];
    $offset = 0;
    foreach ([['[Content_Types].xml', $contentTypes], ['_rels/.rels', $rootRels], ['xl/workbook.xml', $workbook],
        ['xl/_rels/workbook.xml.rels', $workbookRels], ['xl/styles.xml', $styles]] as [$name, $xml]) {
        $contents = medical_xlsx_temp();
        fwrite($contents, $xml);
        medical_xlsx_add_xml($zip, $directory, $offset, $name, $contents);
        fclose($contents);
    }
    foreach ($sheetFiles as $i => $file) {
        rewind($file['stream']);
        medical_xlsx_add_entry($zip, $directory, $offset, 'xl/worksheets/sheet' . ($i + 1) . '.xml',
            $file['stream'], $file['size'], (int) hexdec(hash_final($file['hash'])));
        fclose($file['stream']);
    }
    $directoryOffset = $offset;
    foreach ($directory as $record) {
        fwrite($zip, $record);
        $offset += strlen($record);
    }
    $directorySize = $offset - $directoryOffset;
    $entries = count($directory);
    fwrite($zip, pack('VvvvvVVv', 0x06054b50, 0, 0, $entries, $entries, $directorySize, $directoryOffset, 0));

    rewind($zip);
    return $zip;
}

/** @param iterable<array<string,mixed>> $records */
function medical_export_xlsx(iterable $records): void
{
    $zip = medical_xlsx_build($records);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="medical-records-' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');
    fpassthru($zip);
    fclose($zip);
    exit;
}
