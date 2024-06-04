<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSuscriberSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpException) {
            $data = ["status" => $exception->getStatusCode(), "message" => $exception->getMessage()];
            $event->setResponse(new JsonResponse($data, $exception->getStatusCode()));
        } else {
            $data = ["status" => 500, "message" => $exception->getMessage()];
            $event->setResponse(new JsonResponse($data, 500));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => "onKernelException",
        ];
    }
}
