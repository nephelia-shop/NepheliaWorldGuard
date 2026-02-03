<?php

namespace fenomeno\NepheliaWorldGuard\Flags\Types;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Flags\AbstractFlagHandler;
use fenomeno\NepheliaWorldGuard\Flags\FlagResult;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use pocketmine\block\Bed;
use pocketmine\block\Block;
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\inventory\AnvilInventory;
use pocketmine\block\inventory\BarrelInventory;
use pocketmine\block\inventory\BrewingStandInventory;
use pocketmine\block\inventory\ChestInventory;
use pocketmine\block\inventory\EnchantInventory;
use pocketmine\block\inventory\EnderChestInventory;
use pocketmine\block\inventory\FurnaceInventory;
use pocketmine\block\inventory\ShulkerBoxInventory;
use pocketmine\block\ItemFrame;
use pocketmine\block\PressurePlate;
use pocketmine\block\Trapdoor;
use pocketmine\event\Event;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;

class InteractionHandler extends AbstractFlagHandler
{

    public static function getHandledFlags(): array
    {
        return [
            Flags::InteractFrame,
            Flags::Use,
            Flags::Sleep
        ];
    }

    public function handle(Event $event, Region $region, ?Player $player = null): FlagResult
    {
        if($event instanceof PlayerInteractEvent){
            return $this->handlePlayerInteract($event, $region);
        }

        if($event instanceof InventoryOpenEvent){
            return $this->handleInventoryOpen($event, $region);
        }

        return FlagResult::allow();
    }

    public static function getPriority(): int
    {
        return 3;
    }

    private function handlePlayerInteract(PlayerInteractEvent $event, Region $region): FlagResult
    {
        $player = $event->getPlayer();
        $block  = $event->getBlock();

        if($block instanceof ItemFrame){
            if($this->isFlagDenied($region, Flags::InteractFrame)){
                if(! $this->canBypass($player, Flags::InteractFrame, $region)){
                    return FlagResult::deny(Flags::InteractFrame);
                }
            }
        }

        if($block instanceof Bed){
            if($this->isFlagDenied($region, Flags::Sleep)){
                if(! $this->canBypass($player, Flags::Sleep, $region)){
                    return FlagResult::deny(Flags::Sleep);
                }
            }
        }

        if($this->isInteractableBlock($block)){
            if($this->isFlagDenied($region, Flags::Use)){
                if(! $this->canBypass($player, Flags::Use, $region)){
                    return FlagResult::deny(Flags::Use);
                }
            }
        }

        return FlagResult::allow();
    }

    private function isInteractableBlock(Block $block): bool
    {
        return
            $block instanceof Door ||
            $block instanceof Trapdoor ||
            $block instanceof FenceGate ||
            $block instanceof PressurePlate;
    }

    private function handleInventoryOpen(InventoryOpenEvent $event, Region $region): FlagResult
    {
        $player    = $event->getPlayer();
        $inventory = $event->getInventory();

        if($this->isFlagDenied($region, Flags::Use)){
            if(! $this->canBypass($player, Flags::Use, $region)){
                $blocked = match(true){
                    $inventory instanceof ChestInventory,
                    $inventory instanceof EnderChestInventory,
                    $inventory instanceof BarrelInventory,
                    $inventory instanceof ShulkerBoxInventory,
                    $inventory instanceof FurnaceInventory,
                    $inventory instanceof AnvilInventory,
                    $inventory instanceof EnchantInventory,
                    $inventory instanceof BrewingStandInventory => true,
                    default                                     => false,
                };

                if ($blocked){
                    return $this->deny(Flags::Use, $region);
                }
            }
        }

        return FlagResult::allow();
    }
}