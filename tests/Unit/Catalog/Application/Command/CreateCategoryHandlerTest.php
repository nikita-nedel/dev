<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Application\Command;

use App\Catalog\Application\Command\CreateCategory\CreateCategory;
use App\Catalog\Application\Command\CreateCategory\CreateCategoryHandler;
use App\Catalog\Application\Service\CategoryCreator;
use App\Catalog\Application\Service\CategorySlugService;
use App\Catalog\Domain\Model\Category;
use App\Tests\Support\PassthroughTransactionManager;
use PHPUnit\Framework\TestCase;

final class CreateCategoryHandlerTest extends TestCase
{
    public function testCreatesActiveRoot(): void
    {
        $categories = new InMemoryCategoryRepository();
        $handler = $this->handler($categories, 'elektronika');

        $category = $handler(new CreateCategory(null, ' Электроника ', icon: 'bi-phone', sortOrder: 1));

        self::assertSame('Электроника', $category->getName());
        self::assertSame('elektronika', $category->getSlug());
        self::assertSame('bi-phone', $category->getIcon());
        self::assertSame(1, $category->getSortOrder());
        self::assertTrue($category->isActive());
        self::assertTrue($category->isRoot());
        self::assertSame([$category], $categories->categories);
    }

    public function testAppendsSuffixWhenSlugIsTaken(): void
    {
        $categories = new InMemoryCategoryRepository();
        $categories->add(Category::create(null, 'Другое', 'elektronika'));

        $category = $this->handler($categories, 'elektronika')(new CreateCategory(null, 'Электроника'));

        self::assertSame('elektronika-2', $category->getSlug());
    }

    public function testBlocksSubcategoryWhenParentHasProducts(): void
    {
        $categories = new InMemoryCategoryRepository();
        $root = Category::create(null, 'Электроника', 'elektronika');
        $categories->add($root);
        $categories->productCounts[spl_object_id($root)] = 1;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('В категории уже есть товары');

        $this->handler($categories, 'umnyj-dom')(new CreateCategory($root, 'Умный дом'));
    }

    public function testRejectsDuplicateNameOnSameLevel(): void
    {
        $categories = new InMemoryCategoryRepository();
        $categories->add(Category::create(null, 'Электроника', 'elektronika'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('уже есть');

        $this->handler($categories, 'elektronika-2')(new CreateCategory(null, ' электроника '));
    }

    public function testRejectsInactiveParent(): void
    {
        $categories = new InMemoryCategoryRepository();
        $root = Category::create(null, 'Электроника', 'elektronika');
        $root->deactivate();
        $categories->add($root);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Родительская категория отключена.');

        $this->handler($categories, 'umnyj-dom')(new CreateCategory($root, 'Умный дом'));
    }

    private function handler(InMemoryCategoryRepository $categories, string $slug): CreateCategoryHandler
    {
        return new CreateCategoryHandler(
            new CategoryCreator(
                $categories,
                new CategorySlugService(new FakeCategorySlugGenerator($slug), $categories),
            ),
            new PassthroughTransactionManager(),
        );
    }
}
