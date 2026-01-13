<?php

declare(strict_types=1);

namespace Twitter\Tweet\UI\Web\Homepage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomepageController extends AbstractController
{
    #[Route('/', name: 'homepage', methods: ['GET'])]
    public function __invoke(): Response
    {
        $tweets = [
            [
                'id' => '3fa85f64-5717-4562-b3fc-2c963f66afa6',
                'content' => 'Just built a Twitter clone using Bootstrap! 🚀 #webdev #coding',
                'createdAt' => '2026-01-06T10:24:26+00:00',
                'updatedAt' => '2026-01-06T10:24:26+00:00',
                'author' => [
                    'id' => '550e8400-e29b-41d4-a716-446655440000',
                    'name' => 'John Doe',
                ],
            ],
            [
                'id' => '4fa85f64-5717-4562-b3fc-2c963f66afa6',
                'content' => '#travel #fun Barsa was AWESOME! IBIZA meet me NEXT WEEKENDS!',
                'createdAt' => '2026-01-06T08:10:00+00:00',
                'updatedAt' => '2026-01-06T08:10:00+00:00',
                'author' => [
                    'id' => '551e8400-e29b-41d4-a716-446655440000',
                    'name' => 'Maria Taylor',
                ],
            ],
        ];

        return $this->render('page/homepage.html.twig', [
            'tweets' => $tweets,
        ]);
    }
}
