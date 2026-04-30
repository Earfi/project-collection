<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

final class ItemExcelImporter
{
    private const MAX_DATA_ROWS = 500;

    /** @var array<string, list<string>> */
    private const HEADER_ALIASES = [
        'type' => ['type', 'ประเภท'],
        'scale' => ['scale', 'สเกล'],
        'maker' => ['maker', 'ผู้ผลิต'],
        'subject_brand' => ['subject_brand', 'subject brand', 'แบรนด์หลัก', 'brand'],
        'name' => ['name', 'ชื่อ'],
        'description' => ['description', 'รายละเอียด', 'desc'],
        'price' => ['price', 'ราคา'],
        'qty' => ['qty', 'จำนวน', 'quantity'],
        'collected_at' => ['collected_at', 'collected', 'วันที่ได้มา', 'วันที่'],
        'image_focus_x' => ['image_focus_x', 'focus x', 'focus_x'],
        'image_focus_y' => ['image_focus_y', 'focus y', 'focus_y'],
    ];

    /**
     * @return array{items: list<array{fields: array<string, mixed>, image_bytes: ?string}>, messages: list<string>}
     */
    public function parse(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        if ($path === false) {
            throw new RuntimeException('Could not read upload.');
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $columnByField = $this->mapHeaderRow($sheet);
        if (! isset($columnByField['name'], $columnByField['type'])) {
            throw new RuntimeException('แถวแรกต้องมีหัวคอลัมน์ name (ชื่อ) และ type (ประเภท)');
        }

        $imagesByRow = $this->collectImagesByRow($sheet);

        $items = [];
        $messages = [];
        $highestRow = min((int) $sheet->getHighestDataRow(), self::MAX_DATA_ROWS + 1);

        for ($row = 2; $row <= $highestRow; $row++) {
            $name = $this->stringCell($sheet, $row, $columnByField['name'] ?? null);
            if ($name === '') {
                continue;
            }

            $type = $this->stringCell($sheet, $row, $columnByField['type'] ?? null);
            if ($type === '') {
                $messages[] = "แถว {$row}: มีชื่อแต่ไม่มีประเภท ข้าม";

                continue;
            }

            $fields = [
                'type' => $type,
                'scale' => $this->nullableString($sheet, $row, $columnByField['scale'] ?? null),
                'maker' => $this->nullableString($sheet, $row, $columnByField['maker'] ?? null),
                'subject_brand' => $this->nullableString($sheet, $row, $columnByField['subject_brand'] ?? null),
                'name' => $name,
                'description' => $this->nullableString($sheet, $row, $columnByField['description'] ?? null),
                'price' => $this->decimalCell($sheet, $row, $columnByField['price'] ?? null),
                'qty' => $this->intCell($sheet, $row, $columnByField['qty'] ?? null),
                'collected_at' => $this->dateCell($sheet, $row, $columnByField['collected_at'] ?? null),
                'image_focus_x' => $this->focusCell($sheet, $row, $columnByField['image_focus_x'] ?? null, 50),
                'image_focus_y' => $this->focusCell($sheet, $row, $columnByField['image_focus_y'] ?? null, 50),
            ];

            $imageBytes = $imagesByRow[$row] ?? null;
            $items[] = ['fields' => $fields, 'image_bytes' => $imageBytes];
        }

        if ($items === []) {
            $messages[] = 'ไม่พบแถวข้อมูล (ต้องมีชื่อและประเภทอย่างน้อย 1 แถวจากแถวที่ 2 เป็นต้นไป)';
        }

        return ['items' => $items, 'messages' => $messages];
    }

    public function writeTemplateToPath(string $path): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Items');

        $headers = [
            'A' => 'แทรกรูปที่เซลล์นี้แถวเดียวกับข้อมูล',
            'B' => 'type',
            'C' => 'scale',
            'D' => 'maker',
            'E' => 'subject_brand',
            'F' => 'name',
            'G' => 'description',
            'H' => 'price',
            'I' => 'qty',
            'J' => 'collected_at',
            'K' => 'image_focus_x',
            'L' => 'image_focus_y',
        ];
        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col.'1', $text);
        }
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setWidth($col === 'A' ? 28 : 14);
        }

        $mockRows = [
            ['Figure', '1/144', 'Bandai', 'Gundam', 'RX-78-2', 'ตัวอย่าง mock — กันดั้มคลาสสิก', 890.00, 1, '2024-01-10', 50, 50],
            ['Figure', '1/100', 'Bandai', 'Gundam', 'Barbatos', 'ตัวอย่าง mock — ไอรอนฟอร์ม', 1250.00, 1, '2024-01-18', 52, 48],
            ['Figure', '1/7', 'Good Smile', 'Nendoroid', 'Rem', 'ตัวอย่าง mock — ด๊อยจ์', 1650.00, 2, '2024-02-01', 50, 45],
            ['Figure', '1/8', 'Alter', 'Fate', 'Saber', 'ตัวอย่าง mock — scale figure', 3200.00, 1, '2024-02-14', 48, 50],
            ['Model Kit', '1/72', 'Tamiya', 'Military', 'Spitfire Mk.IX', 'ตัวอย่าง mock — เครื่องบิน', 780.00, 1, '2024-03-05', 50, 50],
            ['Model Kit', '1/35', 'Tamiya', 'Military', 'Tiger I', 'ตัวอย่าง mock — รถถัง', 1450.00, 1, '2024-03-12', 50, 52],
            ['Plush', '-', 'San-X', 'Rilakkuma', 'Rilakkuma M', 'ตัวอย่าง mock — ตุ๊กตา', 590.00, 3, '2024-04-01', 50, 50],
            ['Book', '-', 'Shueisha', 'One Piece', 'Vol. 100', 'ตัวอย่าง mock — มังงะ', 195.00, 1, '2024-04-20', 50, 50],
            ['Figure', '1/12', 'Max Factory', 'Figma', 'Link BOTW', 'ตัวอย่าง mock — figma', 2100.00, 1, '2024-05-08', 50, 48],
            ['Figure', '1/6', 'Hot Toys', 'Marvel', 'Iron Man MK85', 'ตัวอย่าง mock — premium', 12900.00, 1, '2024-06-01', 50, 50],
        ];

        $startRow = 2;
        foreach ($mockRows as $i => $cells) {
            $r = $startRow + $i;
            [$type, $scale, $maker, $subjectBrand, $name, $description, $price, $qty, $collected, $fx, $fy] = $cells;
            $sheet->setCellValue('B'.$r, $type);
            $sheet->setCellValue('C'.$r, $scale);
            $sheet->setCellValue('D'.$r, $maker);
            $sheet->setCellValue('E'.$r, $subjectBrand);
            $sheet->setCellValue('F'.$r, $name);
            $sheet->setCellValue('G'.$r, $description);
            $sheet->setCellValue('H'.$r, $price);
            $sheet->setCellValue('I'.$r, $qty);
            $sheet->setCellValue('J'.$r, $collected);
            $sheet->setCellValue('K'.$r, $fx);
            $sheet->setCellValue('L'.$r, $fy);
        }

        $noteRow = $startRow + count($mockRows);
        $sheet->setCellValue('A'.$noteRow, '← แทรกรูปที่คอลัมน์ A แถวเดียวกับข้อมูลได้ (ไม่บังคับ) | แถวด้านบนเป็นตัวอย่าง — ลบหรือแก้ก่อนนำเข้าจริง');
        $sheet->mergeCells('A'.$noteRow.':L'.$noteRow);
        $sheet->getStyle('A'.$noteRow)->getFont()->setItalic(true);
        $sheet->getStyle('A'.$noteRow)->getFont()->getColor()->setRGB('6B7280');

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
    }

    /** @return array<string, int> field => 1-based column index */
    private function mapHeaderRow(Worksheet $sheet): array
    {
        $highestColumn = $sheet->getHighestColumn(1);
        $maxCol = Coordinate::columnIndexFromString($highestColumn);
        $map = [];

        for ($colIndex = 1; $colIndex <= $maxCol; $colIndex++) {
            $letter = Coordinate::stringFromColumnIndex($colIndex);
            $raw = $sheet->getCell($letter.'1')->getValue();
            $headerText = is_scalar($raw) || $raw === null
                ? trim((string) $raw)
                : trim((string) $sheet->getCell($letter.'1')->getFormattedValue());
            if ($headerText === '') {
                continue;
            }

            $field = $this->resolveField($headerText);
            if ($field !== null) {
                $map[$field] = $colIndex;
            }
        }

        return $map;
    }

    private function resolveField(string $headerCell): ?string
    {
        $raw = trim($headerCell);
        foreach (self::HEADER_ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                if (strcasecmp($raw, $alias) === 0) {
                    return $field;
                }
            }
        }

        return null;
    }

    /** @return array<int, string> row (1-based) => raw image bytes */
    private function collectImagesByRow(Worksheet $sheet): array
    {
        $out = [];
        $collections = [
            iterator_to_array($sheet->getDrawingCollection()->getIterator()),
            iterator_to_array($sheet->getInCellDrawingCollection()->getIterator()),
        ];

        foreach ($collections as $drawings) {
            foreach ($drawings as $drawing) {
                if (! $drawing instanceof BaseDrawing) {
                    continue;
                }

                $bytes = $this->drawingToBytes($drawing);
                if ($bytes === null || $bytes === '') {
                    continue;
                }

                try {
                    [, $row] = Coordinate::indexesFromString($drawing->getCoordinates());
                } catch (\Throwable) {
                    continue;
                }

                if ($row <= 1) {
                    continue;
                }

                if (! isset($out[$row])) {
                    $out[$row] = $bytes;
                }
            }
        }

        return $out;
    }

    private function drawingToBytes(BaseDrawing $drawing): ?string
    {
        if ($drawing instanceof MemoryDrawing) {
            $res = $drawing->getImageResource();
            if ($res === null) {
                return null;
            }
            ob_start();
            $fn = $drawing->getRenderingFunction();
            if ($fn === 'imagejpeg') {
                imagejpeg($res, null, 92);
            } elseif ($fn === 'imagegif') {
                imagegif($res);
            } else {
                imagepng($res);
            }

            return ob_get_clean() ?: null;
        }

        if ($drawing instanceof Drawing) {
            $path = $drawing->getPath();
            if ($path === '' || ! is_readable($path)) {
                return null;
            }

            $data = @file_get_contents($path);

            return $data !== false ? $data : null;
        }

        return null;
    }

    private function stringCell(Worksheet $sheet, int $row, ?int $colIndex): string
    {
        if ($colIndex === null) {
            return '';
        }
        $coord = Coordinate::stringFromColumnIndex($colIndex).$row;
        $cell = $sheet->getCell($coord);
        $value = $cell->getCalculatedValue();

        if ($value === null) {
            return '';
        }
        if (is_string($value) || is_numeric($value)) {
            return trim((string) $value);
        }

        return trim((string) $cell->getFormattedValue());
    }

    private function nullableString(Worksheet $sheet, int $row, ?int $colIndex): ?string
    {
        $s = $this->stringCell($sheet, $row, $colIndex);

        return $s === '' ? null : $s;
    }

    private function decimalCell(Worksheet $sheet, int $row, ?int $colIndex): float
    {
        if ($colIndex === null) {
            return 0.0;
        }
        $coord = Coordinate::stringFromColumnIndex($colIndex).$row;
        $value = $sheet->getCell($coord)->getCalculatedValue();
        if ($value === null || $value === '') {
            return 0.0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }

    private function intCell(Worksheet $sheet, int $row, ?int $colIndex): int
    {
        if ($colIndex === null) {
            return 1;
        }
        $coord = Coordinate::stringFromColumnIndex($colIndex).$row;
        $value = $sheet->getCell($coord)->getCalculatedValue();
        if ($value === null || $value === '') {
            return 1;
        }
        if (is_numeric($value)) {
            return max(0, (int) $value);
        }

        return 1;
    }

    private function focusCell(Worksheet $sheet, int $row, ?int $colIndex, int $default): int
    {
        if ($colIndex === null) {
            return $default;
        }
        $coord = Coordinate::stringFromColumnIndex($colIndex).$row;
        $value = $sheet->getCell($coord)->getCalculatedValue();
        if ($value === null || $value === '') {
            return $default;
        }
        if (is_numeric($value)) {
            return max(0, min(100, (int) $value));
        }

        return $default;
    }

    private function dateCell(Worksheet $sheet, int $row, ?int $colIndex): ?string
    {
        if ($colIndex === null) {
            return null;
        }
        $coord = Coordinate::stringFromColumnIndex($colIndex).$row;
        $cell = $sheet->getCell($coord);
        $value = $cell->getCalculatedValue();

        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && Date::isDateTime($cell)) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        if (is_string($value)) {
            $trim = trim($value);
            $ts = strtotime($trim);

            return $ts !== false ? date('Y-m-d', $ts) : null;
        }

        return null;
    }
}
