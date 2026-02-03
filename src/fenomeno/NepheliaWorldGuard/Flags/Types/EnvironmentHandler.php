<?php

namespace fenomeno\NepheliaWorldGuard\Flags\Types;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Flags\AbstractFlagHandler;
use fenomeno\NepheliaWorldGuard\Flags\FlagResult;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Event;
use pocketmine\player\Player;

class EnvironmentHandler extends AbstractFlagHandler
{

    public static function getHandledFlags(): array
    {
        return [
            Flags::Flow,
            Flags::Explosion,
            Flags::AllowLeavesDecay,
            Flags::AllowPlantGrowth,
            Flags::AllowSpreading,
            Flags::AllowBlockBurn
        ];
    }

    public function handle(Event $event, Region $region, ?Player $player = null): FlagResult
    {
        return match(true){
            $event instanceof EntityExplodeEvent => $this->handleExplosionEvent($region),
            $event instanceof LeavesDecayEvent   => $this->handleLeavesDecayEvent($region),
            $event instanceof BlockGrowEvent     => $this->handleGrowEvent($region),
            $event instanceof BlockSpreadEvent   => $this->handleBlockSpreadEvent($region),
            $event instanceof BlockBurnEvent     => $this->handleBlockBurnEvent($region),
            $event instanceof BlockFormEvent     => $this->handleBlockFormEvent($region),
            default => FlagResult::allow(),
        };
    }

    public static function getPriority(): int
    {
        return 5;
    }

    private function handleExplosionEvent(Region $region): FlagResult
    {
        if ($this->isFlagDenied($region, Flags::Explosion)) {
            return FlagResult::deny(Flags::Explosion);
        }

        return FlagResult::allow();
    }

    private function handleLeavesDecayEvent(Region $region): FlagResult
    {
        if ($this->isFlagDenied($region, Flags::AllowLeavesDecay)) {
            return FlagResult::deny(Flags::AllowLeavesDecay);
        }

        return FlagResult::allow();
    }

    private function handleGrowEvent(Region $region): FlagResult
    {
        if ($this->isFlagDenied($region, Flags::AllowPlantGrowth)) {
            return FlagResult::deny(Flags::AllowPlantGrowth);
        }

        return FlagResult::allow();
    }

    private function handleBlockSpreadEvent(Region $region): FlagResult
    {
        if ($this->isFlagDenied($region, Flags::AllowSpreading)) {
            return FlagResult::deny(Flags::AllowSpreading);
        }

        return FlagResult::allow();
    }

    private function handleBlockBurnEvent(Region $region): FlagResult
    {
        if ($this->isFlagDenied($region, Flags::AllowBlockBurn)) {
            return FlagResult::deny(Flags::AllowBlockBurn);
        }

        return FlagResult::allow();
    }

    private function handleBlockFormEvent(Region $region): FlagResult
    {
        if ($this->isFlagDenied($region, Flags::Flow)) {
            return FlagResult::deny(Flags::Flow);
        }

        return FlagResult::allow();
    }

}