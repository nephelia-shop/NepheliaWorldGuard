<?php

namespace fenomeno\NepheliaWorldGuard\Regions;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Main;
use pocketmine\world\Position;

class Region
{

    /**
     * @param string $name
     * @param Position $pos1
     * @param Position $pos2
     * @param bool $extended je gère ça à la création @see Listeners/CreationListener
     * @param bool $global
     * @param array $flags ici ce sont seulement les flags qui ont une valeur différente de la valeur par défaut
     * @param string|null $parent
     */
    public function __construct(
        public readonly string $name,
        public Position        $pos1,
        public Position        $pos2,
        public bool            $extended = false,
        public bool            $global   = false,
        public array           $flags    = [],
        public ?string         $parent   = null
    ){}

    public function getFlag(Flags $flag): mixed
    {
        if (isset($this->flags[$flag->value])) {
            return $this->flags[$flag->value];
        }

        if ($this->parent !== null) {
            $parentRegion = Main::getInstance()->getRegionsManager()->getRegion($this->parent);
            if ($parentRegion !== null) {
                return $parentRegion->getFlag($flag);
            }
        }

        return $flag->getDefault();
    }

    public function getLocalFlag(Flags $flag): mixed
    {
        return $this->flags[$flag->value] ?? null;
    }

    public function isFlagInherited(Flags $flag): bool
    {
        return ! isset($this->flags[$flag->value]) && $this->parent !== null;
    }

    public function setFlag(Flags $flag, mixed $value): void
    {
        if ($value === $flag->getDefault()) {
            unset($this->flags[$flag->value]);
        } else {
            $this->flags[$flag->value] = $value;
        }
    }

    public function isPositionInside(Position $position): bool
    {
        return $this->insideZone($position);
    }

    public function getAllFlags(): array
    {
        $result = [];
        foreach (Flags::cases() as $flag) {
            $result[$flag->value] = $this->getFlag($flag);
        }

        return $result;
    }

    private function insideZone(Position $position) : bool
    {
        [$x1, $y1, $z1] = [$this->pos1->x, $this->pos1->y, $this->pos1->z];
        [$x2, $y2, $z2] = [$this->pos2->x, $this->pos2->y, $this->pos2->z];

        $minX = min(intval($x1), intval($x2));
        $minY = min(intval($y1), intval($y2));
        $minZ = min(intval($z1), intval($z2));

        $maxX = max(intval($x1), intval($x2));
        $maxY = max(intval($y1), intval($y2));
        $maxZ = max(intval($z1), intval($z2));

        $x = $position->getFloorX();
        $y = $position->getFloorY();
        $z = $position->getFloorZ();

        $insideCoords = $x >= $minX && $x <= $maxX && $y >= $minY - 2 && $y <= $maxY + 2 && $z >= $minZ && $z <= $maxZ;

        if($this->global){
            return $insideCoords;
        } else {
            return $insideCoords && $position->world->getFolderName() === $this->pos1->world->getFolderName();
        }
    }

    public function getPriority(): int
    {
        return (int) $this->getFlag(Flags::Priority);
    }

    private function castValue(mixed $value): string
    {
        if(is_bool($value)){
            return $value ? "true" : "false";
        } elseif(is_array($value)){
            return implode(", ", $value);
        } elseif($value === null){
            return "none";
        }
        return (string) $value;
    }

    public function isFlagModified(Flags $flag): bool
    {
        return isset($this->flags[$flag->value]);
    }

    public function resetFlag(Flags $flag): void
    {
        unset($this->flags[$flag->value]);
    }

    public function getModifiedFlags(): array
    {
        return array_keys($this->flags);
    }

    public function resetAllFlags(): void
    {
        $this->flags = [];
    }

    public function hasParent(): bool
    {
        return $this->parent !== null;
    }

    public function setParent(?string $parentName): void
    {
        $this->parent = $parentName;
    }

    public function getParentName(): ?string
    {
        return $this->parent;
    }

    public function getAllFlagsWithSource(): array
    {
        $result = [];

        foreach (Flags::cases() as $flag) {
            $localValue = $this->getLocalFlag($flag);

            if ($localValue !== null) {
                $result[$flag->value] = [
                    'value'     => $localValue,
                    'inherited' => false,
                    'source'    => $this->name,
                ];
            } elseif ($this->parent !== null) {
                $parentRegion = Main::getInstance()->getRegionsManager()->getRegion($this->parent);
                if ($parentRegion !== null) {
                    $parentValue = $parentRegion->getFlag($flag);
                    $source      = $this->findFlagSource($flag);

                    $result[$flag->value] = [
                        'value'     => $parentValue,
                        'inherited' => true,
                        'source'    => $source,
                    ];
                } else {
                    $result[$flag->value] = [
                        'value'     => $flag->getDefault(),
                        'inherited' => false,
                        'source'    => 'default',
                    ];
                }
            } else {
                $result[$flag->value] = [
                    'value'     => $flag->getDefault(),
                    'inherited' => false,
                    'source'    => 'default',
                ];
            }
        }

        return $result;
    }

    private function findFlagSource(Flags $flag): string
    {
        if (isset($this->flags[$flag->value])) {
            return $this->name;
        }

        if ($this->parent !== null) {
            $parentRegion = Main::getInstance()->getRegionsManager()->getRegion($this->parent);
            if ($parentRegion !== null) {
                return $parentRegion->findFlagSource($flag);
            }
        }

        return 'default';
    }

    public function hasPluginBypass(): bool
    {
        $bypassPlugins = $this->getFlag(Flags::PluginBypass);

        return $bypassPlugins === true || $bypassPlugins === "true" || (is_array($bypassPlugins) && count($bypassPlugins) > 0);
    }

}