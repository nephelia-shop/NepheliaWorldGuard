<?php

namespace fenomeno\NepheliaWorldGuard\Command\SubCommands;

use fenomeno\NepheliaWorldGuard\Constants\Messages\ExtraTags;
use fenomeno\NepheliaWorldGuard\Constants\Messages\MessagesIds;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\args\RawStringArgument;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\BaseSubCommand;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\exception\ArgumentOrderException;
use fenomeno\NepheliaWorldGuard\Main;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\sound\PopSound;

final class DeleteSubCommand extends BaseSubCommand
{

    private static array $confirmations = [];

    private const REGION_ARGUMENT = "region";

    public function __construct(private readonly Main $main)
    {
        parent::__construct("delete", "Supprimer définitivement une region /nwg delete <region>");
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument(self::REGION_ARGUMENT, false));

        $this->setPermission("nepheliaworldguard.use.delete");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $regionName = (string) $args[self::REGION_ARGUMENT];

        if (! $this->main->getRegionsManager()->exists($regionName)) {
            MessagesUtils::sendTo($sender, MessagesIds::REGION_DELETE_NOT_FOUND, [ExtraTags::REGION => $regionName]);
            return;
        }

        if(! isset(self::$confirmations[$sender->getName()]) || self::$confirmations[$sender->getName()] !== $regionName){
            self::$confirmations[$sender->getName()] = $regionName;
            MessagesUtils::sendTo($sender, MessagesIds::REGION_DELETE_CONFIRM, [ExtraTags::REGION => $regionName]);
            return;
        }

        if($this->main->getRegionsManager()->delete($regionName)){
            MessagesUtils::sendTo($sender, MessagesIds::REGION_DELETE_SUCCESS, [ExtraTags::REGION => $regionName]);

            if($sender instanceof Player){
                $sender->broadcastSound(new PopSound());
            }
        } else {
            MessagesUtils::sendTo($sender, MessagesIds::REGION_DELETE_NOT_FOUND, [ExtraTags::REGION => $regionName]);
        }
    }
}