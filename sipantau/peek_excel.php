<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$inputFileType = 'Xlsx';
$inputFileName = './usaha sbr.xlsx';

echo "Loading headers...\n";
$reader = IOFactory::createReader($inputFileType);
$reader->setReadDataOnly(true);

// Read only the first row
$chunkFilter = new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool {
        if ($row <= 5) {
            return true;
        }
        return false;
    }
};
$reader->setReadFilter($chunkFilter);

$spreadsheet = $reader->load($inputFileName);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

echo "First 5 rows:\n";
print_r(array_slice($rows, 0, 5));
