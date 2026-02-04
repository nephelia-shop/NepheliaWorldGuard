<?php

namespace fenomeno\NepheliaWorldGuard\Command\SubCommands;

use fenomeno\NepheliaWorldGuard\Command\Arguments\FlagArgument;
use fenomeno\NepheliaWorldGuard\Command\Arguments\FlagsActionArgument;
use fenomeno\NepheliaWorldGuard\Command\Arguments\FlagValueArgument;
use fenomeno\NepheliaWorldGuard\Constants\Messages\ExtraTags;
use fenomeno\NepheliaWorldGuard\Constants\Messages\MessagesIds;
use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\args\RawStringArgument;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\args\TextArgument;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\BaseSubCommand;
use fenomeno\NepheliaWorldGuard\libs\CortexPE\Commando\exception\ArgumentOrderException;
use fenomeno\NepheliaWorldGuard\Main;
use fenomeno\NepheliaWorldGuard\Regions\Region;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;
use pocketmine\command\CommandSender;

final class FlagsSubCommand extends BaseSubCommand
{

    private const REGION_ARGUMENT = "region";
    private const ACTION_ARGUMENT = "action";
    private const FLAG_ARGUMENT   = "flag";
    private const VALUE_ARGUMENT  = "value";

    private static array $confirmReset = [];

    public function __construct(private readonly Main $main)
    {
        parent::__construct("flags", "Gérer les flags d'une région /nwg flags <region> get|set|reset <flag> [value]");
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("nepheliaworldguard.use.flags");

        $this->registerArgument(0, new RawStringArgument(self::REGION_ARGUMENT, false));
        $this->registerArgument(1, new FlagsActionArgument(self::ACTION_ARGUMENT, false));
        $this->registerArgument(2, new FlagArgument(self::FLAG_ARGUMENT, true));
        $this->registerArgument(3, new TextArgument(self::VALUE_ARGUMENT, true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $regionName = (string) $args[self::REGION_ARGUMENT];
        $action     = (string) $args[self::ACTION_ARGUMENT];

        $region = $this->main->getRegionsManager()->getRegion($regionName);
        if(! $region){
            MessagesUtils::sendTo($sender, MessagesIds::REGION_NOT_FOUND, [
                ExtraTags::REGION => $regionName
            ]);
            return;
        }

        match ($action) {
            FlagsActionArgument::ACTION_GET   => $this->handleGet($sender, $region, $args),
            FlagsActionArgument::ACTION_SET   => $this->handleSet($sender, $region, $args),
            FlagsActionArgument::ACTION_RESET => $this->handleReset($sender, $region, $args),
            FlagsActionArgument::ACTION_LIST  => $this->handleList($sender),
            default => MessagesUtils::sendTo($sender, MessagesIds::GENERAL_INVALID_ARGUMENTS, [
                ExtraTags::USAGE => "/nwg flags <region> <get|set|reset|list> [flag] [value]"
            ]),
        };
    }

    private function handleGet(CommandSender $sender, Region $region, array $args): void
    {
        if (isset($args[self::FLAG_ARGUMENT])) {
            $flag = $args[self::FLAG_ARGUMENT];

            if (! $flag instanceof Flags) {
                MessagesUtils::sendTo($sender, MessagesIds::FLAGS_SET_INVALID_FLAG, [
                    ExtraTags::FLAG => (string) ($args[self::FLAG_ARGUMENT] ?? "")
                ]);
                return;
            }

            $value = $region->getFlag($flag);

            MessagesUtils::sendTo($sender, MessagesIds::FLAGS_GET_SPECIFIC, [
                ExtraTags::REGION => $region->name,
                ExtraTags::FLAG   => $flag->value,
                ExtraTags::VALUE  => $this->formatValue($value),
                ExtraTags::TYPE   => $flag->getCast(),
                ExtraTags::DESC   => $flag->getDescription(),
            ]);
            return;
        }

        MessagesUtils::sendTo($sender, MessagesIds::FLAGS_GET_HEADER, [
            ExtraTags::REGION => $region->name
        ]);

        foreach (Flags::cases() as $flag) {
            $value      = $region->getFlag($flag);
            $isModified = $region->isFlagModified($flag);

            $messageId = $isModified ? MessagesIds::FLAGS_GET_ENTRY_MODIFIED : MessagesIds::FLAGS_GET_ENTRY;

            MessagesUtils::sendTo($sender, $messageId, [
                ExtraTags::FLAG  => $flag->value,
                ExtraTags::VALUE => $this->formatValue($value),
                ExtraTags::TYPE  => $flag->getCast(),
            ]);
        }

        MessagesUtils::sendTo($sender, MessagesIds::FLAGS_GET_FOOTER);
    }

    private function handleSet(CommandSender $sender, Region $region, array $args): void
    {
        if (! isset($args[self::FLAG_ARGUMENT])) {
            MessagesUtils::sendTo($sender, MessagesIds::FLAGS_SET_USAGE, [
                ExtraTags::USAGE => "/nwg flags $region->name set <flag> <value>"
            ]);
            return;
        }

        $flag = $args[self::FLAG_ARGUMENT];

        if (! $flag instanceof Flags) {
            MessagesUtils::sendTo($sender, MessagesIds::FLAGS_SET_INVALID_FLAG, [
                ExtraTags::FLAG => (string) ($args[self::FLAG_ARGUMENT] ?? "")
            ]);
            return;
        }

        if (! isset($args[self::VALUE_ARGUMENT])) {
            MessagesUtils::sendTo($sender, MessagesIds::FLAGS_SET_USAGE, [
                ExtraTags::USAGE    => "/nwg flags $region->name set $flag->value <value>",
                ExtraTags::EXPECTED => $this->getExpectedFormat($flag),
            ]);
            return;
        }

        $rawValue = (string) $args[self::VALUE_ARGUMENT];
        $result   = $this->parseAndValidateValue($flag, $rawValue);

        if ($result['error'] !== null) {
            MessagesUtils::sendTo($sender, MessagesIds::FLAGS_SET_INVALID_VALUE, [
                ExtraTags::FLAG     => $flag->value,
                ExtraTags::VALUE    => $rawValue,
                ExtraTags::EXPECTED => $result['error'],
            ]);
            return;
        }

        $value = $result['value'];
        $region->setFlag($flag, $value);
        if ($this->main->getRegionsManager()->saveRegion($region)) {
            MessagesUtils::sendTo($sender, MessagesIds::FLAGS_SET_SUCCESS, [
                ExtraTags::REGION => $region->name,
                ExtraTags::FLAG   => $flag->value,
                ExtraTags::VALUE  => $this->formatValue($value),
            ]);
        } else {
            MessagesUtils::sendTo($sender, MessagesIds::FLAGS_SET_FAILURE, [
                ExtraTags::REGION => $region->name,
                ExtraTags::FLAG   => $flag->value,
            ]);
        }
    }

    private function handleReset(CommandSender $sender, Region $region, array $args): void
    {
        $senderName = $sender->getName();

        if (isset($args[self::FLAG_ARGUMENT])) {
            $flag = $args[self::FLAG_ARGUMENT];

            if (! $flag instanceof Flags) {
                MessagesUtils::sendTo($sender, MessagesIds::FLAGS_SET_INVALID_FLAG, [
                    ExtraTags::FLAG => (string) ($args[self::FLAG_ARGUMENT] ?? "")
                ]);
                return;
            }

            $region->resetFlag($flag);

            if ($this->main->getRegionsManager()->saveRegion($region)) {
                MessagesUtils::sendTo($sender, MessagesIds::FLAGS_RESET_SUCCESS, [
                    ExtraTags::REGION => $region->name,
                    ExtraTags::FLAG   => $flag->value,
                    ExtraTags::VALUE  => $this->formatValue($flag->getDefault()),
                ]);
            } else {
                MessagesUtils::sendTo($sender, MessagesIds::ERRORS_SAVE_FAILED);
            }

            unset(self::$confirmReset[$senderName]);
            return;
        }

        $confirmKey = $senderName . ":" . $region->name;

        if (! isset(self::$confirmReset[$confirmKey])) {
            self::$confirmReset[$confirmKey] = time();

            MessagesUtils::sendTo($sender, MessagesIds::FLAGS_RESET_CONFIRM, [
                ExtraTags::REGION => $region->name,
                ExtraTags::COUNT  => count($region->getModifiedFlags()),
            ]);
            return;
        }

        if (time() - self::$confirmReset[$confirmKey] > 30) {
            unset(self::$confirmReset[$confirmKey]);

            MessagesUtils::sendTo($sender, MessagesIds::FLAGS_RESET_EXPIRED);
            return;
        }

        $modifiedCount = count($region->getModifiedFlags());
        $region->resetAllFlags();

        if ($this->main->getRegionsManager()->saveRegion($region)) {
            MessagesUtils::sendTo($sender, MessagesIds::FLAGS_RESET_SUCCESS_ALL, [
                ExtraTags::REGION => $region->name,
                ExtraTags::COUNT  => $modifiedCount,
            ]);
        } else {
            MessagesUtils::sendTo($sender, MessagesIds::ERRORS_SAVE_FAILED);
        }

        unset(self::$confirmReset[$confirmKey]);
    }

    private function parseAndValidateValue(Flags $flag, string $rawValue): array
    {
        $cast = $flag->getCast();

        return match ($cast) {
            'bool'   => $this->parseBool($rawValue),
            'int'    => $this->parseInt($rawValue, $flag),
            'string' => $this->parseString($rawValue),
            'array'  => $this->parseArray($rawValue),
            default  => ['value' => $rawValue, 'error' => null],
        };
    }

    private function parseBool(string $value): array
    {
        $lower = strtolower($value);

        if (in_array($lower, ['true', '1', 'yes', 'on', 'allow', 'oui'], true)) {
            return ['value' => true, 'error' => null];
        }

        if (in_array($lower, ['false', '0', 'no', 'off', 'deny', 'non'], true)) {
            return ['value' => false, 'error' => null];
        }

        return ['value' => null, 'error' => 'true/false, yes/no, on/off, allow/deny'];
    }

    private function parseInt(string $value, Flags $flag): array
    {
        if (! is_numeric($value)) {
            return ['value' => null, 'error' => 'un nombre entier'];
        }

        $intValue = (int) $value;

        if ($flag === Flags::FlyMode) {
            if ($intValue < 0 || $intValue > 3) {
                return ['value' => null, 'error' => '0 (vanilla), 1 (activé), 2 (désactivé), 3 (supervisé)'];
            }
        }

        if ($flag === Flags::Priority) {
            if ($intValue < -100 || $intValue > 100) {
                return ['value' => null, 'error' => 'un nombre entre -100 et 100'];
            }
        }

        return ['value' => $intValue, 'error' => null];
    }

    private function parseString(string $value): array
    {
        $value = str_replace('&', '§', $value);

        return ['value' => $value, 'error' => null];
    }

    private function parseArray(string $value): array
    {
        if (strtolower($value) === 'none' || $value === '' || $value === '[]') {
            return ['value' => [], 'error' => null];
        }

        $items = explode(',', $value);
        $items = array_map('trim', $items);
        $items = array_filter($items, fn($item) => $item !== '');

        $result = [];
        foreach ($items as $item) {
            $result[$item] = true;
        }
        return ['value' => $result, 'error' => null];
    }

    private function handleList(CommandSender $sender): void
    {
        MessagesUtils::sendTo($sender, MessagesIds::FLAGS_LIST_HEADER, [
            ExtraTags::COUNT => count(Flags::cases())
        ]);

        foreach (Flags::cases() as $flag) {
            MessagesUtils::sendTo($sender, MessagesIds::FLAGS_LIST_ENTRY, [
                ExtraTags::FLAG  => $flag->value,
                ExtraTags::TYPE  => $flag->getCast(),
                ExtraTags::DESC  => $flag->getDescription(),
                ExtraTags::VALUE => $this->formatValue($flag->getDefault()),
            ]);
        }

        MessagesUtils::sendTo($sender, MessagesIds::FLAGS_GET_FOOTER);
    }

    private function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '§atrue' : '§cfalse';
        }

        if (is_array($value)) {
            if (empty($value)) {
                return '§8(aucun)';
            }

            $keys = array_keys($value);
            if (count($keys) > 5) {
                return implode(', ', array_slice($keys, 0, 5)) . '... (+' . (count($keys) - 5) . ')';
            }
            return implode(', ', $keys);
        }

        if ($value === null || $value === '' || $value === 'none') {
            return '§8(aucun)';
        }

        if (is_int($value)) {
            return '§b' . $value;
        }

        return (string) $value;
    }

    private function getExpectedFormat(Flags $flag): string
    {
        return match ($flag->getCast()) {
            'bool'   => 'true/false',
            'int'    => match ($flag) {
                Flags::FlyMode  => '0-3 (0=vanilla, 1=on, 2=off, 3=supervised)',
                Flags::Priority => '-100 à 100',
                default         => 'nombre entier',
            },
            'string' => 'texte (& pour couleurs)',
            'array'  => 'valeur1,valeur2,valeur3 ou "none" pour vider',
            default  => 'valeur',
        };
    }

}