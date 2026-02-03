<?php

namespace fenomeno\NepheliaWorldGuard\Command\Arguments;

use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\args\StringEnumArgument;
use pocketmine\command\CommandSender;

class FlagsActionArgument extends StringEnumArgument
{

    public const ACTION_GET   = 'get';
    public const ACTION_SET   = 'set';
    public const ACTION_RESET = 'reset';
    public const ACTION_LIST  = 'list';

    protected static array $VALUES = [
        self::ACTION_GET   => self::ACTION_GET,
        self::ACTION_SET   => self::ACTION_SET,
        self::ACTION_RESET => self::ACTION_RESET,
        self::ACTION_LIST  => self::ACTION_LIST,
    ];

    public function __construct(string $name, bool $optional = false)
    {
        parent::__construct($name, $optional);
    }

    public function parse(string $argument, CommandSender $sender): string
    {
        return strtolower($argument);
    }

    public function getTypeName(): string
    {
        return "FlagsAction";
    }
}