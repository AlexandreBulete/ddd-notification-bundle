# DDD Notification Bundle

Notify people **where they want it**: one topic, many recipients, each on their
own channel — email, Slack, WhatsApp. Delivered asynchronously. Standalone: no IAM, no project notion.

## The idea

- A **topic** names a kind of event (`scan.vulnerability_found`,
  `mission.deployment_done`). The code that notifies names its topic, never a
  channel.
- A **recipient** is a person, with an address per channel (a Slack id, an
  email, a phone number).
- A **subscription** routes a `(recipient, topic)` to some channels. *"Quentin +
  scan.vulnerability_found → Slack."*
- A **channel** delivers: email (Symfony Mailer), Slack (Symfony Notifier),
  WhatsApp (Meta's Cloud API).

The caller does one thing:

```php
$notifier->notify(new Notification(
    new Topic('scan.vulnerability_found'),
    'Vulnerability on staging',
    "• 04-exposed-files: var/log readable",
));
```

Who receives it, and on which channel, is the subscriptions' business — one
async delivery per `(recipient, channel)`, so a failing Slack never blocks the
SMS, and each retries on its own (ADR 0010).

## Install

```bash
composer require alexandrebulete/ddd-notification-bundle
composer require symfony/slack-notifier   # the channels you want
bin/console doctrine:migrations:migrate
```

Requires `alexandrebulete/ddd-symfony-bundle` ≥ 1.5 (tracing, buses). A
channel whose library is absent or that is not configured is simply not wired:
its deliveries are logged and skipped.

## Channels

| Channel | Needs | Handle |
|---|---|---|
| Email | `symfony/mailer` (`MAILER_DSN`), `notification.mail.from` | an email address |
| Slack | `symfony/slack-notifier`, its DSN in `config/packages/notifier.yaml` | a channel or user id |
| WhatsApp | `symfony/http-client`, `notification.whatsapp` | a phone number, `+33612345678` |

The `sms` channel exists in the model but has no sender yet.

### WhatsApp

Messages go straight to Meta's WhatsApp Cloud API, no reseller. On Meta's
side: a business app with the WhatsApp product, a dedicated sending number,
a permanent system user token, and a **message template**, approved by Meta,
since a business may only open a conversation with one. A template whose body
has two variables fits every notification:

> {{1}} — {{2}}

The notification's subject fills `{{1}}`, its body `{{2}}` (line breaks
become spaces). Meta bills each template message, at a per-country rate.

```yaml
notification:
    whatsapp:
        enabled: true
        phone_number_id: '%env(WHATSAPP_PHONE_NUMBER_ID)%'   # empty = channel off
        token: '%env(WHATSAPP_TOKEN)%'
        template: notification          # the approved template's name
        language: fr                    # the language it was approved in
        parameters: [subject, body]     # what fills {{1}}, {{2}}…; [] if none
        api_version: v23.0
```

A refused message (unknown template, number not on WhatsApp, expired token)
throws: Messenger retries it, then sends it to the failure transport, with
Meta's error code in the message.

## Managing recipients

In the back office (**Notifications**), or from the console:

```bash
bin/console notification:subscribe "Alice" scan.vulnerability_found slack "#alerts"
```

## Delivery

`DeliverNotification` is routed to `async`: deliveries run in the worker,
tracked like any asynchronous task (with an outbox package), retried on
failure. A channel with no sender wired is logged and skipped — never a crash.

## Guarantees

- **Sent if and only if the action commits**: the delivery is dispatched within
  the transaction of whatever notified (ADR 0010).
- **A recipient subscribed on a channel it has no handle for is skipped**, not
  an error.
- **Standalone**: no coupling to any user/IAM system — recipients are this
  bundle's own data.

## Development

```bash
composer install
composer qa    # phpstan (max + strict rules), deptrac, phpunit
```
