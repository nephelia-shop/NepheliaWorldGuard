<?php
namespace fenomeno\NepheliaWorldGuard;

use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\exception\HookAlreadyRegistered;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\PacketHooker;
use fenomeno\NepheliaWorldGuard\Listeners\EditModeListener;
use fenomeno\NepheliaWorldGuard\Regions\RegionsManager;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase
{
    use SingletonTrait;

    public const BYPASS_PERMISSION = "nepheliaworldguard.bypass";
    private RegionsManager $regionsManager;

    protected function onLoad(): void
    {
        MessagesUtils::init($this);

        self::setInstance($this);
    }

    /**
     * @throws HookAlreadyRegistered
     */
    protected function onEnable(): void
    {
        if(! PacketHooker::isRegistered()){
            PacketHooker::register($this);
        }

        $this->regionsManager = new RegionsManager($this);

        $this->getServer()->getCommandMap()->register('nepheliaworldguard', new Command\CommandExecutor($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventDispatcher($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EditModeListener($this), $this);
    }

    public function getRegionsManager(): RegionsManager
    {
        return $this->regionsManager;
    }

}