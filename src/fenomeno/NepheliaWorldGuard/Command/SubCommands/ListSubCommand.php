<?php

namespace fenomeno\NepheliaWorldGuard\Command\SubCommands;

use fenomeno\NepheliaWorldGuard\Constants\Messages\ExtraTags;
use fenomeno\NepheliaWorldGuard\Constants\Messages\MessagesIds;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\BaseSubCommand;
use fenomeno\NepheliaWorldGuard\Main;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;
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
        MessagesUtils::sendTo($sender, MessagesIds::REGION_LIST_HEADER, [
            ExtraTags::COUNT => count($this->main->getRegionsManager()->getAllRegions())
        ]);

        if(empty($this->main->getRegionsManager()->getAllRegions())) {
            MessagesUtils::sendTo($sender, MessagesIds::REGION_LIST_EMPTY);
            return;
        }

        foreach($this->main->getRegionsManager()->getAllRegions() as $region) {
            MessagesUtils::sendTo($sender, MessagesIds::REGION_LIST_ENTRY, [
                ExtraTags::REGION => $region->name,
                ExtraTags::WORLD  => $region->pos1->world->getFolderName(),
            ]);
        }

        MessagesUtils::sendTo($sender, MessagesIds::REGION_LIST_FOOTER);
    }
}