<?php

namespace App\Console\Command;

use App\Service\ActorParser;
use App\Entity\Actor;
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
    private $doctrine;
    private $actorsSaved;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        parent::__construct();
        $this->container = $container;
        $this->entityManager = $em;
        $this->validator = $validator;
        $this->doctrine = $this->container->get('doctrine');
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
                        $this->save($res);
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


    private function save($data)
    {
        $actor = $data->actor;
        $movies = $data->movies;

        if ($this->actorAlreadySaved($actor->getName())) {
            print_r($actor->getName().' is already saved '.PHP_EOL);
        } else {
            if ($this->processEntity($actor)) {
                foreach ($movies as $movie) {
                    $this->processEntity($movie);
                }
                $this->entityManager->flush();
            }

            $this->actorsSaved++;
            print_r($this->actorsSaved.'. '.$actor->getName().' saved'.PHP_EOL);
        }
    }


    private function processEntity($entity): bool
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            print_r((string) $errors . PHP_EOL);
            return false;
        } else {
            $this->entityManager->persist($entity);
            return true;
        }
    }


    private function actorAlreadySaved(string $actorName): bool
    {
        $savedActor = $this->doctrine
            ->getRepository(Actor::class)
            ->findOneByName($actorName);

        return $savedActor ? true : false;
    }
}