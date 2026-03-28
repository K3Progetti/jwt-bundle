<?php

namespace K3Progetti\JwtBundle\Http;

use JsonSerializable;

class Result implements JsonSerializable
{
    private ?string $message = null;
    private ?array $data = [];
    private mixed $totalRows = null;

    public function __construct(array $config = [])
    {
        foreach ($config as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'data' => $this->data,
            'totalRows' => $this->totalRows,
        ];
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    public function setTotalRows(mixed $totalRows): static
    {
        $this->totalRows = $totalRows;
        return $this;
    }

    public function getTotalRows(): mixed
    {
        return $this->totalRows;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}