<?php

namespace App\Entity;

use App\Repository\ContratRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=ContratRepository::class)
 */
class Contrat
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
    private $type;

    /**
     * @ORM\Column(type="date")
     */
    private $dateDeDep;

    /**
     * @ORM\Column(type="date")
     */
    private $dateDeRet;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="contrats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\OneToOne(targetEntity=Voiture::class, inversedBy="contrat", cascade={"persist", "remove"})
     */
    private $voiture;

    /**
     * @ORM\OneToOne(targetEntity=Facture::class, mappedBy="contrat", cascade={"persist", "remove"})
     */
    private $facture;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDateDeDep(): ?\DateTimeInterface
    {
        return $this->dateDeDep;
    }

    public function setDateDeDep(\DateTimeInterface $dateDeDep): self
    {
        $this->dateDeDep = $dateDeDep;

        return $this;
    }

    public function getDateDeRet(): ?\DateTimeInterface
    {
        return $this->dateDeRet;
    }

    public function setDateDeRet(\DateTimeInterface $dateDeRet): self
    {
        $this->dateDeRet = $dateDeRet;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getVoiture(): ?Voiture
    {
        return $this->voiture;
    }

    public function setVoiture(?Voiture $voiture): self
    {
        $this->voiture = $voiture;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validateDates(ExecutionContextInterface $context, $payload)
    {
        if(date('Y-m-d', strtotime($this->dateDeRet->format("Y-m-d"))) < date('Y-m-d', strtotime("+1 day", strtotime($this->dateDeDep->format("Y-m-d"))))){
            $context->buildViolation('Date de retour doit etre superieure au date de depart.')
                ->atPath('dateRet')
                ->addViolation();
        }
    }

    public function __toString() {
        return $this->getId() . ' - ' .  $this->getVoiture()->getMarque() . ' - ' . $this->getClient()->getNom();
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(Facture $facture): self
    {
        // set the owning side of the relation if necessary
        if ($facture->getContrat() !== $this) {
            $facture->setContrat($this);
        }

        $this->facture = $facture;

        return $this;
    }
}
