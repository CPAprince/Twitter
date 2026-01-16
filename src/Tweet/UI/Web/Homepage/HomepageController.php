<?php

declare(strict_types=1);

namespace Twitter\Tweet\UI\Web\Homepage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\Tweet\Application\UseCase\GetTweets\GetTweetsCommand;
use Twitter\Tweet\Application\UseCase\GetTweets\GetTweetsCommandHandler;
use Twitter\Tweet\Application\UseCase\Shared\TweetResponse;

final class HomepageController extends AbstractController
{
    public function __construct(
        private readonly GetTweetsCommandHandler $handler,
    ) {}

    #[Route('/', name: 'homepage', methods: ['GET'])]
    public function __invoke(): Response
    {
        $dto = $this->handler->handle(new GetTweetsCommand());

        $tweets = array_map(
            static fn (TweetResponse $tweet): array => [
                'id' => $tweet->id,
                'content' => $tweet->content,
                'createdAt' => $tweet->createdAt,
                'updatedAt' => $tweet->updatedAt,
                'author' => [
                    'id' => $tweet->authorId,
                    'name' => $tweet->authorName,
                ],
            ],
            $dto->tweets
        );

        return $this->render('page/homepage.html.twig', [
            'tweets' => $tweets,
        ]);
    }
}
