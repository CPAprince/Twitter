<?php

declare(strict_types=1);

namespace Twitter\Tweet\UI\Web\Tweets;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class TweetPageController extends AbstractController{
    #[Route('/t/{tweetId}', name: 'TweetPage', methods: ['GET'])]
public function showTweetPage(string $tweetId) : Response
    {
        return $this->render('page/tweetpage.html.twig');
    }
}
