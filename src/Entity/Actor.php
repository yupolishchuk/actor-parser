<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActorRepository")
 */
class Actor
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Length(max=255)
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Assert\Length(max=255)
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageSrc;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $biography;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ActorPopularMovie", mappedBy="actor", orphanRemoval=true)
     */
    private $popularMovies;



    public function __construct()
    {
        $this->popularMovies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getImageSrc(): ?string
    {
        return $this->imageSrc;
    }

    public function setImageSrc(?string $imageSrc): self
    {
        $this->imageSrc = $imageSrc;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): self
    {
        $this->biography = $biography;

        return $this;
    }

    /**
     * @return Collection|ActorPopularMovie[]
     */
    public function getPopularMovies(): Collection
    {
        return $this->popularMovies;
    }

    public function addPopularMovie(ActorPopularMovie $popularMovie): self
    {
        if (!$this->popularMovies->contains($popularMovie)) {
            $this->popularMovies[] = $popularMovie;
            $popularMovie->setActor($this);
        }

        return $this;
    }

    public function removePopularMovie(ActorPopularMovie $popularMovie): self
    {
        if ($this->popularMovies->contains($popularMovie)) {
            $this->popularMovies->removeElement($popularMovie);
            // set the owning side to null (unless already changed)
            if ($popularMovie->getActor() === $this) {
                $popularMovie->setActor(null);
            }
        }

        return $this;
    }

}
