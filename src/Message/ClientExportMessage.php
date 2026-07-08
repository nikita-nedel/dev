<?php

declare(strict_types=1);

namespace App\Message;

final readonly class ClientExportMessage
{
    public function __construct(
        public string $exportId,
    ) {
    }
}
