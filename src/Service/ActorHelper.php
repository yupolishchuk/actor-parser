<?php


namespace App\Service;

use App\Entity\Actor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ActorHelper
{
    private $entityManager;
    private $validator;
    private $actor;
    private $movies = [];

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        Actor $actor,
        $movies
        )
    {
        $this->entityManager = $em;
        $this->validator = $validator;
        $this->actor = $actor;
        $this->movies = $movies;
    }


    public function save($data): void
    {
        $actor = $data->actor;
        $movies = $data->movies;

        if ($this->actorAlreadySaved($actor->getName())) {
            throw new \DomainException($actor->getName().' is already saved '.PHP_EOL);
        } else {
            if ($this->processEntity($actor)) {
                foreach ($movies as $movie) {
                    $this->processEntity($movie);
                }
                $this->entityManager->flush();
            }
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
        $savedActor = $this->entityManager
            ->getRepository(Actor::class)
            ->findOneByName($actorName);

        return $savedActor ? true : false;
    }
}