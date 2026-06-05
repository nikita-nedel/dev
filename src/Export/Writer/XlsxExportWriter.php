<?php

declare(strict_types=1);

namespace App\Export\Writer;

use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportResource;
use App\Export\Contract\ExportWriterInterface;
use App\Export\ExportContext;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final class XlsxExportWriter implements ExportWriterInterface
{
    public function supports(ExportFormat $format): bool
    {
        return ExportFormat::Xlsx === $format;
    }

    public function write(iterable $rows, array $headers, ExportContext $context): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Клиенты');

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

        $path = $this->createTempPath('.xlsx');

        try {
            new Xlsx($spreadsheet)->save($path);
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->buildFilename($context),
        );
        $response->headers->set('Content-Type', ExportFormat::Xlsx->getMimeType());
        $response->deleteFileAfterSend(true);

        return $response;
    }

    private function createTempPath(string $extension): string
    {
        $base = tempnam(sys_get_temp_dir(), 'export_');
        if (false === $base) {
            throw new \RuntimeException('Cannot create temporary export file.');
        }

        $path = $base . $extension;
        if (!rename($base, $path)) {
            @unlink($base);
            throw new \RuntimeException('Cannot prepare temporary export file.');
        }

        return $path;
    }

    private function buildFilename(ExportContext $context): string
    {
        $prefix = match ($context->resource) {
            ExportResource::Clients => 'clients',
        };

        return sprintf('%s_%s.xlsx', $prefix, new \DateTimeImmutable()->format('Y-m-d_His'));
    }
}
