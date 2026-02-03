<?php

namespace fenomeno\NepheliaWorldGuard\Flags;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use pocketmine\event\Event;
use pocketmine\player\Player;

interface FlagHandlerInterface
{

    /**
     * @return Flags[]
     */
    public static function getHandledFlags(): array;

    public function handle(Event $event, Region $region, ?Player $player = null): FlagResult;

    public static function getPriority(): int;

}