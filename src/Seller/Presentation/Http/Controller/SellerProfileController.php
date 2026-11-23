<?php

declare(strict_types=1);

namespace App\Seller\Presentation\Http\Controller;

use App\Catalog\Domain\Model\Product;
use App\Identity\Domain\Model\CustomerProfile;
use App\Identity\Domain\Model\Seller;
use App\Seller\Application\Command\UpdateSellerPersonalData\UpdateSellerPersonalData;
use App\Seller\Application\Command\UpdateSellerPersonalData\UpdateSellerPersonalDataHandler;
use App\Seller\Application\Command\UpdateSellerShop\UpdateSellerShop;
use App\Seller\Application\Command\UpdateSellerShop\UpdateSellerShopHandler;
use App\Seller\Application\Dto\SellerPersonalData;
use App\Seller\Application\Dto\SellerShopData;
use App\Seller\Application\Service\SellerOverviewProvider;
use App\Seller\Domain\Model\Shop;
use App\Seller\Presentation\Http\Form\SellerPersonalFormType;
use App\Seller\Presentation\Http\Form\SellerShopFormType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @phpstan-type ShopOverviewRow array{
 *     shop: Shop,
 *     total: int,
 *     active: int,
 *     draft: int,
 *     archived: int,
 *     recentProducts: list<Product>
 * }
 * @phpstan-type SellerOverview array{
 *     shops: list<ShopOverviewRow>,
 *     totals: array{total: int, active: int, draft: int, archived: int}
 * }
 */
#[IsGranted('ROLE_SELLER')]
final class SellerProfileController extends AbstractController
{
    public function __construct(
        private readonly SellerOverviewProvider $overview,
        private readonly UpdateSellerPersonalDataHandler $updatePersonalData,
        private readonly UpdateSellerShopHandler $updateShop,
    ) {
    }

    #[Route('/seller/profile', name: 'app_seller_profile', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $seller = $this->seller();
        $overview = $this->overview->forSeller($seller);
        $activeShop = $this->resolveShop($overview, $request->query->getInt('shop'));

        return $this->renderPage(
            $seller,
            $overview,
            $activeShop,
            $this->createForm(SellerPersonalFormType::class, $this->personalData($seller)),
            $this->createForm(SellerShopFormType::class, $this->shopData($activeShop)),
            null,
        );
    }

    #[Route('/seller/profile/personal', name: 'app_seller_profile_personal_update', methods: ['POST'])]
    public function updatePersonal(Request $request): Response
    {
        $seller = $this->seller();
        $overview = $this->overview->forSeller($seller);
        $activeShop = $this->resolveShop($overview, $request->query->getInt('shop'));
        $form = $this->createForm(SellerPersonalFormType::class, $this->personalData($seller));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!$data instanceof SellerPersonalData) {
                throw new \LogicException('Форма личных данных вернула неожиданные данные.');
            }

            try {
                ($this->updatePersonalData)(new UpdateSellerPersonalData($seller, $data));
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'Этот номер телефона уже используется другим аккаунтом.');

                return $this->redirectToProfile($activeShop);
            } catch (\RuntimeException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToProfile($activeShop);
            }

            $this->addFlash('success', 'Личные данные обновлены.');

            return $this->redirectToProfile($activeShop, true);
        }

        return $this->renderPage(
            $seller,
            $overview,
            $activeShop,
            $form,
            $this->createForm(SellerShopFormType::class, $this->shopData($activeShop)),
            'personal',
        );
    }

    #[Route('/seller/profile/shop', name: 'app_seller_profile_shop_update', methods: ['POST'])]
    public function updateShop(Request $request): Response
    {
        $seller = $this->seller();
        $overview = $this->overview->forSeller($seller);
        $activeShop = $this->resolveShop($overview, $request->query->getInt('shop'));
        $form = $this->createForm(SellerShopFormType::class, $this->shopData($activeShop));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!$data instanceof SellerShopData) {
                throw new \LogicException('Форма магазина вернула неожиданные данные.');
            }

            try {
                $shop = ($this->updateShop)(new UpdateSellerShop($seller, $data, $activeShop));
            } catch (\RuntimeException | \InvalidArgumentException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToProfile($activeShop);
            }

            $this->addFlash('success', null === $activeShop ? 'Магазин создан.' : 'Магазин обновлён.');

            return $this->redirectToProfile($shop, true);
        }

        return $this->renderPage(
            $seller,
            $overview,
            $activeShop,
            $this->createForm(SellerPersonalFormType::class, $this->personalData($seller)),
            $form,
            'shop',
        );
    }

    private function redirectToProfile(?Shop $shop, bool $updated = false): Response
    {
        $parameters = [];

        if ($updated) {
            $parameters['updated'] = 1;
        }

        if (null !== $shop?->getId()) {
            $parameters['shop'] = $shop->getId();
        }

        return $this->redirectToRoute('app_seller_profile', $parameters);
    }

    private function seller(): Seller
    {
        $seller = $this->getUser();

        if (!$seller instanceof Seller) {
            throw $this->createAccessDeniedException();
        }

        return $seller;
    }

    /**
     * @param SellerOverview $overview
     */
    private function resolveShop(array $overview, int $shopId): ?Shop
    {
        /** @var list<Shop> $shops */
        $shops = array_map(static fn (array $row): Shop => $row['shop'], $overview['shops']);

        if ([] === $shops) {
            return null;
        }

        foreach ($shops as $shop) {
            if ($shopId === $shop->getId()) {
                return $shop;
            }
        }

        return $shops[0];
    }

    private function personalData(Seller $seller): SellerPersonalData
    {
        $data = new SellerPersonalData();
        $customerProfile = $seller->getCustomer()?->getProfile();

        $data->firstName = $customerProfile?->getFirstName() ?? '';
        $data->lastName = $customerProfile?->getLastName() ?? '';
        $data->phone = $customerProfile?->getPhone() ?? '';

        return $data;
    }

    private function shopData(?Shop $shop): SellerShopData
    {
        $data = new SellerShopData();
        $data->shopName = $shop?->getName() ?? '';
        $data->legalName = $shop?->getLegalName();
        $data->inn = $shop?->getInn();
        $data->description = $shop?->getDescription();
        $data->contactPhone = $shop?->getContactPhone();

        return $data;
    }

    /**
     * @param SellerOverview $overview
     */
    private function renderPage(
        Seller $seller,
        array $overview,
        ?Shop $activeShop,
        FormInterface $personalForm,
        FormInterface $shopForm,
        ?string $openEditor,
    ): Response {
        $customer = $seller->getCustomer();
        $customerProfile = $customer?->getProfile();

        $activeShopStats = [
            'total' => 0,
            'active' => 0,
            'draft' => 0,
            'archived' => 0,
            'recentProducts' => [],
        ];

        foreach ($overview['shops'] as $row) {
            if ($row['shop'] === $activeShop) {
                $activeShopStats = [
                    'total' => $row['total'],
                    'active' => $row['active'],
                    'draft' => $row['draft'],
                    'archived' => $row['archived'],
                    'recentProducts' => $row['recentProducts'],
                ];

                break;
            }
        }

        $fullName = trim(sprintf(
            '%s %s',
            $customerProfile?->getFirstName() ?? '',
            $customerProfile?->getLastName() ?? '',
        ));

        if ('' === $fullName) {
            $fullName = (string) $seller->getEmail();
        }

        $completion = $this->completion($customerProfile, $activeShop, $overview['totals']['active']);

        return $this->render('seller/profile/index.html.twig', [
            'seller' => $seller,
            'customer' => $customer,
            'customerProfile' => $customerProfile,
            'overview' => $overview,
            'activeShop' => $activeShop,
            'activeShopStats' => $activeShopStats,
            'storeName' => $activeShop?->getName() ?? 'Магазин без названия',
            'fullName' => $fullName,
            'initials' => $this->initials($fullName),
            'personalForm' => $personalForm->createView(),
            'shopForm' => $shopForm->createView(),
            'openEditor' => $openEditor,
            'completion' => $completion,
            'achievements' => $this->achievements(
                $seller,
                $activeShop,
                $overview['totals']['active'],
                $completion['percent'],
            ),
        ]);
    }

    /**
     * @return array{
     *     percent: int,
     *     done: int,
     *     total: int,
     *     checks: list<array{label: string, done: bool, icon: string, target: ?string}>
     * }
     */
    private function completion(
        ?CustomerProfile $customerProfile,
        ?Shop $shop,
        int $productCount,
    ): array {
        $firstName = trim($customerProfile?->getFirstName() ?? '');
        $lastName = trim($customerProfile?->getLastName() ?? '');
        $phone = $customerProfile?->getPhone();
        $shopName = $shop?->getName();
        $legalName = $shop?->getLegalName();
        $inn = $shop?->getInn();
        $description = $shop?->getDescription();
        $contactPhone = $shop?->getContactPhone();

        $checks = [
            [
                'label' => 'Имя и фамилия',
                'done' => '' !== $firstName && '' !== $lastName,
                'icon' => 'bi-person',
                'target' => 'personal',
            ],
            [
                'label' => 'Телефон для связи',
                'done' => null !== $phone && '' !== $phone,
                'icon' => 'bi-telephone',
                'target' => 'personal',
            ],
            [
                'label' => 'Название магазина',
                'done' => null !== $shopName && '' !== $shopName,
                'icon' => 'bi-shop',
                'target' => 'shop',
            ],
            [
                'label' => 'Юридические данные',
                'done' => null !== $legalName && null !== $inn,
                'icon' => 'bi-briefcase',
                'target' => 'shop',
            ],
            [
                'label' => 'Описание магазина',
                'done' => null !== $description && '' !== $description,
                'icon' => 'bi-text-paragraph',
                'target' => 'shop',
            ],
            [
                'label' => 'Телефон магазина',
                'done' => null !== $contactPhone && '' !== $contactPhone,
                'icon' => 'bi-headset',
                'target' => 'shop',
            ],
            [
                'label' => 'Товары в витрине',
                'done' => $productCount > 0,
                'icon' => 'bi-box-seam',
                'target' => null,
            ],
        ];

        $done = count(array_filter($checks, static fn (array $check): bool => $check['done']));

        return [
            'percent' => (int) round($done / count($checks) * 100),
            'done' => $done,
            'total' => count($checks),
            'checks' => $checks,
        ];
    }

    /**
     * @return list<array{icon: string, title: string, text: string, unlocked: bool}>
     */
    private function achievements(
        Seller $seller,
        ?Shop $shop,
        int $productCount,
        int $completion,
    ): array {
        $hasRequisites = null !== $shop
            && null !== $shop->getLegalName()
            && null !== $shop->getInn();

        return [
            [
                'icon' => 'bi-rocket-takeoff',
                'title' => 'Первый шаг',
                'text' => 'Аккаунт продавца создан',
                'unlocked' => true,
            ],
            [
                'icon' => 'bi-shop',
                'title' => 'Магазин открыт',
                'text' => 'Заявка одобрена и активна',
                'unlocked' => $seller->isActive() && null !== $shop,
            ],
            [
                'icon' => 'bi-box-seam',
                'title' => 'Витрина оживает',
                'text' => 'Есть опубликованные товары',
                'unlocked' => $productCount > 0,
            ],
            [
                'icon' => 'bi-patch-check',
                'title' => 'Юрлицо',
                'text' => 'Реквизиты заполнены',
                'unlocked' => $hasRequisites,
            ],
            [
                'icon' => 'bi-stars',
                'title' => 'Профиль на максимум',
                'text' => 'Все пункты профиля заполнены',
                'unlocked' => 100 === $completion,
            ],
        ];
    }

    private function initials(string $fullName): string
    {
        $parts = preg_split('/\s+/u', trim($fullName)) ?: [];
        $initials = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return '' === $initials ? 'S' : $initials;
    }
}
