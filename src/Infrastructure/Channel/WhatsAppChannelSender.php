<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Channel;

use AlexandreBulete\DddNotificationBundle\Application\Delivery\PendingDelivery;
use AlexandreBulete\DddNotificationBundle\Application\Port\ChannelSenderInterface;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Delivers on WhatsApp through Meta's WhatsApp Cloud API, without a reseller.
 *
 * A business may only open a conversation with an approved message template,
 * so every notification goes out as one: the configured template, whose body
 * variables receive the notification's subject then body ({{1}}, {{2}}). The
 * recipient's handle is a phone number in international format.
 *
 * Wired only when `notification.whatsapp` is enabled. A refused request
 * throws, so that Messenger retries the delivery.
 */
final readonly class WhatsAppChannelSender implements ChannelSenderInterface
{
    /** Meta refuses newlines, tabs and runs of spaces in template variables. */
    private const int PARAMETER_MAX_LENGTH = 1024;

    /**
     * @param list<'subject'|'body'> $parameters what fills the template's body variables, in order
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $phoneNumberId,
        private string $token,
        private string $template,
        private string $language,
        private array $parameters,
        private string $apiVersion,
    ) {
    }

    public function supports(Channel $channel): bool
    {
        return Channel::WhatsApp === $channel && '' !== $this->phoneNumberId && '' !== $this->token;
    }

    public function send(PendingDelivery $delivery): void
    {
        $template = ['name' => $this->template, 'language' => ['code' => $this->language]];
        if ([] !== $this->parameters) {
            $template['components'] = [[
                'type' => 'body',
                'parameters' => array_map(
                    fn (string $field): array => ['type' => 'text', 'text' => self::flatten('subject' === $field ? $delivery->subject : $delivery->body)],
                    $this->parameters,
                ),
            ]];
        }

        $response = $this->httpClient->request('POST', \sprintf('https://graph.facebook.com/%s/%s/messages', $this->apiVersion, rawurlencode($this->phoneNumberId)), [
            'auth_bearer' => $this->token,
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => self::phone($delivery->address),
                'type' => 'template',
                'template' => $template,
            ],
        ]);

        $status = $response->getStatusCode();
        if ($status >= 300) {
            /** @var array{error?: array{message?: string, code?: int}} $error */
            $error = $response->toArray(false);
            throw new \RuntimeException(\sprintf('WhatsApp refused the message (HTTP %d, code %s): %s', $status, $error['error']['code'] ?? '?', $error['error']['message'] ?? 'no detail'));
        }
    }

    /**
     * "+33 6 12 34 56 78" → "33612345678": the API wants digits only.
     */
    private static function phone(string $address): string
    {
        $digits = preg_replace('/\D+/', '', $address) ?? '';
        if ('' === $digits) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a phone number.', $address));
        }

        return $digits;
    }

    private static function flatten(string $text): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        return mb_substr('' === $text ? '-' : $text, 0, self::PARAMETER_MAX_LENGTH);
    }
}
