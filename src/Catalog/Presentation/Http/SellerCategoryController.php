<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http;

use App\Catalog\Application\Command\RequestCategory\RequestCategory;
use App\Catalog\Application\Command\RequestCategory\RequestCategoryHandler;
use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryRequestRepositoryInterface;
use App\Catalog\Presentation\Http\Form\CategoryRequestFormData;
use App\Catalog\Presentation\Http\Form\CategoryRequestFormType;
use App\Identity\Domain\Model\Seller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/seller/categories', name: 'app_seller_category_')]
#[IsGranted('ROLE_SELLER')]
final class SellerCategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly CategoryRequestRepositoryInterface $requests,
        private readonly RequestCategoryHandler $requestCategory,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $seller = $this->requireSeller();

        return $this->render('seller/categories/index.html.twig', [
            'roots' => $this->categories->findRootsWithChildren(true),
            'requests' => $this->requests->findBySeller($seller),
            'pendingCount' => $this->requests->countPendingBySeller($seller),
        ]);
    }

    #[Route('/request', name: 'request', methods: ['GET', 'POST'])]
    public function request(Request $request): Response
    {
        $seller = $this->requireSeller();
        $data = new CategoryRequestFormData();
        $form = $this->createForm(CategoryRequestFormType::class, $data, [
            'parents' => $this->requestableParents(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                ($this->requestCategory)(new RequestCategory(
                    seller: $seller,
                    name: $data->name,
                    parent: $data->parent,
                    comment: $data->comment,
                ));

                $this->addFlash('success', 'Заявка отправлена на проверку админу.');

                return $this->redirectToRoute('app_seller_category_index');
            } catch (\RuntimeException $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        return $this->render('seller/categories/request.html.twig', [
            'form' => $form->createView(),
            'pendingCount' => $this->requests->countPendingBySeller($seller),
        ]);
    }

    private function requireSeller(): Seller
    {
        $seller = $this->getUser();

        if (!$seller instanceof Seller) {
            throw $this->createAccessDeniedException();
        }

        return $seller;
    }

    /**
     * Подкатегорию нельзя создать в корне, где уже есть товары.
     *
     * @return list<Category>
     */
    private function requestableParents(): array
    {
        return array_values(array_filter(
            $this->categories->findRootsWithChildren(true),
            fn (Category $root): bool => 0 === $this->categories->countProducts($root),
        ));
    }
}
