<?php

declare(strict_types=1);

namespace Twitter\Tweet\UI\Web\Profile\Tweets;

use Assert\Assert;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\Profile\Application\UseCase\GetProfile\GetProfileQuery;
use Twitter\Profile\Application\UseCase\GetProfile\GetProfileQueryHandler;
use Twitter\Profile\Domain\Profile\Exception\ProfileNotFoundException;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQuery;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQueryHandler;

#[Route('/profiles/{userId}/tweets/fragment', name: 'profile_tweets_fragment', methods: ['GET'])]
final class ProfileTweetsController extends AbstractController
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

        $profile = $this->profileQueryHandler->handle(new GetProfileQuery($userId));
        $tweetsResult = $this->tweetsQueryHandler->handle(new GetUserTweetsQuery($userId, limit: 50, page: 0));

        $tweets = [];
        foreach ($tweetsResult->tweets ?? [] as $tweet) {
            $tweets[] = [
                'id' => $tweet->id(),
                'content' => $tweet->content(),
                'createdAt' => $tweet->createdAt(),
                'updatedAt' => $tweet->updatedAt(),
                'author' => [
                    'id' => $tweet->userId(),
                    'name' => $profile->name,
                ],
                'likes' => $tweet->likes(),
            ];
        }

        return $this->render('component/_tweet_list.html.twig', [
            'tweets' => $tweets,
            'title' => 'Tweets:',
            'showEdit' => true,
        ]);
    }
}
