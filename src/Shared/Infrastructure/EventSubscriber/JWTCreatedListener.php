<?php

declare(strict_types=1);

namespace Twitter\Shared\Infrastructure\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Twitter\IAM\Domain\User\Model\User;


final class JWTCreatedListener
{
    public function __invoke(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $user = $event->getUser();

//        if (!$user instanceof User) {
//            return;
//        }

        if (method_exists($user, 'id')) {
            $payload['id'] = $user->id();
        }

        $event->setData($payload);
    }
}
