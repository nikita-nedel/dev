<?php

declare(strict_types=1);

namespace App\Enum\Export;

enum ExportStorage: string
{
    case Local = 'local';
    case S3 = 's3';

    public function getLabel(): string
    {
        return match ($this) {
            self::Local => 'В проекте',
            self::S3 => 'S3',
        };
    }
}
