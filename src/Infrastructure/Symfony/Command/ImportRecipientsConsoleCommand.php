<?php

declare(strict_types=1);

namespace AlexandreBulete\DddNotificationBundle\Infrastructure\Symfony\Command;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddNotificationBundle\Application\Command\RegisterRecipient\RegisterRecipientCommand;
use AlexandreBulete\DddNotificationBundle\Application\Command\UpdateRecipient\UpdateRecipientCommand;
use AlexandreBulete\DddNotificationBundle\Application\Query\FindRecipients\FindRecipientsQuery;
use AlexandreBulete\DddNotificationBundle\Domain\Model\Recipient;
use AlexandreBulete\DddNotificationBundle\Domain\ValueObject\Channel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

/**
 * Imports recipients from a YAML file — a team sheet becomes notification
 * recipients without typing them in the back office. Reconciles by name
 * (case-insensitive): a new person is created, an existing one has their
 * handles refreshed; nobody is deleted. Idempotent, safe to re-run.
 *
 * Shape (top key `recipients` or `team`), every field but a name is optional:
 *
 *     recipients:
 *       - name: Quentin Mendel          # or firstname + lastname
 *         email: q.mendel@boeki.fr
 *         slack: U093JTMPVC0
 *         sms: "+33600000000"
 *         whatsapp: "+33600000000"
 */
#[AsCommand(
    name: 'notification:import-recipients',
    description: 'Create or update notification recipients from a YAML file.',
)]
final class ImportRecipientsConsoleCommand extends Command
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly QueryBusInterface $queryBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the YAML file.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would change without writing anything.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $file = $input->getArgument('file');
        if (!\is_string($file) || !is_file($file)) {
            $io->error(\sprintf('File not found: %s', \is_string($file) ? $file : ''));

            return Command::FAILURE;
        }

        $entries = $this->readEntries($file, $io);
        if (null === $entries) {
            return Command::FAILURE;
        }

        $dryRun = (bool) $input->getOption('dry-run');
        if ($dryRun) {
            $io->note('Dry run: nothing is written.');
        }

        $known = $this->recipientsByName();
        $rows = [];
        $created = $updated = $failed = 0;

        foreach ($entries as $index => $entry) {
            if (!\is_array($entry)) {
                $rows[] = [(string) ($index + 1), '<error>invalid entry</error>'];
                ++$failed;
                continue;
            }

            /** @var array<string, mixed> $entry */
            $name = $this->name($entry);
            if ('' === $name) {
                $rows[] = [(string) ($index + 1), '<error>no name</error>'];
                ++$failed;
                continue;
            }

            try {
                $handles = $this->handles($entry);
                $existing = $known[mb_strtolower($name)] ?? null;

                if (!$dryRun) {
                    $id = null !== $existing
                        ? $existing->id
                        : $this->commandBus->dispatch(new RegisterRecipientCommand($name))->id;
                    $this->commandBus->dispatch(new UpdateRecipientCommand($id, $name, $handles));
                }

                $rows[] = [$name, null !== $existing ? '<comment>updated</comment>' : '<info>imported</info>'];
                null !== $existing ? ++$updated : ++$created;
            } catch (\Throwable $e) {
                $rows[] = [$name, '<error>'.$e->getMessage().'</error>'];
                ++$failed;
            }
        }

        $io->table(['Recipient', 'Result'], $rows);
        $io->writeln(\sprintf('Imported: %d   Updated: %d   Failed: %d', $created, $updated, $failed));

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function name(array $entry): string
    {
        $name = $this->str($entry, 'name');
        if (null !== $name) {
            return $name;
        }

        $parts = array_filter(
            [$this->str($entry, 'firstname'), $this->str($entry, 'lastname')],
            static fn (?string $v): bool => null !== $v,
        );

        return trim(implode(' ', $parts));
    }

    /**
     * @param array<string, mixed> $entry
     *
     * @return array<string, ?string> channel value => address (null clears it)
     */
    private function handles(array $entry): array
    {
        return [
            Channel::Email->value => $this->str($entry, 'email'),
            Channel::Slack->value => $this->str($entry, 'slack'),
            Channel::Sms->value => $this->str($entry, 'sms'),
            Channel::WhatsApp->value => $this->str($entry, 'whatsapp'),
        ];
    }

    /**
     * Recipients keyed by lower-cased name, so "quentin" matches an existing
     * "Quentin".
     *
     * @return array<string, Recipient>
     */
    private function recipientsByName(): array
    {
        $recipients = [];
        foreach ($this->queryBus->ask(new FindRecipientsQuery()) as $recipient) {
            $recipients[mb_strtolower($recipient->name)] = $recipient;
        }

        return $recipients;
    }

    /**
     * @return list<mixed>|null the recipient entries, or null on a malformed file
     */
    private function readEntries(string $file, SymfonyStyle $io): ?array
    {
        try {
            $parsed = Yaml::parseFile($file);
        } catch (\Throwable $e) {
            $io->error(\sprintf('Invalid YAML: %s', $e->getMessage()));

            return null;
        }

        $list = \is_array($parsed) ? ($parsed['recipients'] ?? $parsed['team'] ?? null) : null;
        if (!\is_array($list)) {
            $io->error('Expected a top-level "recipients" (or "team") list.');

            return null;
        }

        return array_values($list);
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function str(array $entry, string $key): ?string
    {
        $value = $entry[$key] ?? null;
        if (\is_int($value) || \is_float($value)) {
            $value = (string) $value;
        }

        return \is_string($value) && '' !== trim($value) ? trim($value) : null;
    }
}
