<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http;

use App\Catalog\Application\Command\ArchiveProduct\ArchiveProduct;
use App\Catalog\Application\Command\ArchiveProduct\ArchiveProductHandler;
use App\Catalog\Application\Command\CreateProduct\CreateProduct;
use App\Catalog\Application\Command\CreateProduct\CreateProductHandler;
use App\Catalog\Application\Command\PublishProduct\PublishProduct;
use App\Catalog\Application\Command\PublishProduct\PublishProductHandler;
use App\Catalog\Application\Command\UpdateProduct\UpdateProduct;
use App\Catalog\Application\Command\UpdateProduct\UpdateProductHandler;
use App\Catalog\Application\Service\GenerateSkuService;
use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Catalog\Presentation\Http\Form\ProductFormData;
use App\Catalog\Presentation\Http\Form\ProductFormType;
use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\Shop;
use App\Seller\Domain\Repository\ShopRepositoryInterface;
use App\Shared\Domain\ValueObject\Money;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/seller', 'app_seller_')]
#[IsGranted('ROLE_SELLER')]
final class SellerProductsController extends AbstractController
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ShopRepositoryInterface $shops,
        private readonly CreateProductHandler $createProduct,
        private readonly UpdateProductHandler $updateProduct,
        private readonly PublishProductHandler $publishProduct,
        private readonly ArchiveProductHandler $archiveProduct,
    ) {
    }

    #[Route('/products', name: 'products', methods: ['GET'])]
    public function index(
        PaginatorInterface $paginator,
        #[MapQueryParameter] int $page = 1,
    ): Response {
        $seller = $this->requireSeller();
        $shops = $this->shops->findBySeller($seller);

        $shopIds = [];
        $shopNames = [];
        foreach ($shops as $shop) {
            $shopId = (int) $shop->getId();
            $shopIds[] = $shopId;
            $shopNames[$shopId] = $shop->getName();
        }

        $pagination = $paginator->paginate(
            $this->products->createForShopIdsQueryBuilder($shopIds),
            $page,
            20,
        );

        return $this->render('seller/products/index.html.twig', [
            'pagination' => $pagination,
            'shopNames' => $shopNames,
        ]);
    }

    #[Route('/products/generate-sku', name: 'products_generate_sku', methods: ['GET'])]
    public function generateSku(
        GenerateSkuService $generateSku,
        #[MapQueryParameter] ?int $shopId = null,
    ): JsonResponse {
        $seller = $this->requireSeller();

        if (null === $shopId) {
            throw $this->createNotFoundException('Магазин не найден.');
        }

        $shop = $this->shops->findById($shopId);

        if (null === $shop || !$shop->belongsTo($seller)) {
            throw $this->createNotFoundException('Магазин не найден.');
        }

        return $this->json(['sku' => $generateSku->generate($shopId)->value()]);
    }

    #[Route('/products/new', name: 'products_new', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $seller = $this->requireSeller();
        $shops = $this->shops->findBySeller($seller);

        if ([] === $shops) {
            $this->addFlash('error', 'Сначала создайте магазин: товар нельзя создать без магазина.');

            return $this->redirectToRoute('app_seller_dashboard');
        }

        $data = new ProductFormData();
        $data->shop = $shops[0];

        $form = $this->createForm(ProductFormType::class, $data, [
            'shops' => $shops,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shop = $data->shop;

            if (!$shop->belongsTo($seller)) {
                throw $this->createNotFoundException('Магазин не найден.');
            }

            try {
                ($this->createProduct)(new CreateProduct(
                    shopId: (int) $shop->getId(),
                    name: $data->name,
                    sku: $data->sku,
                    price: Money::fromDecimalString($data->price, Product::CURRENCY),
                    description: $data->description,
                ));

                $this->addFlash('success', 'Товар создан.');

                return $this->redirectToRoute('app_seller_products');
            } catch (\RuntimeException | \InvalidArgumentException $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        return $this->render('seller/products/form.html.twig', [
            'form' => $form->createView(),
            'product' => null,
        ]);
    }

    #[Route('/products/{id}/edit', name: 'products_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $seller = $this->requireSeller();
        [$product, $shop] = $this->requireOwnedProductWithShop($id, $seller);

        if ($product->isArchived()) {
            $this->addFlash('error', 'Архивный товар нельзя редактировать.');

            return $this->redirectToRoute('app_seller_products');
        }

        $data = ProductFormData::fromProduct($product, $shop);
        $form = $this->createForm(ProductFormType::class, $data, [
            'shops' => [$shop],
            'shopLocked' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                ($this->updateProduct)(new UpdateProduct(
                    product: $product,
                    name: $data->name,
                    sku: $data->sku,
                    price: Money::fromDecimalString($data->price, Product::CURRENCY),
                    description: $data->description,
                ));

                $this->addFlash('success', 'Товар сохранён.');

                return $this->redirectToRoute('app_seller_products');
            } catch (\RuntimeException | \InvalidArgumentException $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        return $this->render('seller/products/form.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/products/{id}/publish', name: 'products_publish', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function publish(int $id, Request $request): Response
    {
        $seller = $this->requireSeller();
        $product = $this->requireOwnedProduct($id, $seller);

        if (!$this->isCsrfTokenValid('product_publish_' . $id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        try {
            ($this->publishProduct)(new PublishProduct($product));
            $this->addFlash('success', 'Товар опубликован.');
        } catch (\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('app_seller_products');
    }

    #[Route('/products/{id}/archive', name: 'products_archive', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function archive(int $id, Request $request): Response
    {
        $seller = $this->requireSeller();
        $product = $this->requireOwnedProduct($id, $seller);

        if (!$this->isCsrfTokenValid('product_archive_' . $id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        try {
            ($this->archiveProduct)(new ArchiveProduct($product));
            $this->addFlash('success', 'Товар убран в архив.');
        } catch (\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('app_seller_products');
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
     * @return array{Product, Shop}
     */
    private function requireOwnedProductWithShop(int $id, Seller $seller): array
    {
        $product = $this->products->findById($id);

        if (null === $product) {
            throw $this->createNotFoundException('Товар не найден.');
        }

        $shop = $this->shops->findById($product->getShopId());

        if (null === $shop || !$shop->belongsTo($seller)) {
            throw $this->createNotFoundException('Товар не найден.');
        }

        return [$product, $shop];
    }

    private function requireOwnedProduct(int $id, Seller $seller): Product
    {
        return $this->requireOwnedProductWithShop($id, $seller)[0];
    }
}
