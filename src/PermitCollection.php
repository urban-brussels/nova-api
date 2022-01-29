<?php

namespace UrbanBrussels\NovaApi;

class PermitCollection implements \Iterator
{

    public array $permits;
    protected int $position = 0;

    public function __construct()
    {
        $this->permits = [];
    }

    public function addPermit(Permit $permit): void
    {
        $this->permits[] = $permit;
    }

    public function removePermit(Permit $permit): void
    {
        $key = array_search($permit, $this->permits, true);
        unset($this->permits[$key]);
    }

    public function getPermits(): array
    {
        return $this->permits;
    }

    public function current(): int
    {
        return $this->permits[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->medias[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }
}