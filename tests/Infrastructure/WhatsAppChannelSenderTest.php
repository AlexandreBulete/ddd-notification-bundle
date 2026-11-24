<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Tests\Infrastructure;

use AlexandreBulete\DddNotificationBundle\Application\Delivery\PendingDelivery;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use AlexandreBulete\DddNotificationBundle\Infrastructure\Channel\WhatsAppChannelSender;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

final class WhatsAppChannelSenderTest extends TestCase
{
    #[Test]
    public function it_sends_the_notification_as_the_configured_template(): void
    {
        /** @var array{method: string, url: string, headers: list<string>, body: string}|null $sent */
        $sent = null;
        $client = new MockHttpClient(static function (string $method, string $url, array $options) use (&$sent): JsonMockResponse {
            /** @var array{headers: list<string>, body: string} $options */
            $sent = ['method' => $method, 'url' => $url, 'headers' => $options['headers'], 'body' => $options['body']];

            return new JsonMockResponse(['messages' => [['id' => 'wamid.1']]]);
        });

        $this->sender($client)->send($this->delivery("New message\non the site", 'Read it: https://example.test/admin/messages/1'));

        self::assertNotNull($sent);
        self::assertSame('POST', $sent['method']);
        self::assertSame('https://graph.facebook.com/v23.0/1234567890/messages', $sent['url']);
        self::assertContains('Authorization: Bearer secret-token', $sent['headers']);
        self::assertSame([
            'messaging_product' => 'whatsapp',
            'to' => '33612345678',
            'type' => 'template',
            'template' => [
                'name' => 'new_notice',
                'language' => ['code' => 'fr'],
                'components' => [[
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => 'New message on the site'],
                        ['type' => 'text', 'text' => 'Read it: https://example.test/admin/messages/1'],
                    ],
                ]],
            ],
        ], json_decode($sent['body'], true));
    }

    #[Test]
    public function a_refused_message_throws_so_that_it_is_retried(): void
    {
        $client = new MockHttpClient(new JsonMockResponse(['error' => ['message' => 'Template name does not exist', 'code' => 132001]], ['http_code' => 404]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('132001');
        $this->sender($client)->send($this->delivery('Subject', 'Body'));
    }

    #[Test]
    public function it_handles_only_whatsapp_and_only_once_configured(): void
    {
        $client = new MockHttpClient();

        self::assertTrue($this->sender($client)->supports(Channel::WhatsApp));
        self::assertFalse($this->sender($client)->supports(Channel::Sms));
        self::assertFalse((new WhatsAppChannelSender($client, '', '', 'new_notice', 'fr', [], 'v23.0'))->supports(Channel::WhatsApp));
    }

    private function sender(MockHttpClient $client): WhatsAppChannelSender
    {
        return new WhatsAppChannelSender($client, '1234567890', 'secret-token', 'new_notice', 'fr', ['subject', 'body'], 'v23.0');
    }

    private function delivery(string $subject, string $body): PendingDelivery
    {
        return new PendingDelivery('contact.message_received', 'Reader', Channel::WhatsApp, '+33 6 12 34 56 78', $subject, $body);
    }
}
