<?php

namespace fenomeno\NepheliaWorldGuard\Flags;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Main;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use pocketmine\player\Player;

abstract class AbstractFlagHandler implements FlagHandlerInterface
{

    public function __construct(
        private readonly Main $main
    ){}

    public static function getPriority(): int
    {
        return 0;
    }

    protected function canBypass(Player $player, Flags $flag, Region $region): bool
    {
        if($player->hasPermission(Main::BYPASS_PERMISSION)){
            return true;
        }

        $flagPermission = $flag->getPermission();
        if($player->hasPermission($flagPermission)){
            return true;
        }

        $regionFlagPermission = $flag->getPermissionByRegion($region);
        if($player->hasPermission($regionFlagPermission)){
            return true;
        }

        return false;
    }

    protected function getFlagValue(Region $region, Flags $flag): mixed
    {
        return $region->getFlag($flag);
    }

    protected function isFlagAllowed(Region $region, Flags $flag): bool
    {
        $value = $this->getFlagValue($region, $flag);

        return $value === true || $value === "true";
    }

    protected function isFlagDenied(Region $region, Flags $flags): bool
    {
        $value = $this->getFlagValue($region, $flags);

        return $value === false || $value === "false";
    }

    protected function shouldNotify(Region $region): bool
    {
        return $this->isFlagAllowed($region, Flags::DenyMsg);
    }

    protected function deny(Flags $flag, Region $region, ?string $customMessage = null): FlagResult
    {
        if(! $this->shouldNotify($region)){
            return FlagResult::denySilent($flag);
        }

        return FlagResult::deny($flag, $customMessage ?? $flag->getDenyMessage());
    }

}