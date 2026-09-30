<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Tests\Fixture;

use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use AlexandreBulete\DddFoundation\Infrastructure\InMemory\InMemoryRepository;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\Repository\RecipientRepositoryInterface;

/**
 * @extends InMemoryRepository<Recipient>
 */
final class InMemoryRecipientRepository extends InMemoryRepository implements RecipientRepositoryInterface
{
    public function save(Recipient $e): void
    {
        $this->entities[(string) $e->id] = $e;
    }

    public function remove(Recipient $e): void
    {
        unset($this->entities[(string) $e->id]);
    }

    public function findById(IdentifierVO $id): ?Recipient
    {
        return $this->entities[(string) $id] ?? null;
    }
}
