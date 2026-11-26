<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http;

use App\Catalog\Application\Command\CreateCategory\CreateCategory;
use App\Catalog\Application\Command\CreateCategory\CreateCategoryHandler;
use App\Catalog\Application\Command\SetCategoryActive\SetCategoryActive;
use App\Catalog\Application\Command\SetCategoryActive\SetCategoryActiveHandler;
use App\Catalog\Application\Command\UpdateCategory\UpdateCategory;
use App\Catalog\Application\Command\UpdateCategory\UpdateCategoryHandler;
use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryRequestRepositoryInterface;
use App\Catalog\Presentation\Http\Form\CategoryFormData;
use App\Catalog\Presentation\Http\Form\CategoryFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/categories', name: 'app_admin_category_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminCategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly CategoryRequestRepositoryInterface $requests,
        private readonly CreateCategoryHandler $createCategory,
        private readonly UpdateCategoryHandler $updateCategory,
        private readonly SetCategoryActiveHandler $setActive,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $roots = $this->categories->findRootsWithChildren();
        $productCounts = $this->categories->countProductsByCategoryId();
        $rootTotals = [];

        foreach ($roots as $root) {
            $total = $productCounts[$root->getId()] ?? 0;

            foreach ($root->getChildren() as $child) {
                $total += $productCounts[$child->getId()] ?? 0;
            }

            $rootTotals[$root->getId()] = $total;
        }

        return $this->render('admin/categories/index.html.twig', [
            'admin_menu' => 'categories',
            'roots' => $roots,
            'productCounts' => $productCounts,
            'rootTotals' => $rootTotals,
            'pendingRequests' => $this->requests->countPending(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $data = new CategoryFormData();
        $form = $this->createForm(CategoryFormType::class, $data, [
            'parents' => $this->parentChoices(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                ($this->createCategory)(new CreateCategory(
                    parent: $data->parent,
                    name: $data->name,
                    description: $data->description,
                    icon: $data->icon,
                    sortOrder: $data->sortOrder,
                ));

                $this->addFlash('success', 'Категория создана.');

                return $this->redirectToRoute('app_admin_category_index');
            } catch (\RuntimeException $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        return $this->render('admin/categories/form.html.twig', [
            'admin_menu' => 'categories',
            'form' => $form->createView(),
            'category' => null,
            'withParent' => true,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Category $category, Request $request): Response
    {
        $data = CategoryFormData::fromCategory($category);
        $form = $this->createForm(CategoryFormType::class, $data, [
            'withParent' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                ($this->updateCategory)(new UpdateCategory(
                    category: $category,
                    name: $data->name,
                    description: $data->description,
                    icon: $data->icon,
                    sortOrder: $data->sortOrder,
                ));

                $this->addFlash('success', 'Категория сохранена.');

                return $this->redirectToRoute('app_admin_category_index');
            } catch (\RuntimeException $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        return $this->render('admin/categories/form.html.twig', [
            'admin_menu' => 'categories',
            'form' => $form->createView(),
            'category' => $category,
            'withParent' => false,
        ]);
    }

    #[Route('/{id}/deactivate', name: 'deactivate', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deactivate(Category $category, Request $request): Response
    {
        $this->assertCsrf($request, 'category_activation_' . $category->getId());

        try {
            ($this->setActive)(new SetCategoryActive($category, false));
            $this->addFlash('success', 'Категория отключена.');
        } catch (\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('app_admin_category_index');
    }

    #[Route('/{id}/activate', name: 'activate', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function activate(Category $category, Request $request): Response
    {
        $this->assertCsrf($request, 'category_activation_' . $category->getId());
        ($this->setActive)(new SetCategoryActive($category, true));

        $this->addFlash('success', 'Категория включена.');

        return $this->redirectToRoute('app_admin_category_index');
    }

    /**
     * Родителями могут быть только активные корни без товаров:
     * иначе подкатегорию создать нельзя.
     *
     * @return list<Category>
     */
    private function parentChoices(): array
    {
        return array_values(array_filter(
            $this->categories->findRootsWithChildren(true),
            fn (Category $root): bool => 0 === $this->categories->countProducts($root),
        ));
    }

    private function assertCsrf(Request $request, string $tokenId): void
    {
        if (!$this->isCsrfTokenValid($tokenId, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
    }
}
