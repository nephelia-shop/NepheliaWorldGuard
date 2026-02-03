<?php

namespace fenomeno\NepheliaWorldGuard\Flags\Types;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Flags\AbstractFlagHandler;
use fenomeno\NepheliaWorldGuard\Flags\FlagResult;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\player\Player;

class ItemHandler extends AbstractFlagHandler
{

    public static function getHandledFlags(): array
    {
        return [
            Flags::ItemByDeath,
            Flags::ItemDrop,
            Flags::Eat,
            Flags::Enderpearl,
            Flags::Bow,
            Flags::Potions
        ];
    }

    public function handle(Event $event, Region $region, ?Player $player = null): FlagResult
    {
        return match(true){
            $event instanceof PlayerDropItemEvent    => $this->handlePlayerDropItem($event, $region),
            $event instanceof PlayerDeathEvent       => $this->handlePlayerDeath($event, $region),
            $event instanceof PlayerItemConsumeEvent => $this->handlePlayerItemConsume($event, $region),
            $event instanceof ProjectileLaunchEvent  => $this->handleProjectileLaunch($event, $region),
            default                                  => FlagResult::allow(),
        };
    }

    public static function getPriority(): int
    {
        return 3;
    }

    private function handlePlayerDropItem(PlayerDropItemEvent $event, Region $region): FlagResult
    {
        $player = $event->getPlayer();
        if($this->isFlagDenied($region, Flags::ItemDrop)){
            if(! $this->canBypass($player, Flags::ItemDrop, $region)){
                return FlagResult::deny(Flags::ItemDrop);
            }
        }

        return FlagResult::allow();
    }

    private function handlePlayerDeath(PlayerDeathEvent $event, Region $region): FlagResult
    {
        if ($this->isFlagDenied($region, Flags::ItemByDeath)){
            $event->setDrops([]);
            return FlagResult::deny(Flags::ItemByDeath);
        }

        return FlagResult::allow();
    }

    private function handlePlayerItemConsume(PlayerItemConsumeEvent $event, Region $region): FlagResult
    {
        $player = $event->getPlayer();

        if($this->isFlagDenied($region, Flags::Eat)){
            if(! $this->canBypass($player, Flags::ItemDrop, $region)){
                return FlagResult::deny(Flags::Eat);
            }
        }

        return FlagResult::allow();
    }

    private function handleProjectileLaunch(ProjectileLaunchEvent $event, Region $region): FlagResult
    {
        $projectile = $event->getEntity();
        $owner = $projectile->getOwningEntity();

        if (! $owner instanceof Player) {
            return FlagResult::allow();
        }

        if($projectile instanceof EnderPearl){
            if($this->isFlagDenied($region, Flags::Enderpearl)){
                if(! $this->canBypass($owner, Flags::Enderpearl, $region)){
                    return FlagResult::deny(Flags::Enderpearl);
                }
            }
        }

        if($projectile instanceof Arrow){
            if($this->isFlagDenied($region, Flags::Bow)){
                if(! $this->canBypass($owner, Flags::Bow, $region)){
                    return FlagResult::deny(Flags::Bow);
                }
            }
        }

        if($projectile instanceof SplashPotion){
            if($this->isFlagDenied($region, Flags::Potions)){
                if(! $this->canBypass($owner, Flags::Potions, $region)){
                    return FlagResult::deny(Flags::Potions);
                }
            }
        }

        return FlagResult::allow();
    }
}