<?php

namespace fenomeno\NepheliaWorldGuard\Flags\Types;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Flags\AbstractFlagHandler;
use fenomeno\NepheliaWorldGuard\Flags\FlagResult;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\player\Player;

class DamageHandler extends AbstractFlagHandler
{

    public static function getHandledFlags(): array
    {
        return [
            Flags::PVP,
            Flags::AllowDamageMonsters,
            Flags::AllowDamageAnimals,
            Flags::FallDmg,
            Flags::Invincible
        ];
    }

    public function handle(Event $event, Region $region, ?Player $player = null): FlagResult
    {
        if(! $event instanceof EntityDamageEvent){
            return FlagResult::allow();
        }

        $entity = $event->getEntity();
        if($entity instanceof Player){
            if ($this->isFlagAllowed($region, Flags::Invincible)){
                return FlagResult::deny(Flags::Invincible);
            }

            if($event->getCause() === EntityDamageEvent::CAUSE_FALL){
                if($this->isFlagDenied($region, Flags::FallDmg)){
                    return FlagResult::deny(Flags::FallDmg);
                }
            }
        }

        if($event instanceof EntityDamageByEntityEvent){
            return $this->handleEntityDamageByEntity($event, $region);
        }

        return FlagResult::allow();
    }

    private function handleEntityDamageByEntity(EntityDamageByEntityEvent $event, Region $region): FlagResult
    {
        $damager = $event->getDamager();
        $victim  = $event->getEntity();

        if($damager instanceof Player && $victim instanceof Player){
            if($this->isFlagDenied($region, Flags::PVP)){
                if(! $this->canBypass($damager, Flags::PVP, $region) && ! $this->canBypass($victim, Flags::PVP, $region)){
                    return FlagResult::deny(Flags::PVP);
                }
            }
        }

        if($damager instanceof Player && $this->isAnimal($victim)){
            if($this->isFlagDenied($region, Flags::AllowDamageAnimals)){
                if(! $this->canBypass($damager, Flags::AllowDamageAnimals, $region)){
                    return FlagResult::deny(Flags::AllowDamageAnimals);
                }
            }
        }

        if($damager instanceof Player && $this->isMonster($victim)){
            if($this->isFlagDenied($region, Flags::AllowDamageMonsters)){
                if(! $this->canBypass($damager, Flags::AllowDamageMonsters, $region)){
                    return FlagResult::deny(Flags::AllowDamageMonsters);
                }
            }
        }

        return FlagResult::allow();
    }

    private function isAnimal(Entity $entity): bool
    {
        return
            str_contains('animal', strtolower(get_class($entity))) ||
            str_contains('passive', strtolower(get_class($entity))) ||
            str_contains('chicken', strtolower(get_class($entity))) ||
            str_contains('cow', strtolower(get_class($entity))) ||
            str_contains('pig', strtolower(get_class($entity))) ||
            str_contains('sheep', strtolower(get_class($entity))) ||
            str_contains('rabbit', strtolower(get_class($entity))) ||
            str_contains('horse', strtolower(get_class($entity))) ||
            str_contains('llama', strtolower(get_class($entity))) ||
            str_contains('donkey', strtolower(get_class($entity))) ||
            str_contains('mule', strtolower(get_class($entity))) ||
            str_contains('parrot', strtolower(get_class($entity))) ||
            str_contains('turtle', strtolower(get_class($entity)));
    }

    private function isMonster(Entity $entity): bool
    {
        return
            str_contains('monster', strtolower(get_class($entity))) ||
            str_contains('hostile', strtolower(get_class($entity))) ||
            str_contains('zombie', strtolower(get_class($entity))) ||
            str_contains('skeleton', strtolower(get_class($entity))) ||
            str_contains('creeper', strtolower(get_class($entity))) ||
            str_contains('spider', strtolower(get_class($entity))) ||
            str_contains('enderman', strtolower(get_class($entity))) ||
            str_contains('witch', strtolower(get_class($entity))) ||
            str_contains('slime', strtolower(get_class($entity))) ||
            str_contains('ghast', strtolower(get_class($entity))) ||
            str_contains('blaze', strtolower(get_class($entity))) ||
            str_contains('magma', strtolower(get_class($entity))) ||
            str_contains('silverfish', strtolower(get_class($entity))) ||
            str_contains('guardian', strtolower(get_class($entity))) ||
            str_contains('phantom', strtolower(get_class($entity)));
    }

    public static function getPriority(): int
    {
        return 2;
    }

}