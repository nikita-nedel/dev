<?php

declare(strict_types=1);

namespace App\Export\Domain\Model;

enum ExportResource: string
{
    case Clients = 'clients';
}
