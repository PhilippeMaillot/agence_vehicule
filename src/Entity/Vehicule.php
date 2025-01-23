<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
class Vehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $marque = null;

    #[ORM\Column(length: 50)]
    private ?string $modele = null;

    #[ORM\Column(length: 7)]
    private ?string $immatriculation = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column]
    private ?float $prixparjour = null;

    #[ORM\Column]
    private ?bool $disponibilite = null;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(mappedBy: 'vehicule', targetEntity: Commentaire::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(mappedBy: 'vehicule', targetEntity: Reservation::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $reservations;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(string $immatriculation): static
    {
        $this->immatriculation = $immatriculation;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPrixparjour(): ?float
    {
        return $this->prixparjour;
    }

    public function setPrixparjour(float $prixparjour): static
    {
        if ($prixparjour < 20 || $prixparjour > 50) {
            throw new \InvalidArgumentException('Le prix par jour doit être compris entre 20 et 50.');
        }

        $this->prixparjour = $prixparjour;

        return $this;
    }

    public function isDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): static
    {
        $this->disponibilite = $disponibilite;

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Commentaire $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setVehicule($this);
        }

        return $this;
    }

    public function removeComment(Commentaire $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getVehicule() === $this) {
                $comment->setVehicule(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $Reservation): static
    {
        if (!$this->reservations->contains($Reservation)) {
            $this->reservations->add($Reservation);
            $Reservation->setVehicule($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $Reservation): static
    {
        if ($this->reservations->removeElement($Reservation)) {
            // set the owning side to null (unless already changed)
            if ($Reservation->getVehicule() === $this) {
                $Reservation->setVehicule(null);
            }
        }

        return $this;
    }
}
