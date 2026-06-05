<?php

declare(strict_types=1);

namespace App\Enum\Export;

enum ExportFormat: string
{
    case Xlsx = 'xlsx';

    public function getMimeType(): string
    {
        return match ($this) {
            self::Xlsx => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
    }

    public function getFileExtension(): string
    {
        return $this->value;
    }
}
