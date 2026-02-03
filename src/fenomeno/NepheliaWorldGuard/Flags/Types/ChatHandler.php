<?php

namespace fenomeno\NepheliaWorldGuard\Flags\Types;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Flags\AbstractFlagHandler;
use fenomeno\NepheliaWorldGuard\Flags\FlagResult;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;

class ChatHandler extends AbstractFlagHandler
{

    public static function getHandledFlags(): array
    {
        return [
            Flags::SendChat,
            Flags::ReceiveChat,
            Flags::BlockedCmds,
            Flags::AllowedCmds
        ];
    }

    public function handle(Event $event, Region $region, ?Player $player = null): FlagResult
    {
        if($event instanceof PlayerChatEvent){
            return $this->handleChat($event, $region);
        }

        if($event instanceof CommandEvent){
            return $this->handleCommand($event, $region);
        }

        return FlagResult::allow();
    }

    public static function getPriority(): int
    {
        return 4;
    }

    private function handleChat(PlayerChatEvent $event, Region $region): FlagResult
    {
        $player = $event->getPlayer();

        if($this->isFlagDenied($region, Flags::ReceiveChat)){
            if(! $this->canBypass($player, Flags::ReceiveChat, $region)){
                return FlagResult::deny(Flags::ReceiveChat);
            }
        }

        if($this->isFlagDenied($region, Flags::SendChat)){
            if(! $this->canBypass($player, Flags::SendChat, $region)){
                $event->cancel();
                return FlagResult::deny(Flags::SendChat);
            }
        }

        return FlagResult::allow();
    }

    private function handleCommand(CommandEvent $event, Region $region): FlagResult
    {
        $sender = $event->getSender();
        if(! $sender instanceof Player){
            return FlagResult::allow();
        }

        if($this->canBypass($sender, Flags::BlockedCmds, $region)){
            return FlagResult::allow();
        }

        $command     = strtolower($event->getCommand());
        $allowedCmds = $this->getFlagValue($region, Flags::AllowedCmds);
        if(is_array($allowedCmds) && count($allowedCmds) > 0){
            if(! isset($allowedCmds[$command]) && ! in_array($command, $allowedCmds)){
                return FlagResult::deny(Flags::AllowedCmds);
            }

            return FlagResult::allow();
        }

        $blockedCmds = $this->getFlagValue($region, Flags::BlockedCmds);
        if(is_array($blockedCmds) && count($blockedCmds) > 0) {
            if (isset($blockedCmds[$command]) || in_array($command, $blockedCmds)) {
                return FlagResult::deny(Flags::BlockedCmds);
            }

            return FlagResult::allow();
        }

        return FlagResult::allow();
    }
}