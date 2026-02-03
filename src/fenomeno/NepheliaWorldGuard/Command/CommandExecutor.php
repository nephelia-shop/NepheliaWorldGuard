<?php

namespace fenomeno\NepheliaWorldGuard\Command;

use fenomeno\NepheliaWorldGuard\Command\SubCommands\CancelSubCommand;
use fenomeno\NepheliaWorldGuard\Command\SubCommands\CreateSubCommand;
use fenomeno\NepheliaWorldGuard\Command\SubCommands\DeleteSubCommand;
use fenomeno\NepheliaWorldGuard\Command\SubCommands\FlagsSubCommand;
use fenomeno\NepheliaWorldGuard\Command\SubCommands\InfoSubCommand;
use fenomeno\NepheliaWorldGuard\Command\SubCommands\ListSubCommand;
use fenomeno\NepheliaWorldGuard\Command\SubCommands\ParentSubCommand;
use fenomeno\NepheliaWorldGuard\Command\SubCommands\RedefineSubCommand;
use fenomeno\NepheliaWorldGuard\Constants\Messages\MessagesIds;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\BaseCommand;
use fenomeno\NepheliaWorldGuard\Main;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;
use pocketmine\command\CommandSender;

final class CommandExecutor extends BaseCommand
{

    public function __construct(private readonly Main $main)
    {
        parent::__construct($this->main, "nepheliaworldguard", "Nephelia WorldGuard Commande", ["worldguard", "nwg", "wg", "region"]);
    }

    protected function prepare(): void
    {
        $this->setPermission("nepheliaworldguard.use");
        $this->registerSubCommand(new CreateSubCommand($this->main));
        $this->registerSubCommand(new DeleteSubCommand($this->main));
        $this->registerSubCommand(new ListSubCommand($this->main));
        $this->registerSubCommand(new InfoSubCommand($this->main));
        $this->registerSubCommand(new RedefineSubCommand($this->main));
        $this->registerSubCommand(new FlagsSubCommand($this->main));
        $this->registerSubCommand(new CancelSubCommand($this->main));
        $this->registerSubCommand(new ParentSubCommand($this->main));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        MessagesUtils::sendTo($sender, MessagesIds::HELP_HEADER);
        MessagesUtils::sendTo($sender, MessagesIds::HELP_COMMANDS);
        MessagesUtils::sendTo($sender, MessagesIds::HELP_FOOTER);

        MessagesUtils::sendTo($sender, MessagesIds::HELP_FLAGS_HEADER);
        MessagesUtils::sendTo($sender, MessagesIds::HELP_FLAGS_LIST);
        MessagesUtils::sendTo($sender, MessagesIds::HELP_FOOTER);
    }
}