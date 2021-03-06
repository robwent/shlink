<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\CLI\Command\Shortcode;

use Shlinkio\Shlink\Core\Exception\EntityDoesNotExistException;
use Shlinkio\Shlink\Core\Exception\InvalidShortCodeException;
use Shlinkio\Shlink\Core\Service\UrlShortenerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\I18n\Translator\TranslatorInterface;

class ResolveUrlCommand extends Command
{
    const NAME = 'shortcode:parse';

    /**
     * @var UrlShortenerInterface
     */
    private $urlShortener;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(UrlShortenerInterface $urlShortener, TranslatorInterface $translator)
    {
        $this->urlShortener = $urlShortener;
        $this->translator = $translator;
        parent::__construct(null);
    }

    public function configure()
    {
        $this->setName(self::NAME)
             ->setDescription($this->translator->translate('Returns the long URL behind a short code'))
             ->addArgument(
                 'shortCode',
                 InputArgument::REQUIRED,
                 $this->translator->translate('The short code to parse')
             );
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $shortCode = $input->getArgument('shortCode');
        if (! empty($shortCode)) {
            return;
        }

        $io = new SymfonyStyle($input, $output);
        $shortCode = $io->ask(
            $this->translator->translate('A short code was not provided. Which short code do you want to parse?')
        );
        if (! empty($shortCode)) {
            $input->setArgument('shortCode', $shortCode);
        }
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $shortCode = $input->getArgument('shortCode');

        try {
            $url = $this->urlShortener->shortCodeToUrl($shortCode);
            $output->writeln(
                \sprintf('%s <info>%s</info>', $this->translator->translate('Long URL:'), $url->getLongUrl())
            );
        } catch (InvalidShortCodeException $e) {
            $io->error(
                \sprintf($this->translator->translate('Provided short code "%s" has an invalid format.'), $shortCode)
            );
        } catch (EntityDoesNotExistException $e) {
            $io->error(
                \sprintf($this->translator->translate('Provided short code "%s" could not be found.'), $shortCode)
            );
        }
    }
}
