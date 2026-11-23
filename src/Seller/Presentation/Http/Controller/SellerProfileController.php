<?php

declare(strict_types=1);

namespace App\Seller\Presentation\Http\Controller;

use App\Catalog\Application\Query\SellerCatalogQueryInterface;
use App\Identity\Domain\Model\CustomerProfile;
use App\Identity\Domain\Model\Seller;
use App\Seller\Application\Command\UpdateSellerProfile\UpdateSellerProfile;
use App\Seller\Application\Command\UpdateSellerProfile\UpdateSellerProfileHandler;
use App\Seller\Application\Dto\SellerProfileData;
use App\Seller\Domain\Model\SellerRequest;
use App\Seller\Domain\Repository\SellerRequestRepositoryInterface;
use App\Seller\Presentation\Http\Form\SellerProfileFormType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SELLER')]
final class SellerProfileController extends AbstractController
{
    public function __construct(
        private readonly SellerRequestRepositoryInterface $requests,
        private readonly SellerCatalogQueryInterface $catalog,
        private readonly UpdateSellerProfileHandler $updateProfile,
    ) {
    }

    #[Route('/seller/profile', name: 'app_seller_profile', methods: ['GET'])]
    public function index(): Response
    {
        $seller = $this->seller();
        $form = $this->createForm(SellerProfileFormType::class, $this->formData($seller));

        return $this->renderPage($seller, $form, false);
    }

    #[Route('/seller/profile', name: 'app_seller_profile_update', methods: ['POST'])]
    public function update(Request $request): Response
    {
        $seller = $this->seller();
        $form = $this->createForm(SellerProfileFormType::class, $this->formData($seller));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!$data instanceof SellerProfileData) {
                throw new \LogicException('Форма профиля вернула неожиданные данные.');
            }

            try {
                ($this->updateProfile)(new UpdateSellerProfile($seller, $data));
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'Этот номер телефона уже используется другим аккаунтом.');

                return $this->redirectToRoute('app_seller_profile');
            } catch (\RuntimeException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('app_seller_profile');
            }

            $this->addFlash('success', 'Профиль обновлён. Магазин и контакты уже используют новые данные.');

            return $this->redirectToRoute('app_seller_profile', ['updated' => 1]);
        }

        return $this->renderPage($seller, $form, true);
    }

    private function seller(): Seller
    {
        $seller = $this->getUser();

        if (!$seller instanceof Seller) {
            throw $this->createAccessDeniedException();
        }

        return $seller;
    }

    private function formData(Seller $seller): SellerProfileData
    {
        $data = new SellerProfileData();
        $customerProfile = $seller->getCustomer()?->getProfile();
        $sellerRequest = $this->requests->findBySeller($seller);

        $data->firstName = $customerProfile?->getFirstName() ?? '';
        $data->lastName = $customerProfile?->getLastName() ?? '';
        $data->phone = $customerProfile?->getPhone() ?? '';
        $data->shopName = $sellerRequest?->getShopName() ?? '';
        $data->legalName = $sellerRequest?->getLegalName();
        $data->inn = $sellerRequest?->getInn();
        $data->description = $sellerRequest?->getDescription();
        $data->contactPhone = $sellerRequest?->getContactPhone();

        return $data;
    }

    private function renderPage(Seller $seller, FormInterface $form, bool $editOpen): Response
    {
        $customer = $seller->getCustomer();
        $customerProfile = $customer?->getProfile();
        $sellerRequest = $this->requests->findBySeller($seller);
        $products = $this->catalog->products();
        $stats = $this->catalog->stats();

        $storeName = $sellerRequest?->getShopName() ?? 'Магазин без названия';

        $fullName = trim(sprintf(
            '%s %s',
            $customerProfile?->getFirstName() ?? '',
            $customerProfile?->getLastName() ?? '',
        ));

        if ('' === $fullName) {
            $fullName = (string) $seller->getEmail();
        }

        $completion = $this->completion($customerProfile, $sellerRequest, $stats['total']);

        return $this->render('seller/profile/index.html.twig', [
            'seller' => $seller,
            'customer' => $customer,
            'customerProfile' => $customerProfile,
            'sellerRequest' => $sellerRequest,
            'storeName' => $storeName,
            'fullName' => $fullName,
            'initials' => $this->initials($fullName),
            'form' => $form->createView(),
            'editOpen' => $editOpen,
            'products' => $products,
            'stats' => $stats,
            'completion' => $completion,
            'achievements' => $this->achievements($seller, $sellerRequest, $stats['total'], $completion['percent']),
        ]);
    }

    /**
     * @return array{percent: int, done: int, total: int, checks: list<array{label: string, done: bool, icon: string}>}
     */
    private function completion(
        ?CustomerProfile $customerProfile,
        ?SellerRequest $sellerRequest,
        int $productCount,
    ): array {
        $firstName = trim($customerProfile?->getFirstName() ?? '');
        $lastName = trim($customerProfile?->getLastName() ?? '');
        $phone = $customerProfile?->getPhone();
        $shopName = $sellerRequest?->getShopName();
        $legalName = $sellerRequest?->getLegalName();
        $inn = $sellerRequest?->getInn();
        $description = $sellerRequest?->getDescription();
        $contactPhone = $sellerRequest?->getContactPhone();

        $checks = [
            [
                'label' => 'Имя и фамилия',
                'done' => '' !== $firstName && '' !== $lastName,
                'icon' => 'bi-person',
            ],
            [
                'label' => 'Телефон для связи',
                'done' => null !== $phone && '' !== $phone,
                'icon' => 'bi-telephone',
            ],
            [
                'label' => 'Название магазина',
                'done' => null !== $shopName && '' !== $shopName,
                'icon' => 'bi-shop',
            ],
            [
                'label' => 'Юридические данные',
                'done' => null !== $legalName && null !== $inn,
                'icon' => 'bi-briefcase',
            ],
            [
                'label' => 'Описание магазина',
                'done' => null !== $description && '' !== $description,
                'icon' => 'bi-text-paragraph',
            ],
            [
                'label' => 'Телефон магазина',
                'done' => null !== $contactPhone && '' !== $contactPhone,
                'icon' => 'bi-headset',
            ],
            [
                'label' => 'Товары в витрине',
                'done' => $productCount > 0,
                'icon' => 'bi-box-seam',
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
        ?SellerRequest $sellerRequest,
        int $productCount,
        int $completion,
    ): array {
        $hasRequisites = null !== $sellerRequest
            && null !== $sellerRequest->getLegalName()
            && null !== $sellerRequest->getInn();

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
                'unlocked' => $seller->isActive(),
            ],
            [
                'icon' => 'bi-box-seam',
                'title' => 'Витрина оживает',
                'text' => 'В магазине есть товары',
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
