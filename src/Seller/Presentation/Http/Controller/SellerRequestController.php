<?php

declare(strict_types=1);

namespace App\Seller\Presentation\Http\Controller;

use App\Identity\Domain\Model\Customer;
use App\Identity\Domain\Model\Seller;
use App\Identity\Domain\Repository\SellerRepositoryInterface;
use App\Seller\Application\Command\ApplyForSeller\ApplyForSeller;
use App\Seller\Application\Command\ApplyForSeller\ApplyForSellerHandler;
use App\Seller\Domain\Model\SellerRequest;
use App\Seller\Domain\Repository\SellerRequestRepositoryInterface;
use App\Seller\Presentation\Http\Form\SellerRequestFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Страница «стать продавцом»: показывает статус, если заявка уже есть, иначе — форму.
 */
final class SellerRequestController extends AbstractController
{
    public function __construct(
        private readonly SellerRepositoryInterface $sellers,
        private readonly SellerRequestRepositoryInterface $requests,
        private readonly ApplyForSellerHandler $applyForSeller,
    ) {
    }

    #[Route('/become-seller', name: 'seller_apply', methods: ['GET', 'POST'])]
    public function apply(Request $request): Response
    {
        $customer = $this->getUser();

        if (!$customer instanceof Customer) {
            return $this->redirectToRoute('app_login');
        }

        $existingSeller = $this->sellers->findByCustomer($customer);

        if (null !== $existingSeller) {
            return $this->renderStatus($existingSeller);
        }

        $sellerRequest = new SellerRequest();
        $form = $this->createForm(SellerRequestFormType::class, $sellerRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                ($this->applyForSeller)(new ApplyForSeller($customer, $sellerRequest));
                $this->addFlash('success', 'Заявка отправлена на рассмотрение.');

                return $this->redirectToRoute('seller_apply');
            } catch (\RuntimeException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render('seller/application/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function renderStatus(Seller $seller): Response
    {
        return $this->render('seller/application/status.html.twig', [
            'seller' => $seller,
            'sellerRequest' => $this->requests->findBySeller($seller),
        ]);
    }
}
