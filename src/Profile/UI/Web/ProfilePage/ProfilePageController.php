<?php

declare(strict_types=1);

namespace Twitter\Profile\UI\Web\ProfilePage;

use Assert\Assert;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\Profile\Application\UseCase\GetProfile\GetProfileQuery;
use Twitter\Profile\Application\UseCase\GetProfile\GetProfileQueryHandler;
use Twitter\Profile\Domain\Profile\Exception\ProfileNotFoundException;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQuery;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQueryHandler;

#[Route('/profiles/{userId}', name: 'profile_page', methods: ['GET'])]
final class ProfilePageController extends AbstractController
{
    public function __construct(
        private readonly GetProfileQueryHandler $profileQueryHandler,
        private readonly GetUserTweetsQueryHandler $tweetsQueryHandler,
    ) {}

    /**
     * @throws ProfileNotFoundException
     */
    public function __invoke(string $userId): Response
    {
        Assert::that($userId)->uuid();

        // Fetch profile
        $profileQuery = new GetProfileQuery($userId);
        $profileResult = $this->profileQueryHandler->handle($profileQuery);

        // Fetch user tweets
        $tweetsQuery = new GetUserTweetsQuery($userId, limit: 50, page: 0);
        $tweetsResult = $this->tweetsQueryHandler->handle($tweetsQuery);

        // Map tweets to array format for template
        $tweets = [];
        if (!empty($tweetsResult->tweets)) {
            foreach ($tweetsResult->tweets as $tweet) {
                $tweets[] = [
                    'id' => $tweet->id(),
                    'content' => $tweet->content(),
                    'createdAt' => $tweet->createdAt(),
                    'updatedAt' => $tweet->updatedAt(),
                    'author' => [
                        'id' => $tweet->userId(),
                        'name' => $profileResult->name,
                    ],
                    'likes' => $tweet->likes(),
                ];
            }
        }

        return $this->render('page/profile.html.twig', [
            'profile' => [
                'userId' => $userId,
                'name' => $profileResult->name,
                'bio' => $profileResult->bio,
                'createdAt' => $profileResult->createdAt,
                'updatedAt' => $profileResult->updatedAt,
            ],
            'tweets' => $tweets,
        ]);
    }
}
