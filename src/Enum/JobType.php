<?php

declare(strict_types=1);

namespace App\Enum;

enum JobType: string
{
    case Export = 'export';
    case ClientPasswordProcessing = 'client_password_processing';

    public function getLabel(): string
    {
        return match ($this) {
            self::Export => 'Экспорт',
            self::ClientPasswordProcessing => 'Обработка паролей клиентов',
        };
    }
}
