<?php

namespace App\Entity;

use App\Mapping\EntityBase;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;


/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class User extends EntityBase implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * users I follow
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="myFollowers")
     */
    private $followersOfMe;

    /**
     * users that follow me
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="followersOfMe")
     */
    private $myFollowers;



    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="user")
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="user", orphanRemoval=true)
     */
    private $comments;

    public function __construct()
    {
        $this->followersOfMe = new ArrayCollection();
        $this->myFollowers = new ArrayCollection();

        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|self[]
     */
    public function getFollowersOfMe(): Collection
    {
        return $this->followersOfMe;
    }

    public function addFollowersOfMe(self $followersOfMe): self
    {
        if (!$this->followersOfMe->contains($followersOfMe)) {
            $this->followersOfMe[] = $followersOfMe;
        }

        return $this;
    }

    public function removeFollowersOfMe(self $followersOfMe): self
    {
        $this->followersOfMe->removeElement($followersOfMe);

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getMyFollowers(): Collection
    {
        return $this->myFollowers;
    }

    public function addMyFollower(self $myFollower): self
    {
        if (!$this->myFollowers->contains($myFollower)) {
            $this->myFollowers[] = $myFollower;
            $myFollower->addFollowersOfMe($this);
        }

        return $this;
    }

    public function removeMyFollower(self $myFollower): self
    {
        if ($this->myFollowers->removeElement($myFollower)) {
            $myFollower->removeFollowersOfMe($this);
        }

        return $this;
    }



    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('email', new Assert\Email([
            'message' => 'The email "{{ value }}" is not a valid email.',
            'checkMX' => true,
        ]))->addPropertyConstraint('email', new Assert\NotBlank());
    }
}
