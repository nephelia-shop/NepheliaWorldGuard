<?php

namespace fenomeno\NepheliaWorldGuard\Regions;

use fenomeno\NepheliaWorldGuard\Main;
use pocketmine\world\Position;

class RegionsManager
{

    private RegionRepository $repository;

    /** @var array<string, Region> */
    private array $regions = [];

    public function __construct(
        private readonly Main $main
    ){
        $this->repository = new RegionRepository($this->main);

        $this->reload();
    }

    public function reload(): void
    {
        $this->repository->init();
        $this->regions = $this->repository->load();
    }

    public function saveAll(): bool
    {
        return $this->repository->saveAll($this->regions);
    }

    public function flush(): bool
    {
        return $this->repository->flush();
    }

    public function getRegion(string $regionName): ?Region
    {
        return $this->regions[$regionName] ?? null;
    }

    public function getRegionNameAt(Position $position): ?string
    {
        $highestPriority = -1;
        $regionName      = null;

        foreach ($this->regions as $region) {
            if ($region->isPositionInside($position)) {
                $priority = $region->getPriority($this);
                if ($priority > $highestPriority) {
                    $highestPriority = $priority;
                    $regionName      = $region->name;
                }
            }
        }

        return $regionName;
    }

    public function getRegionAt(Position $position): ?Region
    {
        $name = $this->getRegionNameAt($position);
        return $name !== null ? $this->getRegion($name) : null;
    }

    /** @return array<string, Region> */
    public function getAllRegions(): array
    {
        return $this->regions;
    }

    public function exists(string $name): bool
    {
        return isset($this->regions[$name]);
    }

    public function create(
        string $name,
        Position $pos1,
        Position $pos2,
        bool $extended = false,
        bool $global = false,
        ?string $parent = null
    ): ?Region {
        if ($parent !== null && ! $this->exists($parent)) {
            return null;
        }

        if ($parent !== null && $this->wouldCreateCircularReference($name, $parent)) {
            return null;
        }

        $region = new Region($name, $pos1, $pos2, $extended, $global, [], $parent);
        $this->regions[$name] = $region;

        if (! $this->repository->save($region)) {
            unset($this->regions[$name]);
            return null;
        }

        return $region;
    }

    public function setParent(string $regionName, ?string $parentName): bool
    {
        $region = $this->getRegion($regionName);
        if ($region === null) {
            return false;
        }

        if ($parentName !== null) {
            if (! $this->exists($parentName)) {
                return false;
            }

            if ($parentName === $regionName) {
                return false;
            }

            if ($this->wouldCreateCircularReference($regionName, $parentName)) {
                return false;
            }
        }

        $region->setParent($parentName);
        return $this->saveRegion($region);
    }

    public function getParent(string $regionName): ?Region
    {
        $region = $this->getRegion($regionName);
        if ($region === null || $region->parent === null) {
            return null;
        }

        return $this->getRegion($region->parent);
    }

    public function getParentChain(string $regionName): array
    {
        $chain   = [];
        $current = $this->getRegion($regionName);

        while ($current !== null && $current->parent !== null) {
            $parent = $this->getRegion($current->parent);
            if ($parent === null) {
                break;
            }

            if (isset($chain[$parent->name])) {
                break;
            }

            $chain[$parent->name] = $parent;
            $current = $parent;
        }

        return $chain;
    }

    public function getChildren(string $parentName): array
    {
        $children = [];

        foreach ($this->regions as $region) {
            if ($region->parent === $parentName) {
                $children[$region->name] = $region;
            }
        }

        return $children;
    }

    public function getAllDescendants(string $parentName): array
    {
        $descendants = [];
        $children    = $this->getChildren($parentName);

        foreach ($children as $child) {
            $descendants[$child->name] = $child;
            $childDescendants = $this->getAllDescendants($child->name);
            $descendants = array_merge($descendants, $childDescendants);
        }

        return $descendants;
    }

    private function wouldCreateCircularReference(string $regionName, string $parentName): bool
    {
        $current = $parentName;
        $visited = [$regionName => true];

        while ($current !== null) {
            if (isset($visited[$current])) {
                return true;
            }

            $visited[$current] = true;
            $region = $this->getRegion($current);

            if ($region === null) {
                break;
            }

            $current = $region->parent;
        }

        return false;
    }

    public function delete(string $regionName, bool $orphanChildren = true): bool
    {
        if (! $this->exists($regionName)) {
            return false;
        }

        $region   = $this->regions[$regionName];
        $children = $this->getChildren($regionName);

        foreach ($children as $child) {
            if ($orphanChildren) {
                $child->setParent(null);
            } else {
                $child->setParent($region->parent);
            }
            $this->repository->save($child);
        }

        unset($this->regions[$regionName]);

        return $this->repository->delete($regionName);
    }
    public function saveRegion(Region $region): bool
    {
        return $this->repository->save($region);
    }

    public function redefine(string $name, Position $pos1, Position $pos2): bool
    {
        if (! $this->exists($name)) {
            return false;
        }

        $existingRegion = $this->regions[$name];

        if ($pos1->getWorld()->getFolderName() !== $pos2->getWorld()->getFolderName()) {
            return false;
        }

        $newRegion = new Region(
            name: $name,
            pos1: $pos1,
            pos2: $pos2,
            extended: $existingRegion->extended,
            global: $existingRegion->global,
            flags: $existingRegion->flags,
            parent: $existingRegion->parent,
        );

        $this->regions[$name] = $newRegion;

        if (! $this->repository->save($newRegion)) {
            $this->regions[$name] = $existingRegion;
            return false;
        }

        return true;
    }

}