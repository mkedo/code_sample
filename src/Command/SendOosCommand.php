<?php

namespace App\Command;

use App\Oos\Publisher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Отправить пакеты ожидающие очереди в __REDACTED__.
 */
class SendOosCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:oos:publish';

    /**
     * @var Publisher
     */
    private $exchange;

    /**
     * SendOosCommand constructor.
     * @param Publisher $exchange
     */
    public function __construct(Publisher $exchange)
    {
        parent::__construct();
        $this->exchange = $exchange;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            return Command::SUCCESS;
        }

        $output->writeln("Sending OOS packets ...");
        $count = $this->exchange->publishAll($output);
        $output->writeln("Done. Packets sent: " . $count);
        $this->release();
        return Command::SUCCESS;
    }
}
