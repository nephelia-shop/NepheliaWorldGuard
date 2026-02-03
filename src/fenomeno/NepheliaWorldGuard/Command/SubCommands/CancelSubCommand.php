<?php

namespace fenomeno\NepheliaWorldGuard\Command\SubCommands;

use fenomeno\NepheliaWorldGuard\Constants\Messages\ExtraTags;
use fenomeno\NepheliaWorldGuard\Constants\Messages\MessagesIds;
use fenomeno\NepheliaWorldGuard\Exceptions\RegionCreationException;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\BaseSubCommand;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\constraint\InGameRequiredConstraint;
use fenomeno\NepheliaWorldGuard\Main;
use fenomeno\NepheliaWorldGuard\Session\Session;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\sound\EndermanTeleportSound;

final class CancelSubCommand extends BaseSubCommand
{
    public function __construct(private readonly Main $main)
    {
        parent::__construct("cancel", "Annuler la création/redéfinition en cours /nwg cancel");
    }

    protected function prepare(): void
    {
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->setPermission("nepheliaworldguard.use.cancel");
    }

    /**
     * @throws RegionCreationException
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        assert($sender instanceof Player);
        $session = Session::get($sender);

        if (! $session->isInEditMode()) {
            MessagesUtils::sendTo($sender, MessagesIds::CANCEL_NOTHING_TO_CANCEL);
            return;
        }

        $regionName = $session->getRegionName();
        $mode       = $session->getModeString();

        $session->cancel();

        $messageId = match ($mode) {
            'creating'   => MessagesIds::CANCEL_CREATION_CANCELLED,
            'redefining' => MessagesIds::REGION_REDEFINE_CANCELLED,
            default      => MessagesIds::CANCEL_OPERATION_CANCELLED,
        };

        MessagesUtils::sendTo($sender, $messageId, [
            ExtraTags::REGION => $regionName,
        ]);

        $sender->broadcastSound(new EndermanTeleportSound());
    }
}