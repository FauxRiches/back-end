<?php

namespace App\Entity;

use App\Repository\SongRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SongRepository::class)]
class Song
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['song:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['song:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['song:read'])]
    private ?string $artist = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['song:read'])]
    private ?string $album = null;

    #[ORM\Column(length: 25)]
    #[Groups(['song:read'])]
    private ?string $status = null;

    /**
     * @var Collection<int, Link>
     */
    #[ORM\ManyToMany(targetEntity: Link::class, inversedBy: 'songs')]
    private Collection $link;

    public function __construct()
    {
        $this->links = new ArrayCollection();
        $this->link = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(string $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    public function getAlbum(): ?string
    {
        return $this->album;
    }

    public function setAlbum(?string $album): static
    {
        $this->album = $album;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Link>
     */
    public function getLink(): Collection
    {
        return $this->link;
    }

    public function addLink(Link $link): static
    {
        if (!$this->link->contains($link)) {
            $this->link->add($link);
        }

        return $this;
    }

    public function removeLink(Link $link): static
    {
        $this->link->removeElement($link);

        return $this;
    }
}
