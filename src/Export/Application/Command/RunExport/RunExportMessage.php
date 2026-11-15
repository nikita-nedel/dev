<?php

declare(strict_types=1);

namespace App\Export\Application\Command\RunExport;

/**
 * Сообщение очереди: «собери файл для такой-то выгрузки».
 *
 * Передаём только идентификатор — остальное воркер читает из БД. Так сообщение
 * не устаревает, если фильтры или формат успели поменять.
 */
final readonly class RunExportMessage
{
    public function __construct(
        public string $exportId,
    ) {
    }
}
