<?php

declare(strict_types=1);

namespace App\Export\Infrastructure\Writer;

use App\Export\Application\Dto\ExportContext;
use App\Export\Application\Port\ExportWriterInterface;
use App\Export\Domain\Model\ExportFormat;
use App\Export\Domain\Model\ExportResource;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class XlsxExportWriter implements ExportWriterInterface
{
    public function supports(ExportFormat $format): bool
    {
        return ExportFormat::Xlsx === $format;
    }

    public function writeToFile(iterable $rows, array $headers, ExportContext $context, string $targetPath): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($this->sheetTitle($context->resource));

        $sheet->fromArray($headers, null, 'A1');

        $rowIndex = 2;
        foreach ($rows as $row) {
            $sheet->fromArray($row, null, 'A' . $rowIndex);
            ++$rowIndex;
        }

        $highestColumn = $sheet->getHighestColumn();
        $headerRange = 'A1:' . $highestColumn . '1';

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '667EEA'],
            ],
        ]);

        foreach (range('A', $highestColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        try {
            new Xlsx($spreadsheet)->save($targetPath);
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function sheetTitle(ExportResource $resource): string
    {
        return match ($resource) {
            ExportResource::Clients => 'Клиенты',
        };
    }
}
