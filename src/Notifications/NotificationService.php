<?php

namespace App\Notifications;

use App\Entity\Contragent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Сервис уведомлений.
 */
class NotificationService
{
    /**
     * @var Template
     */
    private $template;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * NotificationService constructor.
     * @param Template $template
     * @param EntityManagerInterface $em
     */
    public function __construct(Template $template, EntityManagerInterface $em)
    {
        $this->template = $template;
        $this->em = $em;
    }

    /**
     * Отослать уведомление пользователю.
     * @param User $user
     * @param Notification $notification
     */
    public function sendToUser(User $user, Notification $notification)
    {
        $notificationParts = $notification->format($this->template);

        $n = new \App\Entity\Notification();
        $n->setSubject($notificationParts['subject']);
        $n->setBody($notificationParts['body']);
        $n->setReceiver($user);
        $n->setPlan($notification->getPlan());
        $this->em->persist($n);
        $this->em->flush();
    }

    /**
     * Отослать уведомление пользователям организации.
     * @param Contragent $contragent
     * @param Notification $notification
     */
    public function sendToContragent(Contragent $contragent, Notification $notification)
    {
        foreach ($contragent->getUsers() as $user) {
            $this->sendToUser($user, $notification);
        }
    }
}
