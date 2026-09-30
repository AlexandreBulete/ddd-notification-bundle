<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddFoundation\Application\Event\EventDispatcherInterface;
use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\RecipientRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends DoctrineRepository<Recipient>
 */
final class DoctrineRecipientRepository extends DoctrineRepository implements RecipientRepositoryInterface
{
    private const ALIAS = 'recipient';

    public function __construct(
        EntityManagerInterface $em,
        private readonly EventDispatcherInterface $events,
    ) {
        parent::__construct($em, Recipient::class, self::ALIAS);
    }

    public function save(Recipient $recipient): void
    {
        $this->em->wrapInTransaction(function () use ($recipient): void {
            $this->em->persist($recipient);
            $this->em->flush();
            foreach ($recipient->releaseEvents() as $event) {
                $this->events->dispatch($event);
            }
        });
    }

    public function remove(Recipient $recipient): void
    {
        $this->em->wrapInTransaction(function () use ($recipient): void {
            $this->em->remove($recipient);
            $this->em->flush();
            foreach ($recipient->releaseEvents() as $event) {
                $this->events->dispatch($event);
            }
        });
    }

    public function findById(IdentifierVO $id): ?Recipient
    {
        return $this->em->find(Recipient::class, $id->value());
    }
}
