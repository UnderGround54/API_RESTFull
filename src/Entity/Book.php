<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getBooks", "createBook"])]
    #[OA\Property(description: 'Identifiant de livre')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getBooks", "createBook"])]
    #[Assert\NotBlank(message: "Le titre du livre est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le titre doit faire au moins {{ limit }} caractères", maxMessage: "Le titre ne peut pas faire plus de {{ limit }} caractères")]
    #[OA\Property(description: 'Titre du livre', type: 'string', maxLength: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getBooks", "createBook"])]
    #[OA\Property(description: 'Couverture du livre', type: 'string', maxLength: 255)]
    private ?string $coverText = null;

    #[ORM\ManyToOne(inversedBy: 'Books')]
    #[Groups(["getBooks", "createBook"])]
    #[OA\Property(ref: new Model(type: Author::class, groups: ["createBook"]))]
    private ?Author $author = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getBooks", "createBook"])]
    #[OA\Property(description: 'Commentaire du livre', type: 'string')]
    private ?string $comment = null;

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

    public function getCoverText(): ?string
    {
        return $this->coverText;
    }

    public function setCoverText(?string $coverText): static
    {
        $this->coverText = $coverText;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
