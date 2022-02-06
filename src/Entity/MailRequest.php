<?php

namespace App\Entity;

use App\Interface\MailRequestStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(MailRequestRepository::class)]
class MailRequest
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 255)]
    private string $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $sender;

    #[ORM\Column(type: "string", length: 255)]
    private string $recipient;

    #[ORM\Column(type: "text")]
    private string $message;

    #[ORM\Column(type: "boolean")]
    private bool $isSubmitted = false;

    /** @see MailRequestStatus */
    #[ORM\Column(type: "string", length: 255)]
    private string $status = 'processing';

    #[ORM\Column(type: "integer")]
    private int $priority = 0;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function setSender(string $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function setRecipient(string $recipient): self
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function isSubmitted(): bool
    {
        return $this->isSubmitted;
    }

    public function setIsSubmitted(bool $isSubmitted): self
    {
        $this->isSubmitted = $isSubmitted;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }
}
