<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AudioUploadChunkRepository")
 * @ORM\Table(name="audio_upload_chunk",
 *    uniqueConstraints={
 *        @UniqueConstraint(name="chunk_unique",
 *            columns={"audio_upload_id", "chunk_number"})
 *    }
 * )
 * @UniqueEntity(
 *     fields={"audioUpload", "chunkNumber"},
 *     message="Cannot save duplicate chunk"
 * )
 */
class AudioUploadChunk
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AudioUpload", inversedBy="audioUploadChunks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $audioUpload;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\Column(type="integer")
     */
    private $chunkNumber;

    /**
     * @ORM\Column(type="datetime")
     */
    private $uploadDate;

    /**
     * @var string
     */
    private $audioData;

    /**
     * @return string
     */
    public function getAudioData()
    {
        return $this->audioData;
    }

    /**
     * @param string $audioData
     */
    public function setAudioData(string $audioData): void
    {
        $this->audioData = $audioData;
    }

    public function __construct()
    {
        $this->setUploadDate(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAudioUpload(): ?AudioUpload
    {
        return $this->audioUpload;
    }

    public function setAudioUpload(?AudioUpload $audioUpload): self
    {
        $this->audioUpload = $audioUpload;

        return $this;
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

    public function getChunkNumber(): ?int
    {
        return $this->chunkNumber;
    }

    public function setChunkNumber(int $chunkNumber): self
    {
        $this->chunkNumber = $chunkNumber;

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
}
