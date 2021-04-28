<?php

namespace Luminee\Migrations\Console\Supporters;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;

class Output
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct($app)
    {
        $output = $app->make(OutputInterface::class);
        $this->output = new SymfonyStyle(new ArgvInput(), $output);
    }

    public function getInterface()
    {
        return $this->output;
    }

    public function ask($question, $default = null, $validator = null)
    {
        return $this->output->ask($question, $default, $validator);
    }

    public function confirm($question, $default = true)
    {
        return $this->output->confirm($question, $default);
    }

    public function getFormatter()
    {
        return $this->output->getFormatter();
    }

    public function newLine($count = 1)
    {
        return $this->output->newLine($count);
    }

    public function secret($question, $fallback = true)
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }

    public function choice($question, array $choices, $default = null)
    {
        return $this->output->choice($question, $choices, $default);
    }

    public function writeln($messages, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->output->writeln($messages, $type);
    }

    public function table(array $headers, array $rows)
    {
        return $this->output->table($headers, $rows);
    }

    public function progressStart($max = 0)
    {
        $this->output->progressStart($max);
    }

    public function progressAdvance($step = 1)
    {
        $this->output->progressAdvance($step);
    }

    public function progressFinish()
    {
        $this->output->progressFinish();
    }

    public function createProgressBar($max = 0)
    {
        return $this->output->createProgressBar($max);
    }


}