<?php

namespace App\EventListener;

use App\Exceptions\WebHttpException\WebExceptionInterface;
use App\Service\MailSender;
use App\Service\TelegramBotService;
use Swift_IoException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Twig\Environment;

class ExceptionListener
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var MailSender
     */
    private $mailSender;

    /**
     * @var string
     */
    private $adminEmail;

    /**
     * @var TelegramBotService
     */
    private $botService;

    /**
     * @var string
     */
    private $sitename;

    /**
     * @param Environment $twig
     * @param MailSender $mailSender
     * @param string $adminEmail
     * @param TelegramBotService $botService
     */
    public function __construct(Environment $twig, MailSender $mailSender, TelegramBotService $botService, string $adminEmail, string $sitename)
    {
        $this->sitename = $sitename;
        $this->twig = $twig;
        $this->mailSender = $mailSender;
        $this->adminEmail = $adminEmail;
        $this->botService = $botService;
    }

    /**
     * @param ExceptionEvent $event
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $messageSubject = "Исключение на сайте {$this->sitename}";

        $request = $event->getRequest();
        $message = $exception->getMessage() . "\r\n";
        $message .= 'ip = ' . $_SERVER['HTTP_X_FORWARDED_FOR'] . "\r\n";
        $message .= 'referer = ' . $request->headers->get('referer') . "\r\n";
        $message .= 'user-agent = ' . $request->headers->get('User-Agent') . "\r\n";
        $message .= 'uri = ' . $request->getUri() . "\r\n";

        if (method_exists($exception, 'getStatusCode')) {
            if (Response::HTTP_NOT_FOUND !== $exception->getStatusCode()) {
                $this->botService->sendMessage($message);
            }

        } else {
            $this->botService->sendMessage($message);
        }

        if ($exception instanceof WebExceptionInterface) {
            if (empty($exception->getTemplate())) {
                $response = new Response($exception->getMessage());
            } else {
                $response = new Response(
                    $this->twig->render(
                        $exception->getTemplate(),
                        array_merge(['errors' => $exception->getErrors()], $exception->getTemplateVars())
                    )
                );
            }
        }

        if (!empty($response)) {
            $event->setResponse($response);
        }
    }
}
