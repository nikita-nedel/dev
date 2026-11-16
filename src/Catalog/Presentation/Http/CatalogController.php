<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http;

use App\Catalog\Application\Query\CatalogQueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CatalogController extends AbstractController
{
    public function __construct(
        private readonly CatalogQueryInterface $catalog,
    ) {
    }

    #[Route('/catalog/{slug}', name: 'cat', defaults: ['slug' => 'odezhda'])]
    public function index(string $slug): Response
    {
        return $this->render('catalog/index.html.twig', $this->catalog->catalogPage($slug));
    }
}
