<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Config\Grid;

class Field
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $label;

    /**
     * @var bool
     */
    private $enabled = true;

    private $sorting = false;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var int
     *
     * Position equals to 100 to ensure that wile sorting fields by position ASC
     * the fields positioned by default will be last
     */
    private $position = 100;

    /**
     * @param string $name
     * @param string $type
     */
    private function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;

        $this->path = $name;
        $this->label = $name;
    }

    /**
     * @param string $name
     * @param string $type
     *
     * @return self
     */
    public static function fromNameAndType(string $name, string $type): self
    {
        return new self($name, $type);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return false !== $this->sorting;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param mixed $sorting
     *
     * @return self
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;

        return $this;
    }
}
