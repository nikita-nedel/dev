<?php

declare(strict_types=1);

namespace App\Controller\Seller;

use App\Entity\Customer;
use App\Entity\Seller;
use App\Entity\SellerRequest;
use App\Form\SellerRequestFormType;
use App\Service\SellerRequestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SellerRequestController extends AbstractController
{
    public function __construct(
        private readonly SellerRequestService $sellerRequestService,
    ) {
    }

    #[Route('/become-seller', name: 'seller_apply', methods: ['GET', 'POST'])]
    public function apply(Request $request): Response
    {
        $customer = $this->getUser();
        if (!$customer instanceof Customer) {
            return $this->redirectToRoute('app_login');
        }

        $existingSeller = $this->sellerRequestService->getSellerForCustomer($customer);
        if (null !== $existingSeller) {
            return $this->render('seller/application/status.html.twig', [
                'seller' => $existingSeller,
                'sellerRequest' => $existingSeller->getRequest(),
            ]);
        }

        $sellerRequest = new SellerRequest();
        $form = $this->createForm(SellerRequestFormType::class, $sellerRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->sellerRequestService->createRequest($customer, $sellerRequest);
                $this->addFlash('success', 'Заявка отправлена на рассмотрение.');

                return $this->redirectToRoute('seller_apply');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('seller/application/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
