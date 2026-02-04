<?php

namespace fenomeno\NepheliaWorldGuard\Flags\Types;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Flags\AbstractFlagHandler;
use fenomeno\NepheliaWorldGuard\Flags\FlagResult;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\Server;

class MovementHandler extends AbstractFlagHandler
{

    public static function getHandledFlags(): array
    {
        return [
            Flags::AllowedEnter,
            Flags::AllowedLeave,
            Flags::NotifyEnter,
            Flags::NotifyLeave,
            Flags::ConsoleCmdOnEnter,
            Flags::ConsoleCmdOnLeave
        ];
    }

    public function handle(Event $event, Region $region, ?Player $player = null): FlagResult
    {
        return FlagResult::allow();
    }

    public function onRegionEnter(Player $player, Region $region): FlagResult
    {
        if (! $this->isFlagAllowed($region, Flags::AllowedEnter)) {
            if (! $this->canBypass($player, Flags::AllowedEnter, $region)) {
                return $this->deny(Flags::AllowedEnter, $region);
            }
        }

        $consoleCmd = $this->getFlagValue($region, Flags::ConsoleCmdOnEnter);
        if (is_string($consoleCmd) && $consoleCmd !== "none" && $consoleCmd !== "" && $consoleCmd !== "null") {
            $this->executeConsoleCommand($consoleCmd, $player);
        }

        $notifyEnter = $this->getFlagValue($region, Flags::NotifyEnter);
        if(! empty(trim($notifyEnter)) && ($notifyEnter !== "none" && $notifyEnter !== "" && $notifyEnter !== "false" && $notifyEnter !== "null")) {
            $player->sendMessage($notifyEnter);
        }

        return FlagResult::allow();
    }

    public function onRegionLeave(Player $player, Region $region): FlagResult
    {
        if (! $this->isFlagAllowed($region, Flags::AllowedLeave)) {
            if (! $this->canBypass($player, Flags::AllowedLeave, $region)) {
                return $this->deny(Flags::AllowedLeave, $region);
            }
        }

        $consoleCmd = $this->getFlagValue($region, Flags::ConsoleCmdOnLeave);
        if (is_string($consoleCmd) && $consoleCmd !== "none" && $consoleCmd !== "" && $consoleCmd !== "null") {
            $this->executeConsoleCommand($consoleCmd, $player);
        }

        $notifyLeave = $this->getFlagValue($region, Flags::NotifyLeave);
        if(! empty(trim($notifyLeave)) && ($notifyLeave !== "none" && $notifyLeave !== "" && $notifyLeave !== "false" && $notifyLeave !== "null")) {
            $player->sendMessage($notifyLeave);
        }

        return FlagResult::allow();
    }

    public static function getPriority(): int
    {
        return 4;
    }

    private function executeConsoleCommand(string $consoleCmd, Player $player): void
    {
        $command = str_replace("{PLAYER}", $player->getName(), $consoleCmd);
        $player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $command);
    }

}