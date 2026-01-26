<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class TestController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function testAction(): Response
    {
        $a = rand(1, 100);

        return $this->render('index.html.twig', [
//            'number2' => $id,
            'number' => $a,
        ]);
    }
}
