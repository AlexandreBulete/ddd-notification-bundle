<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

/**
 * A recipient's handles as a JSON object {channel: address} — JSONB on
 * PostgreSQL, the platform's JSON elsewhere.
 */
final class HandlesType extends Type
{
    public const NAME = 'notification_handles';

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
            throw new \InvalidArgumentException('Expected a map of handles.');
        }

        return json_encode((object) $value, \JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, string>
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
            throw ValueNotConvertible::new($value, self::NAME, 'expected a JSON object');
        }

        $handles = [];
        foreach ($decoded as $channel => $address) {
            if (!is_string($channel) || !is_string($address)) {
                throw ValueNotConvertible::new($value, self::NAME, 'handles are channel => address strings');
            }
            $handles[$channel] = $address;
        }

        return $handles;
    }
}
