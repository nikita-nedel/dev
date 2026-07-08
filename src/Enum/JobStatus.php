<?php

declare(strict_types=1);

namespace App\Enum;

enum JobStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'В очереди',
            self::Processing => 'Выполняется',
            self::Completed => 'Готово',
            self::Failed => 'Ошибка',
        };
    }
}
