<?php
namespace TheRat\OmCms\I18nBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Dumper;
use TheRat\OmCms\I18nBundle\Entity\LocaleRepository;

class LocaleUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('om-cms:locale:update')
            ->setDescription('Update available locales from db')
            ->addOption('cache-clear', null, InputOption::VALUE_NONE, 'If set, cache will be cleared');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ConsoleOutput $output */
        $container = $this->getContainer();
        $logger = $container->get('logger');
        /** @var LocaleRepository $localeRepository */
        $localeRepository = $container->get('doctrine')->getManager()
            ->getRepository('OmCmsI18nBundle:Locale');
        $aliases = $localeRepository->getActiveAliases();

        if ($output->isDebug()) {
            $logger->debug('Find aliases', $aliases);
        }

        $parameters = [
            'parameters' => [
                'om_cms_i18n.locale.aliases' => $aliases
            ]
        ];
        $filename = $container->getParameter('om_cms_i18n.locale.filename');
        $fs = new Filesystem();
        $dumper = new Dumper();
        $data = $dumper->dump($parameters);
        $fs->dumpFile($filename, $data);

        if ($output->isDebug()) {
            $logger->debug('Dump data', ['filename' => $filename, $data]);
        }

        if ($input->getOption('cache-clear')) {
            $cmd = $this->getContainer()->getParameter('kernel.root_dir') . '/console ';
            $inputParameters = ['command' => 'cache:clear', '--env' => $container->getParameter('kernel.environment')];
            if ($output->getVerbosity() <= OutputInterface::VERBOSITY_NORMAL) {
                $inputParameters['--no-debug'] = null;
            }
            $cmd .= (new ArrayInput($inputParameters))->__toString();
            $process = new Process($cmd);
            $process->mustRun(function ($type, $buffer) use ($output) {
                if (Process::ERR === $type) {
                    $output->write('<error>' . $buffer . '</error>');
                } else {
                    if ($output->isDebug()) {
                        $output->write($buffer);
                    }
                }
            });
        }
    }
}
