<?php

namespace fenomeno\NepheliaWorldGuard\Command\Arguments;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\args\StringEnumArgument;
use pocketmine\command\CommandSender;

class FlagArgument extends StringEnumArgument
{

    public function __construct(string $name, bool $optional = false)
    {
        foreach (Flags::toArray() as $value => $flag) {
            static::$VALUES[$value] = $value;
        }

        parent::__construct($name, $optional);
    }

    public function parse(string $argument, CommandSender $sender): ?Flags
    {
        return Flags::tryFrom($argument);
    }

    public function getTypeName(): string
    {
        return 'flag';
    }
}