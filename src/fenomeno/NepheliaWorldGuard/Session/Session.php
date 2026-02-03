<?php

namespace fenomeno\NepheliaWorldGuard\Session;

use fenomeno\NepheliaWorldGuard\Exceptions\RegionCreationException;
use pocketmine\player\Player;
use pocketmine\world\Position;
use WeakMap;

class Session
{
    private static WeakMap $map;

    public static function get(Player $player): static
    {
        if (!isset(static::$map)) {
            static::$map = new WeakMap();
        }

        /** @noinspection PhpIllegalArrayKeyTypeInspection */
        return static::$map[$player] ??= new static;
    }

    public const STAGE_START  = 0;
    public const STAGE_ZONE_A = 1;
    public const STAGE_ZONE_B = 2;
    public const STAGE_END    = self::STAGE_ZONE_B;

    public const MODE_NONE     = 0;
    public const MODE_CREATING = 1;
    public const MODE_REDEFINE = 2;

    private int $mode           = self::MODE_NONE;
    private ?int $currentStage  = null;
    private array $positions    = [];
    private ?string $regionName = null;
    private array $data         = [];

    public function startCreating(string $name, array $data): void
    {
        $this->mode         = self::MODE_CREATING;
        $this->currentStage = self::STAGE_START;
        $this->positions    = [];
        $this->regionName   = $name;
        $this->data         = $data;
    }

    public function endCreating(): void
    {
        $this->reset();
    }

    public function isCreating(): bool
    {
        return $this->mode === self::MODE_CREATING;
    }

    public function startRedefining(string $name, array $data): void
    {
        $this->mode         = self::MODE_REDEFINE;
        $this->currentStage = self::STAGE_START;
        $this->positions    = [];
        $this->regionName   = $name;
        $this->data         = $data;
    }

    public function endRedefining(): void
    {
        $this->reset();
    }

    public function isRedefining(): bool
    {
        return $this->mode === self::MODE_REDEFINE;
    }

    public function isInEditMode(): bool
    {
        return $this->mode !== self::MODE_NONE;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function getModeString(): string
    {
        return match ($this->mode) {
            self::MODE_CREATING => 'creating',
            self::MODE_REDEFINE => 'redefining',
            default             => 'none',
        };
    }

    public function reset(): void
    {
        $this->mode         = self::MODE_NONE;
        $this->currentStage = null;
        $this->positions    = [];
        $this->regionName   = null;
        $this->data         = [];
    }

    public function cancel(): void
    {
        $this->reset();
    }

    public function setCurrentStage(?int $stage): void
    {
        $this->currentStage = $stage;
    }

    public function getCurrentStage(): ?int
    {
        return $this->currentStage;
    }

    /**
     * @throws RegionCreationException
     */
    public function addPosition(int $stage, Position $position): void
    {
        if ($stage < self::STAGE_START || $stage > self::STAGE_END) {
            throw RegionCreationException::invalidStage($stage);
        }

        if (isset($this->positions[$stage])) {
            throw RegionCreationException::stageAlreadySet($stage);
        }

        $this->positions[$stage] = $position;
    }

    /**
     * @throws RegionCreationException
     */
    public function getPosition(int $stage): Position
    {
        if (!isset($this->positions[$stage])) {
            throw RegionCreationException::positionNotSet($stage);
        }
        return $this->positions[$stage];
    }

    /**
     * @throws RegionCreationException
     */
    public function getRegionName(): string
    {
        if ($this->isInEditMode() && $this->regionName === null) {
            throw RegionCreationException::noNameSet();
        }
        return $this->regionName ?? '';
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function isExtended(): bool
    {
        return (bool) ($this->data['extended'] ?? false);
    }

    public function isGlobal(): bool
    {
        return (bool) ($this->data['global'] ?? false);
    }
}