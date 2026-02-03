<?php

namespace fenomeno\NepheliaWorldGuard\Listeners;

use fenomeno\NepheliaWorldGuard\Constants\Messages\ExtraTags;
use fenomeno\NepheliaWorldGuard\Constants\Messages\MessagesIds;
use fenomeno\NepheliaWorldGuard\Exceptions\RegionCreationException;
use fenomeno\NepheliaWorldGuard\Main;
use fenomeno\NepheliaWorldGuard\Session\Session;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\World;
use Throwable;

final readonly class EditModeListener implements Listener
{

    public function __construct(
        private Main $main
    ){}

    /** @noinspection PhpUnused */
    public function onSneak(PlayerToggleSneakEvent $event): void
    {
        $player  = $event->getPlayer();
        $session = Session::get($player);

        if(! $session->isInEditMode()){
            return;
        }

        if($event->isSneaking()){
            return;
        }

        try {
            match ($session->getMode()) {
                Session::MODE_CREATING => $this->handleCreationMode($player, $session),
                Session::MODE_REDEFINE => $this->handleRedefineMode($player, $session),
                default => null,
            };
        } catch (Throwable $e) {
            $this->handleError($player, $session, $e);
        }
    }

    /**
     * @throws RegionCreationException
     */
    private function handleCreationMode(Player $player, Session $session): void
    {
        $position = $this->getAdjustedPosition($player, $session->isExtended());

        switch ($session->getCurrentStage()) {
            case Session::STAGE_START:
            case Session::STAGE_ZONE_A:
                $this->setPosition1($player, $session, $position, MessagesIds::REGION_CREATION_POS_1_SET);
                break;

            case Session::STAGE_ZONE_B:
            case Session::STAGE_END:
                $this->setPosition2($player, $session, $position, MessagesIds::REGION_CREATION_POS_2_SET);
                $this->finalizeCreation($player, $session);
                break;
        }
    }

    /**
     * @throws RegionCreationException
     */
    private function handleRedefineMode(Player $player, Session $session): void
    {
        $position = $this->getAdjustedPosition($player, $session->isExtended());

        switch ($session->getCurrentStage()) {
            case Session::STAGE_START:
            case Session::STAGE_ZONE_A:
                $this->setPosition1($player, $session, $position, MessagesIds::REGION_REDEFINE_POS_1_SET);
                break;

            case Session::STAGE_ZONE_B:
            case Session::STAGE_END:
                $this->setPosition2($player, $session, $position, MessagesIds::REGION_REDEFINE_POS_2_SET);
                $this->finalizeRedefine($player, $session);
                break;
        }
    }

    /**
     * @throws RegionCreationException
     */
    private function setPosition1(Player $player, Session $session, Position $position, string $messageId): void
    {
        if ($session->isExtended()) {
            $position = new Position(
                $position->x,
                World::Y_MAX - 1,
                $position->z,
                $position->getWorld()
            );
        }

        $session->addPosition(Session::STAGE_ZONE_A, $position);
        $session->setCurrentStage(Session::STAGE_ZONE_B);

        MessagesUtils::sendTo($player, $messageId, [
            ExtraTags::X => (int) $position->getX(),
            ExtraTags::Y => (int) $position->getY(),
            ExtraTags::Z => (int) $position->getZ(),
        ]);

        $player->broadcastSound(new PopSound());
    }

    /**
     * @throws RegionCreationException
     */
    private function setPosition2(Player $player, Session $session, Position $position, string $messageId): void
    {
        if ($session->isExtended()) {
            $position = new Position(
                $position->x,
                World::Y_MIN,
                $position->z,
                $position->getWorld()
            );
        }

        $session->addPosition(Session::STAGE_ZONE_B, $position);
        $session->setCurrentStage(null);

        MessagesUtils::sendTo($player, $messageId, [
            ExtraTags::X => (int) $position->getX(),
            ExtraTags::Y => (int) $position->getY(),
            ExtraTags::Z => (int) $position->getZ(),
        ]);

        $player->broadcastSound(new PopSound());
    }

    /**
     * @throws RegionCreationException
     */
    private function finalizeCreation(Player $player, Session $session): void
    {
        $pos1   = $session->getPosition(Session::STAGE_ZONE_A);
        $pos2   = $session->getPosition(Session::STAGE_ZONE_B);
        $global = $session->isGlobal();

        $region = $this->main->getRegionsManager()->create(
            name: $session->getRegionName(),
            pos1: $pos1,
            pos2: $pos2,
            extended: $session->isExtended(),
            global: $global,
        );

        if ($region === null) {
            MessagesUtils::sendTo($player, MessagesIds::ERRORS_SAVE_FAILED);
            $session->endCreating();
            return;
        }

        $player->broadcastSound(new XpCollectSound());

        MessagesUtils::sendTo($player, $global
            ? MessagesIds::REGION_CREATION_SUCCESS_GLOBAL
            : MessagesIds::REGION_CREATION_SUCCESS, [
            ExtraTags::REGION => $region->name,
            ExtraTags::WORLD  => $player->getWorld()->getFolderName(),
        ]);

        $session->endCreating();
    }

    /**
     * @throws RegionCreationException
     */
    private function finalizeRedefine(Player $player, Session $session): void
    {
        $regionName = $session->getRegionName();
        $pos1       = $session->getPosition(Session::STAGE_ZONE_A);
        $pos2       = $session->getPosition(Session::STAGE_ZONE_B);

        $success = $this->main->getRegionsManager()->redefine(
            name: $regionName,
            pos1: $pos1,
            pos2: $pos2,
        );

        if (! $success) {
            MessagesUtils::sendTo($player, MessagesIds::REGION_REDEFINE_FAILED, [
                ExtraTags::REGION => $regionName,
                ExtraTags::REASON => "une erreur est survenue lors de la sauvegarde.",
            ]);
            $session->endRedefining();
            return;
        }

        $player->broadcastSound(new XpCollectSound());

        MessagesUtils::sendTo($player, MessagesIds::REGION_REDEFINE_SUCCESS, [
            ExtraTags::REGION => $regionName,
            ExtraTags::WORLD  => $player->getWorld()->getFolderName(),
        ]);

        $session->endRedefining();
    }

    private function handleError(Player $player, Session $session, Throwable $e): void
    {
        $this->main->getLogger()->error("Erreur pendant le mode édition: " . $e->getMessage());

        $player->sendMessage(TextFormat::RED . "Une erreur est survenue: " . $e->getMessage());

        $session->reset();
    }

    private function getAdjustedPosition(Player $player, bool $extended): Position
    {
        // Note: L'ajustement Y sera fait lors de la définition des positions
        return $player->getPosition();
    }

    /** @noinspection PhpUnused */
    public function onQuit(PlayerQuitEvent $event): void
    {
        $player  = $event->getPlayer();
        $session = Session::get($player);

        if($session->isInEditMode()){
            $session->reset();
        }
    }

}