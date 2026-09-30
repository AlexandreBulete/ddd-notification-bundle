# DDD Notification Bundle

Notify people **where they want it**: one topic, many recipients, each on their
own channel — Slack, email, SMS, WhatsApp. Built on Symfony Notifier, delivered
asynchronously. Standalone: no IAM, no project notion.

## The idea

- A **topic** names a kind of event (`scan.vulnerability_found`,
  `mission.deployment_done`). The code that notifies names its topic, never a
  channel.
- A **recipient** is a person, with an address per channel (a Slack id, an
  email, a phone number).
- A **subscription** routes a `(recipient, topic)` to some channels. *"Quentin +
  scan.vulnerability_found → Slack."*
- A **channel** delivers, bridging to a Symfony Notifier transport.

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

Requires `alexandrebulete/ddd-symfony-bundle` ≥ 1.5 (tracing, buses). Channels
are Symfony Notifier transports: install the bridge for each (Slack, Twilio for
SMS/WhatsApp…) and configure its DSN in `config/packages/notifier.yaml`. A
channel whose bridge is absent is simply not wired.

## Managing recipients

Until the back office lands, a one-liner:

```bash
bin/console notification:subscribe "Quentin" scan.vulnerability_found slack "#alerts"
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
