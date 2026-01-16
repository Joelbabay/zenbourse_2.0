<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private MailerInterface $mailer,
        private LoggerInterface $logger
    ) {}

    public function __invoke(SendEmailMessage $message): void
    {
        $user = $this->userRepository->find($message->getUserId());

        if (!$user) {
            $this->logger->error('User not found for email', [
                'user_id' => $message->getUserId()
            ]);
            return;
        }

        try {
            $email = (new Email())
                ->from(new Address('no-reply@zenbourse.fr', 'Zenbourse'))
                ->to(new Address($user->getEmail(), $user->getFirstname() . ' ' . $user->getLastname()))
                ->subject($message->getSubject())
                ->text($message->getTextContent());

            $this->mailer->send($email);

            $this->logger->info('Email sent successfully', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'subject' => $message->getSubject()
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Email failed', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
