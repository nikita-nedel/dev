<?php

declare(strict_types=1);

namespace App\Export\Contract;

use App\Enum\Export\ExportFormat;
use App\Export\ExportContext;
use Symfony\Component\HttpFoundation\Response;

interface ExportWriterInterface
{
    public function supports(ExportFormat $format): bool;

    /**
     * @param list<string> $headers
     * @param iterable<list<mixed>> $rows
     */
    public function write(iterable $rows, array $headers, ExportContext $context): Response;

    /**
     * @param list<string> $headers
     * @param iterable<list<mixed>> $rows
     */
    public function writeToFile(iterable $rows, array $headers, ExportContext $context, string $targetPath): void;
}
