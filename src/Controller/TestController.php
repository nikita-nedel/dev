<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class TestController extends AbstractController
{
    #[Route('/{id}', name: 'test', requirements: ['id' => Requirement::DIGITS], defaults: ['id' => 2])]
    public function testAction(int $id): Response
    {
        $a = rand(1, 100);

        return $this->render('base.html.twig', [
            'number2' => $id,
            'number' => $a,
        ]);
    }
}
