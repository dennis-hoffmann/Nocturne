<?php

namespace App\Entity;

use App\Repository\KodiImageTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=KodiImageTypeRepository::class)
 */
class KodiImageType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=KodiImage::class, mappedBy="imageType")
     */
    private $kodiImages;

    /**
     * @ORM\Column(type="integer")
     */
    private $width;

    /**
     * @ORM\Column(type="integer")
     */
    private $height;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="integer")
     */
    private $compression;

    public function __construct()
    {
        $this->kodiImages = new ArrayCollection();
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

    /**
     * @return Collection|KodiImage[]
     */
    public function getKodiImages(): Collection
    {
        return $this->kodiImages;
    }

    public function addKodiImage(KodiImage $kodiImage): self
    {
        if (!$this->kodiImages->contains($kodiImage)) {
            $this->kodiImages[] = $kodiImage;
            $kodiImage->setImageType($this);
        }

        return $this;
    }

    public function removeKodiImage(KodiImage $kodiImage): self
    {
        if ($this->kodiImages->contains($kodiImage)) {
            $this->kodiImages->removeElement($kodiImage);
            // set the owning side to null (unless already changed)
            if ($kodiImage->getImageType() === $this) {
                $kodiImage->setImageType(null);
            }
        }

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getCompression(): ?int
    {
        return $this->compression;
    }

    public function setCompression(int $compression): self
    {
        $this->compression = $compression;

        return $this;
    }
}
