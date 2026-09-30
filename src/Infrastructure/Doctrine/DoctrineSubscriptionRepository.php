<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddFoundation\Application\Event\EventDispatcherInterface;
use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Subscription;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\SubscriptionRepositoryInterface;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\RecipientId;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Topic;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends DoctrineRepository<Subscription>
 */
final class DoctrineSubscriptionRepository extends DoctrineRepository implements SubscriptionRepositoryInterface
{
    private const ALIAS = 'subscription';

    public function __construct(
        EntityManagerInterface $em,
        private readonly EventDispatcherInterface $events,
    ) {
        parent::__construct($em, Subscription::class, self::ALIAS);
    }

    public function save(Subscription $subscription): void
    {
        $this->em->wrapInTransaction(function () use ($subscription): void {
            $this->em->persist($subscription);
            $this->em->flush();
            foreach ($subscription->releaseEvents() as $event) {
                $this->events->dispatch($event);
            }
        });
    }

    public function remove(Subscription $subscription): void
    {
        $this->em->wrapInTransaction(function () use ($subscription): void {
            $this->em->remove($subscription);
            $this->em->flush();
            foreach ($subscription->releaseEvents() as $event) {
                $this->events->dispatch($event);
            }
        });
    }

    public function findById(IdentifierVO $id): ?Subscription
    {
        return $this->em->find(Subscription::class, $id->value());
    }

    public function forTopic(Topic $topic): array
    {
        /** @var list<Subscription> */
        return $this->query()
            ->andWhere(self::ALIAS . '.topic = :topic')
            ->setParameter('topic', $topic->value())
            ->getQuery()
            ->getResult();
    }

    public function findFor(RecipientId $recipientId, Topic $topic): ?Subscription
    {
        /** @var ?Subscription */
        return $this->query()
            ->andWhere(self::ALIAS . '.recipientId = :recipient')
            ->andWhere(self::ALIAS . '.topic = :topic')
            ->setParameter('recipient', $recipientId->toRfc4122())
            ->setParameter('topic', $topic->value())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countForRecipient(RecipientId $recipientId): int
    {
        $count = (int) $this->query()
            ->select('COUNT(' . self::ALIAS . '.id)')
            ->andWhere(self::ALIAS . '.recipientId = :recipient')
            ->setParameter('recipient', $recipientId->toRfc4122())
            ->getQuery()
            ->getSingleScalarResult();

        return max(0, $count);
    }
}
