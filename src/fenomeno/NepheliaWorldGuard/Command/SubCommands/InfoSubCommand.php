<?php

namespace fenomeno\NepheliaWorldGuard\Command\SubCommands;

use fenomeno\NepheliaWorldGuard\Constants\Messages\ExtraTags;
use fenomeno\NepheliaWorldGuard\Constants\Messages\MessagesIds;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\args\RawStringArgument;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\BaseSubCommand;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\constraint\InGameRequiredConstraint;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\exception\ArgumentOrderException;
use fenomeno\NepheliaWorldGuard\Main;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class InfoSubCommand extends BaseSubCommand
{

    private const REGION_ARGUMENT = "region";

    public function __construct(private readonly Main $main)
    {
        parent::__construct("info", "Affiche les informations de la region ou vous vous trouvez ou bien d'une region spécifiée /nwg info <region>");
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument(self::REGION_ARGUMENT, true));

        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->setPermission("nepheliaworldguard.use.info");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        assert($sender instanceof Player);
        if (! isset($args[self::REGION_ARGUMENT])){
            $region = $this->main->getRegionsManager()->getRegionAt($sender->getPosition());
            if (! $region) {
                MessagesUtils::sendTo($sender, MessagesIds::REGION_INFO_NOT_IN_REGION);
                return;
            }
        } else {
            $regionName = (string) $args[self::REGION_ARGUMENT];
            $region     = $this->main->getRegionsManager()->getRegion($regionName);
            if (! $region) {
                MessagesUtils::sendTo($sender, MessagesIds::REGION_NOT_FOUND, [
                    ExtraTags::REGION => $regionName
                ]);
                return;
            }
        }

        MessagesUtils::sendTo($sender, MessagesIds::REGION_INFO_HEADER, [
            ExtraTags::REGION => $region->name
        ]);
        MessagesUtils::sendTo($sender, MessagesIds::REGION_INFO_WORLD, [
            ExtraTags::WORLD => $region->global ? "Global" : $region->pos1->getWorld()->getFolderName()
        ]);
        MessagesUtils::sendTo($sender, MessagesIds::REGION_INFO_PRIORITY, [
            ExtraTags::PRIORITY => $region->getPriority()
        ]);
        MessagesUtils::sendTo($sender, MessagesIds::REGION_INFO_POSITIONS, [
            ExtraTags::X1 => $region->pos1->getX(),
            ExtraTags::Y1 => $region->pos1->getY(),
            ExtraTags::Z1 => $region->pos1->getZ(),

            ExtraTags::X2 => $region->pos2->getX(),
            ExtraTags::Y2 => $region->pos2->getY(),
            ExtraTags::Z2 => $region->pos2->getZ(),
        ]);
    }
}