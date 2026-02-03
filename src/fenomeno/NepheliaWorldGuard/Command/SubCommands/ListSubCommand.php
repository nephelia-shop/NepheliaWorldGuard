<?php

namespace fenomeno\NepheliaWorldGuard\Command\SubCommands;

use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\BaseSubCommand;
use fenomeno\NepheliaWorldGuard\Main;
use pocketmine\command\CommandSender;

final class ListSubCommand extends BaseSubCommand
{

    public function __construct(private readonly Main $main)
    {
        parent::__construct("list", "Affiche la liste de toutes les régions existantes");
    }

    protected function prepare(): void
    {
        $this->setPermission("nepheliaworldguard.use.list");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {

    }
}