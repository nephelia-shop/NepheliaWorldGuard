<?php

namespace fenomeno\NepheliaWorldGuard\Command\SubCommands;

use fenomeno\NepheliaWorldGuard\Constants\Messages\ExtraTags;
use fenomeno\NepheliaWorldGuard\Constants\Messages\MessagesIds;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\args\RawStringArgument;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\BaseSubCommand;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\constraint\InGameRequiredConstraint;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\exception\ArgumentOrderException;
use fenomeno\NepheliaWorldGuard\Main;
use fenomeno\NepheliaWorldGuard\Session\Session;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\sound\PopSound;

class RedefineSubCommand extends BaseSubCommand
{

    private const REGION_ARGUMENT = "region";

    public function __construct(private readonly Main $main)
    {
        parent::__construct("redefine", "Redéfinir les limites d'une région existante /nwg redefine <region>");
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument(self::REGION_ARGUMENT, false));

        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->setPermission("nepheliaworldguard.use.redefine");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        assert($sender instanceof Player);
        $session = Session::get($sender);

        if ($session->isCreating()) {
            MessagesUtils::sendTo($sender, MessagesIds::ALREADY_CREATING_REGION);
            return;
        }

        if ($session->isRedefining()) {
            MessagesUtils::sendTo($sender, MessagesIds::REGION_REDEFINE_ALREADY_REDEFINING);
            return;
        }

        $regionName = (string) ($args[self::REGION_ARGUMENT] ?? "");

        if (! $this->main->getRegionsManager()->exists($regionName)) {
            MessagesUtils::sendTo($sender, MessagesIds::REGION_NOT_FOUND, [
                ExtraTags::REGION => $regionName
            ]);
            return;
        }

        $region = $this->main->getRegionsManager()->getRegion($regionName);

        if ($region->global) {
            MessagesUtils::sendTo($sender, MessagesIds::REGION_REDEFINE_GLOBAL_NOT_ALLOWED, [
                ExtraTags::REGION => $regionName
            ]);
            return;
        }

        $session->startRedefining($regionName, [
            'extended' => $region->extended,
            'original_pos1' => $region->pos1,
            'original_pos2' => $region->pos2,
        ]);

        MessagesUtils::sendTo($sender, $region->extended
            ? MessagesIds::REGION_REDEFINE_START_EXTENDED
            : MessagesIds::REGION_REDEFINE_START, [
            ExtraTags::REGION => $regionName
        ]);

        $sender->broadcastSound(new PopSound());
    }
}