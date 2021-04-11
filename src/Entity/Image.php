<?php

namespace App\Entity;

use App\Mapping\EntityBase;
use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Image extends EntityBase
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
    private $image_filename;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="images")
     * @ORM\JoinColumn(nullable=false)
     */
    private $post;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageFilename(): ?string
    {
        return $this->image_filename;
    }

    public function setImageFilename(string $image_filename): self
    {
        $this->image_filename = $image_filename;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }
}
