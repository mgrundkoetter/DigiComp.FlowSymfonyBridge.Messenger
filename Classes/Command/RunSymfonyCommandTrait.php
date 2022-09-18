<?php

namespace DigiComp\FlowSymfonyBridge\Messenger\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

trait RunSymfonyCommandTrait
{
    protected function run(Command $command)
    {
        $definition = $command->getDefinition();
        $definition->setArguments(\array_merge(
            [new InputArgument('command', InputArgument::REQUIRED)],
            $definition->getArguments()
        ));
        $definition->setOptions(\array_merge(
            [
                new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
                new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'),
            ],
            $definition->getOptions()
        ));
        $input = new ArgvInput(null, $command->getDefinition());
        $this->configureIO($input, $this->output->getOutput());
        $command->run($input, $this->output->getOutput());
    }

    protected function configureIO($input, $output)
    {
        switch ($shellVerbosity = (int)\getenv('SHELL_VERBOSITY')) {
            case -1:
                $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
                break;
            case 1:
                $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                break;
            case 2:
                $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
                break;
            case 3:
                $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
                break;
            default:
                $shellVerbosity = 0;
                break;
        }

        if (true === $input->hasParameterOption(['--quiet', '-q'], true)) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            $shellVerbosity = -1;
        } else {
            if (
                $input->hasParameterOption('-vvv', true)
                || $input->hasParameterOption('--verbose=3', true)
                || 3 === $input->getParameterOption('--verbose', false, true)
            ) {
                $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
                $shellVerbosity = 3;
            } elseif (
                $input->hasParameterOption('-vv', true)
                || $input->hasParameterOption('--verbose=2', true)
                || 2 === $input->getParameterOption('--verbose', false, true)
            ) {
                $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
                $shellVerbosity = 2;
            } elseif (
                $input->hasParameterOption('-v', true)
                || $input->hasParameterOption('--verbose=1', true)
                || $input->hasParameterOption('--verbose', true)
                || $input->getParameterOption('--verbose', false, true)
            ) {
                $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                $shellVerbosity = 1;
            }
        }

        if (-1 === $shellVerbosity) {
            $input->setInteractive(false);
        }

        \putenv('SHELL_VERBOSITY=' . $shellVerbosity);
        $_ENV['SHELL_VERBOSITY'] = $shellVerbosity;
        $_SERVER['SHELL_VERBOSITY'] = $shellVerbosity;
    }
}
