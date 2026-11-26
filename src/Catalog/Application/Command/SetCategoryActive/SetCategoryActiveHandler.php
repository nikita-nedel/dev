<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\SetCategoryActive;

use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class SetCategoryActiveHandler
{
    public function __construct(
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(SetCategoryActive $command): void
    {
        $category = $command->category;

        if ($command->active && null !== $category->getParent() && !$category->getParent()->isActive()) {
            throw new \RuntimeException('Сначала включите родительскую категорию.');
        }

        if (!$command->active) {
            foreach ($category->getChildren() as $child) {
                if ($child->isActive()) {
                    throw new \RuntimeException('Сначала отключите подкатегории.');
                }
            }
        }

        $this->transactions->transactional(static function () use ($category, $command): void {
            if ($command->active) {
                $category->activate();
            } else {
                $category->deactivate();
            }
        });
    }
}
