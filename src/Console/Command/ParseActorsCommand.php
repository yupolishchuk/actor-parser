<?php

namespace App\Console\Command;

use App\Service\ActorParser;
use App\Service\ActorHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ParseActorsCommand extends Command
{
    private $container;
    private $entityManager;
    private $validator;
    private $actorsSaved;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        parent::__construct();
        $this->container = $container;
        $this->entityManager = $em;
        $this->validator = $validator;
        $this->actorsSaved = 0;
    }


    protected function configure()
    {
        $this
            ->setName('parse:actors')
            ->setDescription('Parse actors and save to db')
            ->addArgument(
                'quantity',
                InputArgument::OPTIONAL,
                'How much new actors do you want to parse and save?',
                50
            )
            ->addArgument(
                'from',
                InputArgument::OPTIONAL,
                'Set actor id to start parsing',
                1
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $quantity = $input->getArgument('quantity');
        $fromId = $input->getArgument('from');

        if ($quantity) {
            $this->processActors($quantity, $fromId);
            $text = $this->actorsSaved.' new actors parsed and saved'.PHP_EOL.'Done!';
        }

        $output->writeln($text);
    }


    protected function processActors(int $quantity = 50, int $fromActorId = 1): void
    {
        $processed = 0;

        while($processed < $quantity) {
            print_r('Get actor links starting from id: '.$fromActorId.PHP_EOL);
            $url = "https://www.imdb.com/search/name/?gender=male,female&start=$fromActorId&ref_=rlm";
            $actorLinks = (new ActorParser($url))->getActorLinks();

            foreach($actorLinks as $actorLink) {
                try {
                    if ($res = (new ActorParser($actorLink))->getActor()) {
                        (new ActorHelper(
                            $this->entityManager,
                            $this->validator,
                            $res->actor,
                            $res->movies
                        ))->save();
                        $this->actorsSaved++;
                        print_r($this->actorsSaved.'. '.$res->actor->getName().' saved'.PHP_EOL);
                    }

                } catch (\Exception $e) {
                    print_r($e->getMessage());
                    continue;
                }
                $processed++;
            }
            $fromActorId = $fromActorId + count($actorLinks);
        }
    }
}