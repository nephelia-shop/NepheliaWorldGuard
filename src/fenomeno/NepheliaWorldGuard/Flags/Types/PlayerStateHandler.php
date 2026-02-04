<?php

namespace fenomeno\NepheliaWorldGuard\Flags\Types;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Flags\AbstractFlagHandler;
use fenomeno\NepheliaWorldGuard\Flags\FlagResult;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;

class PlayerStateHandler extends AbstractFlagHandler
{

    public const FLY_ENABLE     = 1;
    public const FLY_DISABLE    = 2;
    public const FLY_SUPERVISED = 3;

    public static function getHandledFlags(): array
    {
        return [
            Flags::GameMode,
            Flags::FlyMode,
            Flags::Hunger,
            Flags::ExpDrops,
            Flags::Effects
        ];
    }

    public function handle(Event $event, Region $region, ?Player $player = null): FlagResult
    {
        return match(true){
            $event instanceof PlayerExhaustEvent          => $this->handleHungerEvent($event, $region),
            $event instanceof PlayerExperienceChangeEvent => $this->handleExpChangeEvent($event, $region),
            default                                       => FlagResult::allow(),
        };
    }

    public static function getPriority(): int
    {
        return 6;
    }

    private function handleHungerEvent(PlayerExhaustEvent $event, Region $region): FlagResult
    {
        if ($this->isFlagDenied($region, Flags::Hunger)) {
            $player = $event->getPlayer();
            if($player instanceof Player){
                if(! $this->canBypass($player, Flags::Hunger, $region)){
                    return FlagResult::deny(Flags::Hunger);
                }
            }

            return FlagResult::deny(Flags::Hunger);
        }

        return FlagResult::allow();
    }

    private function handleExpChangeEvent(PlayerExperienceChangeEvent $event, Region $region): FlagResult
    {
        $player = $event->getEntity();
        if($player instanceof Player && $this->canBypass($player, Flags::ExpDrops, $region)){
            return FlagResult::allow();
        }

        if ($this->isFlagAllowed($region, Flags::ExpDrops)) {
            return FlagResult::allow();
        }

        return FlagResult::deny(Flags::ExpDrops);
    }

    public function applyPlayerState(Player $player, Region $region): void
    {
        $this->applyGameMode($player, $region);
        $this->applyFlyMode($player, $region);
        $this->applyEffects($player, $region);
    }

    public function removePlayerState(Player $player, Region $region): void
    {
        $this->removeEffects($player, $region);

        $flyMode = $this->getFlagValue($region, Flags::FlyMode);
        if ($flyMode === self::FLY_SUPERVISED) {
            if ($player->getGamemode() !== GameMode::CREATIVE) {
                $player->setAllowFlight(false);
                $player->setFlying(false);
            }
        }
    }

    private function applyGameMode(Player $player, Region $region): void
    {
        if ($this->canBypass($player, Flags::GameMode, $region)) {
            return;
        }

        $gm = $this->getFlagValue($region, Flags::GameMode);

        if ($gm === "false" || $gm === false) {
            return;
        }

        $gamemode = match(strtolower((string)$gm)) {
            "0", "survival"  => GameMode::SURVIVAL,
            "1", "creative"  => GameMode::CREATIVE,
            "2", "adventure" => GameMode::ADVENTURE,
            "3", "spectator" => GameMode::SPECTATOR,
            default => null
        };

        if ($gamemode !== null) {
            $player->setGamemode($gamemode);
        }
    }

    private function applyFlyMode(Player $player, Region $region): void
    {
        if ($this->canBypass($player, Flags::FlyMode, $region)) {
            return;
        }

        if ($player->getGamemode() === GameMode::CREATIVE) {
            return;
        }

        $flyMode = (int)$this->getFlagValue($region, Flags::FlyMode);

        match($flyMode) {
            self::FLY_ENABLE, self::FLY_SUPERVISED => $player->setAllowFlight(true),
            self::FLY_DISABLE                      => $this->disableFlight($player),
            default                                => null
        };
    }

    private function disableFlight(Player $player): void
    {
        $player->setAllowFlight(false);
        $player->setFlying(false);
    }

    private function applyEffects(Player $player, Region $region): void
    {
        $effects = $this->getFlagValue($region, Flags::Effects);

        if (! is_array($effects) || empty($effects)) {
            return;
        }

        foreach ($effects as $effectData => $_) {
            if (is_string($effectData)) {
                $this->addEffectFromString($player, $effectData);
            }
        }
    }

    private function removeEffects(Player $player, Region $region): void
    {
        $effects = $this->getFlagValue($region, Flags::Effects);

        if (! is_array($effects) || empty($effects)) {
            return;
        }

        foreach ($effects as $effectData) {
            if (is_string($effectData)) {
                $parts      = explode(":", $effectData);
                $effectName = $parts[0];
                $effect     = StringToEffectParser::getInstance()->parse($effectName);
                if ($effect !== null) {
                    $player->getEffects()->remove($effect);
                }
            }
        }
    }

    private function addEffectFromString(Player $player, string $effectString): void
    {
        $parts      = explode(":", $effectString);
        $effectName = $parts[0];
        $duration   = isset($parts[1]) ? (int)$parts[1] * 20 : 999999;
        $amplifier  = isset($parts[2]) ? (int)$parts[2] : 0;

        $effect = StringToEffectParser::getInstance()->parse($effectName);
        if ($effect !== null) {
            $player->getEffects()->add(new EffectInstance($effect, $duration, $amplifier, false));
        }
    }

}