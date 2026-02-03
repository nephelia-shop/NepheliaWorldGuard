<?php

namespace fenomeno\NepheliaWorldGuard\Command\Arguments;

use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\args\StringEnumArgument;
use pocketmine\command\CommandSender;

class FlagValueArgument extends StringEnumArgument
{

    protected static array $VALUES = [
        'true'  => 'true',
        'false' => 'false',
        'none'  => 'none',
    ];

    public function parse(string $argument, CommandSender $sender): string
    {
        return strtolower($argument);
    }

    public function getTypeName(): string
    {
        return "flagValue";
    }
}