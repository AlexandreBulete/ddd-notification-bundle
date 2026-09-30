<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

/**
 * A subscription's channels as a JSON array of channel values.
 */
final class ChannelsType extends Type
{
    public const NAME = 'notification_channels';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonbTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        if (!is_array($value)) {
            throw new \InvalidArgumentException('Expected a list of channels.');
        }

        $values = [];
        foreach ($value as $channel) {
            if (!$channel instanceof Channel) {
                throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', Channel::class, get_debug_type($channel)));
            }
            $values[] = $channel->value;
        }

        return json_encode($values, \JSON_THROW_ON_ERROR);
    }

    /**
     * @return list<Channel>
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        if ($value === null) {
            return [];
        }
        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }
        if (!is_string($value)) {
            throw ValueNotConvertible::new($value, self::NAME, 'expected a JSON string');
        }
        try {
            $decoded = json_decode($value, true, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ValueNotConvertible::new($value, self::NAME, $e->getMessage(), $e);
        }
        if (!is_array($decoded)) {
            throw ValueNotConvertible::new($value, self::NAME, 'expected a JSON array');
        }

        $channels = [];
        foreach ($decoded as $c) {
            if (!is_string($c)) {
                throw ValueNotConvertible::new($value, self::NAME, 'channels are strings');
            }
            $channels[] = Channel::from($c);
        }

        return $channels;
    }
}
