<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AudioUploadRepository")
 */
class AudioUpload
{
    const UPLOAD_STATUS_CHUNKED = 0;
    const UPLOAD_STATUS_UPLOADED = 1;
    const UPLOAD_STATUS_TRANSCRIBED = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"rest"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Groups({"rest"})
     */
    private $filename;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"rest"})
     */
    private $uploadDate;

    /**
     * 0: Chunked
     * 1: Uploaded
     * 2: Transcribed
     *
     * @ORM\Column(type="integer")
     * @Assert\Choice({0, 1, 2})
     * @Groups({"rest"})
     */
    private $status;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AudioUploadChunk", mappedBy="audioUpload", orphanRemoval=true)
     */
    private $audioUploadChunks;

    /**
     * @Groups({"rest"})
     *
     * @var string
     */
    private $audioUrl;

    /**
     * @return string
     */
    public function getAudioUrl(): string
    {
        return $this->audioUrl;
    }

    /**
     * @param string $audioUrl
     */
    public function setAudioUrl(string $audioUrl): void
    {
        $this->audioUrl = $audioUrl;
    }

    public function __construct()
    {
        $this->audioUploadChunks = new ArrayCollection();
        $this->setIsDeleted(false);
        $this->setStatus(self::UPLOAD_STATUS_CHUNKED);
        $this->setUploadDate(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getUploadDate(): ?\DateTimeInterface
    {
        return $this->uploadDate;
    }

    public function setUploadDate(\DateTimeInterface $uploadDate): self
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @Groups({"rest"})
     *
     * @return string
     */
    public function getStatusName(): string
    {
        return [
            'UPLOADING',
            'TRANSCRIBING',
            'COMPLETE',
        ][$this->status];
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @return Collection|AudioUploadChunk[]
     */
    public function getAudioUploadChunks(): Collection
    {
        return $this->audioUploadChunks;
    }

    public function addAudioUploadChunk(AudioUploadChunk $audioUploadChunk): self
    {
        if (!$this->audioUploadChunks->contains($audioUploadChunk)) {
            $this->audioUploadChunks[] = $audioUploadChunk;
            $audioUploadChunk->setAudioUpload($this);
        }

        return $this;
    }

    public function removeAudioUploadChunk(AudioUploadChunk $audioUploadChunk): self
    {
        if ($this->audioUploadChunks->contains($audioUploadChunk)) {
            $this->audioUploadChunks->removeElement($audioUploadChunk);
            // set the owning side to null (unless already changed)
            if ($audioUploadChunk->getAudioUpload() === $this) {
                $audioUploadChunk->setAudioUpload(null);
            }
        }

        return $this;
    }
}
