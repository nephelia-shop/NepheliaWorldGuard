<?php

namespace fenomeno\NepheliaWorldGuard;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Flags\FlagRegistry;
use fenomeno\NepheliaWorldGuard\Flags\FlagResult;
use fenomeno\NepheliaWorldGuard\Flags\Types\DamageHandler;
use fenomeno\NepheliaWorldGuard\Flags\Types\MovementHandler;
use fenomeno\NepheliaWorldGuard\Flags\Types\PlayerStateHandler;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Event;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;
use pocketmine\world\Position;

final class EventDispatcher implements Listener
{

    private FlagRegistry $registry;

    /** @var array<string, string> Player UUID => Region name */
    private array $playerRegions = [];

    /** @var array<string, Player> UUID => Player (pour receive-chat) */
    private array $mutedPlayers = [];

    public function __construct(
        private readonly Main $main
    ){
        $this->registry = FlagRegistry::getInstance($this->main);
    }

    /**
     * @priority LOWEST
     * @noinspection PhpUnused
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $uuid   = $player->getUniqueId()->getBytes();

        $this->playerRegions[$uuid] = "";
        $this->updatePlayerRegion($player);
    }

    /**
     * @priority MONITOR
     * @noinspection PhpUnused
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $uuid   = $player->getUniqueId()->getBytes();

        if (isset($this->playerRegions[$uuid])) {
            $regionName = $this->playerRegions[$uuid];
            if ($regionName !== "") {
                $region = $this->main->getRegionsManager()->getRegion($regionName);
                if ($region !== null) {
                    $uuid = $player->getUniqueId()->getBytes();
                    $this->playerRegions[$uuid] = "";
                    $this->updatePlayerRegion($player);
                }
            }
            unset($this->playerRegions[$uuid]);
        }

        unset($this->mutedPlayers[$uuid]);
    }

    /**
     * @priority NORMAL
     * @noinspection PhpUnused
     */
    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $this->updatePlayerRegion($player);
    }

    private function updatePlayerRegion(Player $player): void
    {
        $uuid              = $player->getUniqueId()->getBytes();
        $currentRegionName = $this->playerRegions[$uuid] ?? "";
        $newRegionName     = $this->main->getRegionsManager()->getRegionNameAt($player->getPosition());

        if(is_null($newRegionName) || is_null($currentRegionName)) {
            $newRegionName     = $newRegionName ?? "";
            $currentRegionName = $currentRegionName ?? "";
        }

        if ($newRegionName === $currentRegionName) {
            return;
        }

        $this->handleRegionChange($player, $currentRegionName, $newRegionName);
    }

    private function handleRegionChange(Player $player, string $oldRegionName, string $newRegionName): void
    {
        $uuid      = $player->getUniqueId()->getBytes();
        $oldRegion = $oldRegionName !== "" ? $this->main->getRegionsManager()->getRegion($oldRegionName) : null;
        $newRegion = $newRegionName !== "" ? $this->main->getRegionsManager()->getRegion($newRegionName) : null;

        $player->sendMessage("leave " . $oldRegionName);
        $player->sendMessage("enter " . $newRegionName);

        /** @var MovementHandler $movementHandler */
        $movementHandler = $this->registry->getHandler(MovementHandler::class);

        /** @var PlayerStateHandler $stateHandler */
        $stateHandler = $this->registry->getHandler(PlayerStateHandler::class);

        if ($oldRegion !== null) {
            $result = $movementHandler->onRegionLeave($player, $oldRegion);
            if ($result->cancelled) {
                $this->sendDenyMessage($player, $result);
                return;
            }

            $stateHandler->removePlayerState($player, $oldRegion);
            unset($this->mutedPlayers[$uuid]);
        }

        if ($newRegion !== null) {
            $result = $movementHandler->onRegionEnter($player, $newRegion);
            if ($result->cancelled) {
                $this->sendDenyMessage($player, $result);
                return;
            }

            $stateHandler->applyPlayerState($player, $newRegion);

            if (! $newRegion->getFlag(Flags::ReceiveChat)) {
                $this->mutedPlayers[$uuid] = $player;
            }
        }

        $this->playerRegions[$uuid] = $newRegionName;
    }

    // =========================================================================
    // BLOCK EVENTS
    // =========================================================================

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        $this->dispatchBlockEvent($event, $event->getBlockAgainst()->getPosition());
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $this->dispatchBlockEvent($event, $event->getBlock()->getPosition());
    }

    // =========================================================================
    // ENVIRONMENT EVENTS
    // =========================================================================

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onLeavesDecay(LeavesDecayEvent $event): void
    {
        $this->dispatchEnvironmentEvent($event, $event->getBlock()->getPosition());
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onBlockGrow(BlockGrowEvent $event): void
    {
        $this->dispatchEnvironmentEvent($event, $event->getBlock()->getPosition());
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onBlockSpread(BlockSpreadEvent $event): void
    {
        $this->dispatchEnvironmentEvent($event, $event->getBlock()->getPosition());
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onBlockBurn(BlockBurnEvent $event): void
    {
        $this->dispatchEnvironmentEvent($event, $event->getBlock()->getPosition());
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onBlockForm(BlockFormEvent $event): void
    {
        $this->dispatchEnvironmentEvent($event, $event->getBlock()->getPosition());
    }

    // =========================================================================
    // ENTITY EVENTS
    // =========================================================================

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        $region = $this->main->getRegionsManager()->getRegionAt($entity->getPosition());

        if ($region === null) {
            return;
        }

        $player = null;
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if ($damager instanceof Player) {
                $player = $damager;
            }
        }

        $this->dispatchToHandlers($event, $region, $player);
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onEntityExplode(EntityExplodeEvent $event): void
    {
        $position = $event->getPosition();
        $region   = $this->main->getRegionsManager()->getRegionAt($position);

        if ($region === null) {
            return;
        }

        $result = $this->dispatchToHandlers($event, $region);

        if ($result->cancelled) {
            $event->setBlockList([]);
        }
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onProjectileLaunch(ProjectileLaunchEvent $event): void
    {
        $projectile = $event->getEntity();
        $owner      = $projectile->getOwningEntity();

        if (! $owner instanceof Player) {
            return;
        }

        $region = $this->main->getRegionsManager()->getRegionAt($owner->getPosition());
        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $owner);
    }

    // =========================================================================
    // PLAYER EVENTS
    // =========================================================================

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $region = $this->main->getRegionsManager()->getRegionAt($event->getBlock()->getPosition());

        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $player);
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onInventoryOpen(InventoryOpenEvent $event): void
    {
        $player = $event->getPlayer();
        $region = $this->main->getRegionsManager()->getRegionAt($player->getPosition());

        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $player);
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onPlayerDropItem(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();
        $region = $this->main->getRegionsManager()->getRegionAt($player->getPosition());

        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $player);
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onPickup(EntityItemPickupEvent $event): void
    {
        $entity = $event->getEntity();
        if (! $entity instanceof Player) {
            return;
        }

        $region = $this->main->getRegionsManager()->getRegionAt($entity->getPosition());

        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $entity);
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $region = $this->main->getRegionsManager()->getRegionAt($player->getPosition());

        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $player);
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $region = $this->main->getRegionsManager()->getRegionAt($player->getPosition());

        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $player);
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onPlayerItemConsume(PlayerItemConsumeEvent $event): void
    {
        $player = $event->getPlayer();
        $region = $this->main->getRegionsManager()->getRegionAt($player->getPosition());

        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $player);
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onPlayerExhaust(PlayerExhaustEvent $event): void
    {
        $player = $event->getPlayer();
        $region = $this->main->getRegionsManager()->getRegionAt($player->getPosition());

        if ($region === null || ! $player instanceof Player) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $player);
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onPlayerExperienceChange(PlayerExperienceChangeEvent $event): void
    {
        $player = $event->getEntity();

        if (! $player instanceof Player) {
            return;
        }

        $region = $this->main->getRegionsManager()->getRegionAt($player->getPosition());

        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $player);
    }

    // =========================================================================
    // CHAT & COMMAND EVENTS
    // =========================================================================

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onPlayerChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $region = $this->main->getRegionsManager()->getRegionAt($player->getPosition());

        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $player);

        if (! $event->isCancelled()) {
            $recipients         = $event->getRecipients();
            $filteredRecipients = [];

            foreach ($recipients as $recipient) {
                if ($recipient instanceof Player) {
                    $uuid = $recipient->getUniqueId()->getBytes();
                    if (! isset($this->mutedPlayers[$uuid])) {
                        $filteredRecipients[] = $recipient;
                    }
                } else {
                    $filteredRecipients[] = $recipient;
                }
            }

            $event->setRecipients($filteredRecipients);
        }
    }

    /**
     * @priority HIGH
     * @noinspection PhpUnused
     */
    public function onCommand(CommandEvent $event): void
    {
        $sender = $event->getSender();

        if (! $sender instanceof Player) {
            return;
        }

        $region = $this->main->getRegionsManager()->getRegionAt($sender->getPosition());

        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region, $sender);
    }

    private function dispatchBlockEvent(Event|Cancellable $event, Position $position): void
    {
        $region = $this->main->getRegionsManager()->getRegionAt($position);

        if ($region === null) {
            return;
        }

        $player = null;
        if (method_exists($event, 'getPlayer')) {
            $player = $event->getPlayer();
        }

        $this->dispatchToHandlers($event, $region, $player);
    }

    private function dispatchEnvironmentEvent(Event&Cancellable $event, Position $position): void
    {
        $region = $this->main->getRegionsManager()->getRegionAt($position);

        if ($region === null) {
            return;
        }

        $this->dispatchToHandlers($event, $region);
    }

    private function dispatchToHandlers(Event $event, Region $region, ?Player $player = null): FlagResult
    {
        if ($region->hasPluginBypass()) {
            return FlagResult::allow();
        }

        foreach ($this->registry->getHandlers() as $handler) {
            $result = $handler->handle($event, $region, $player);

            if ($result->cancelled) {
                if ($event instanceof Cancellable) {
                    $event->cancel();
                }

                if ($player !== null) {
                    $this->sendDenyMessage($player, $result);
                }

                return $result;
            }
        }

        return FlagResult::allow();
    }

    private function sendDenyMessage(Player $player, FlagResult $result): void
    {
        $extraTags = [];
        $message = $result->message ?? $result->flag?->getDenyMessage() ?? null;
        if ($message !== null) {
            MessagesUtils::sendTo($player, $message, $extraTags);
        }
    }

}