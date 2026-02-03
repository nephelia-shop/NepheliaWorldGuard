<?php

namespace fenomeno\NepheliaWorldGuard\Command\SubCommands;

use fenomeno\NepheliaWorldGuard\Constants\Messages\ExtraTags;
use fenomeno\NepheliaWorldGuard\Constants\Messages\MessagesIds;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\args\RawStringArgument;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\BaseSubCommand;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\exception\ArgumentOrderException;
use fenomeno\NepheliaWorldGuard\Main;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;
use pocketmine\command\CommandSender;

final class ParentSubCommand extends BaseSubCommand
{
    private const REGION_ARGUMENT = "region";
    private const ACTION_ARGUMENT = "action";
    private const PARENT_ARGUMENT = "parent";

    public function __construct(private readonly Main $main)
    {
        parent::__construct("parent", "Gérer le parent d'une région /nwg parent <region> <set|remove|info> [parent]");
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("nepheliaworldguard.use.parent");

        $this->registerArgument(0, new RawStringArgument(self::REGION_ARGUMENT, false));
        $this->registerArgument(1, new RawStringArgument(self::ACTION_ARGUMENT, false));
        $this->registerArgument(2, new RawStringArgument(self::PARENT_ARGUMENT, true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $regionName = (string) ($args[self::REGION_ARGUMENT] ?? "");
        $action     = strtolower((string) ($args[self::ACTION_ARGUMENT] ?? ""));

        $region = $this->main->getRegionsManager()->getRegion($regionName);
        if ($region === null) {
            MessagesUtils::sendTo($sender, MessagesIds::REGION_NOT_FOUND, [
                ExtraTags::REGION => $regionName
            ]);
            return;
        }

        match ($action) {
            'set'                      => $this->handleSet($sender, $region, $args),
            'remove', 'unset', 'clear' => $this->handleRemove($sender, $region),
            'info', 'get'              => $this->handleInfo($sender, $region),
            default                    => MessagesUtils::sendTo($sender, MessagesIds::GENERAL_INVALID_ARGUMENTS, [
                ExtraTags::USAGE => "/nwg parent <region> <set|remove|info> [parent]"
            ]),
        };
    }

    private function handleSet(CommandSender $sender, Region $region, array $args): void
    {
        if (! isset($args[self::PARENT_ARGUMENT])) {
            MessagesUtils::sendTo($sender, MessagesIds::PARENT_SET_USAGE);
            return;
        }

        $parentName = (string) $args[self::PARENT_ARGUMENT];

        if (! $this->main->getRegionsManager()->exists($parentName)) {
            MessagesUtils::sendTo($sender, MessagesIds::REGION_NOT_FOUND, [
                ExtraTags::REGION => $parentName
            ]);
            return;
        }

        if ($parentName === $region->name) {
            MessagesUtils::sendTo($sender, MessagesIds::PARENT_SELF_REFERENCE);
            return;
        }

        if ($this->main->getRegionsManager()->setParent($region->name, $parentName)) {
            MessagesUtils::sendTo($sender, MessagesIds::PARENT_SET_SUCCESS, [
                ExtraTags::REGION => $region->name,
                ExtraTags::PARENT => $parentName,
            ]);
        } else {
            MessagesUtils::sendTo($sender, MessagesIds::PARENT_SET_FAILED, [
                ExtraTags::REGION => $region->name,
                ExtraTags::PARENT => $parentName,
            ]);
        }
    }

    private function handleRemove(CommandSender $sender, Region $region): void
    {
        if ($region->parent === null) {
            MessagesUtils::sendTo($sender, MessagesIds::PARENT_NO_PARENT, [
                ExtraTags::REGION => $region->name
            ]);
            return;
        }

        $oldParent = $region->parent;

        if ($this->main->getRegionsManager()->setParent($region->name, null)) {
            MessagesUtils::sendTo($sender, MessagesIds::PARENT_REMOVE_SUCCESS, [
                ExtraTags::REGION => $region->name,
                ExtraTags::PARENT => $oldParent,
            ]);
        } else {
            MessagesUtils::sendTo($sender, MessagesIds::ERRORS_SAVE_FAILED);
        }
    }

    private function handleInfo(CommandSender $sender, Region $region): void
    {
        $manager = $this->main->getRegionsManager();

        $parentName = $region->parent ?? "(aucun)";

        $parentChain = $manager->getParentChain($region->name);
        $chainNames  = empty($parentChain) ? "(aucun)" : implode(" → ", array_keys($parentChain));

        $children     = $manager->getChildren($region->name);
        $childrenList = empty($children) ? "(aucun)" : implode(", ", array_keys($children));

        $descendants     = $manager->getAllDescendants($region->name);
        $descendantCount = count($descendants);

        MessagesUtils::sendTo($sender, MessagesIds::PARENT_INFO_HEADER, [
            ExtraTags::REGION => $region->name
        ]);

        MessagesUtils::sendTo($sender, MessagesIds::PARENT_INFO_PARENT, [
            ExtraTags::PARENT => $parentName
        ]);

        MessagesUtils::sendTo($sender, MessagesIds::PARENT_INFO_CHAIN, [
            ExtraTags::VALUE => $chainNames
        ]);

        MessagesUtils::sendTo($sender, MessagesIds::PARENT_INFO_CHILDREN, [
            ExtraTags::VALUE => $childrenList
        ]);

        MessagesUtils::sendTo($sender, MessagesIds::PARENT_INFO_DESCENDANTS, [
            ExtraTags::COUNT => $descendantCount
        ]);

        $flagsWithSource = $region->getAllFlagsWithSource();
        $inheritedCount  = 0;

        foreach ($flagsWithSource as $flagData) {
            if ($flagData['inherited']) {
                $inheritedCount++;
            }
        }

        MessagesUtils::sendTo($sender, MessagesIds::PARENT_INFO_INHERITED_FLAGS, [
            ExtraTags::COUNT => $inheritedCount
        ]);

        MessagesUtils::sendTo($sender, MessagesIds::FLAGS_GET_FOOTER);
    }
}