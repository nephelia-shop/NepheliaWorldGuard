<?php

namespace fenomeno\NepheliaWorldGuard\Flags\Types;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Flags\AbstractFlagHandler;
use fenomeno\NepheliaWorldGuard\Flags\FlagResult;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Event;
use pocketmine\player\Player;

class BlockHandler extends AbstractFlagHandler
{

    public static function getHandledFlags(): array
    {
        return [
            Flags::BlockPlace,
            Flags::BlockBreak,
        ];
    }

    public function handle(Event $event, Region $region, ?Player $player = null): FlagResult
    {
        if($event instanceof BlockPlaceEvent){
            return $this->handleBlockPlace($event, $region);
        }

        if($event instanceof BlockBreakEvent){
            return $this->handleBlockBreak($event, $region);
        }

        return FlagResult::allow();
    }

    public static function getPriority(): int
    {
        return 1;
    }

    private function handleBlockPlace(BlockPlaceEvent $event, Region $region): FlagResult
    {
        $player = $event->getPlayer();

        if($this->canBypass($player, Flags::BlockPlace, $region)){
            return FlagResult::allow();
        }

        if($this->isFlagDenied($region, Flags::BlockPlace)){
            return $this->deny(Flags::BlockPlace, $region);
        }

        return FlagResult::allow();
    }

    private function handleBlockBreak(BlockBreakEvent $event, Region $region): FlagResult
    {
        $player = $event->getPlayer();

        if($this->canBypass($player, Flags::BlockBreak, $region)){
            return FlagResult::allow();
        }

        if($this->isFlagDenied($region, Flags::BlockBreak)){
            return $this->deny(Flags::BlockBreak, $region);
        }

        return FlagResult::allow();
    }

}