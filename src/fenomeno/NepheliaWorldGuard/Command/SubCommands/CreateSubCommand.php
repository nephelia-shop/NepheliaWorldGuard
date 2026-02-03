<?php

namespace fenomeno\NepheliaWorldGuard\Command\SubCommands;

use fenomeno\NepheliaWorldGuard\Constants\Messages\ExtraTags;
use fenomeno\NepheliaWorldGuard\Constants\Messages\MessagesIds;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\args\BooleanArgument;
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

final class CreateSubCommand extends BaseSubCommand
{

    private const NAME_ARGUMENT     = 'name';
    private const EXTENDED_ARGUMENT = 'extended';
    private const GLOBAL_ARGUMENT   = 'global';

    public function __construct(private readonly Main $main)
    {
        parent::__construct("create", "Créer une nouvelle region /nwg create <name> <extended:true|false> <global:true|false>");
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument(self::NAME_ARGUMENT, false));
        $this->registerArgument(1, new BooleanArgument(self::EXTENDED_ARGUMENT, true));
        $this->registerArgument(2, new BooleanArgument(self::GLOBAL_ARGUMENT, true));

        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->setPermission("nepheliaworldguard.use.create");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        assert($sender instanceof Player);
        $session = Session::get($sender);
        if($session->isCreating()){
            MessagesUtils::sendTo($sender, MessagesIds::ALREADY_CREATING_REGION);
            return;
        }

        $name     = (string) ($args[self::NAME_ARGUMENT] ?? "");
        if($this->main->getRegionsManager()->exists($name)){
            MessagesUtils::sendTo($sender, MessagesIds::REGION_ALREADY_EXISTS, [ExtraTags::REGION => $name]);
            return;
        }

        //doit contenir que des lettres et des chiffres et des underscores
        if(! preg_match('/^[a-zA-Z0-9_]+$/', $name)){
            MessagesUtils::sendTo($sender, MessagesIds::REGION_INVALID_NAME, [ExtraTags::REGION => $name]);
            return;
        }

        $extended = (bool) ($args[self::EXTENDED_ARGUMENT] ?? false);
        $global   = (bool) ($args[self::GLOBAL_ARGUMENT] ?? false);

        $session->startCreating($name, ['extended' => $extended, 'global' => $global]);
        MessagesUtils::sendTo($sender, $extended ? MessagesIds::START_CREATING_EXTENDED_REGION : MessagesIds::START_CREATING_REGION, [ExtraTags::REGION => $name]);
        $sender->broadcastSound(new PopSound());
    }
}