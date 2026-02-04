<?php

namespace fenomeno\NepheliaWorldGuard\Enums;

use fenomeno\NepheliaWorldGuard\Regions\Region;
use fenomeno\NepheliaWorldGuard\Utils\MessagesUtils;

enum Flags: string
{

    case PluginBypass         = "plugin-bypass";
    case DenyMsg              = "deny-msg";
    case BlockPlace           = "block-place";
    case BlockBreak           = "block-break";
    case PVP                  = "pvp";
    case ConsoleCmdOnEnter    = "console-cmd-on-enter";
    case ConsoleCmdOnLeave    = "console-cmd-on-leave";
    case Flow                 = "flow";
    case ExpDrops             = "exp-drops";
    case Invincible           = "invincible";
    case FallDmg              = "fall-dmg";
    case Effects              = "effects";
    case BlockedCmds          = "blocked-cmds";
    case AllowedCmds          = "allowed-cmds";
    case Use                  = "use";
    case InteractFrame        = "interactframe";
    case ItemDrop             = "item-drop";
    case ItemByDeath          = "item-by-death";
    case ItemUse              = "item-use";
    case ItemPickup           = "item-pickup";
    case Explosion            = "explosion";
    case NotifyEnter          = "notify-enter";
    case NotifyLeave          = "notify-leave";
    case Potions              = "potions";
    case AllowedEnter         = "allowed-enter";
    case AllowedLeave         = "allowed-leave";
    case GameMode             = "game-mode";
    case Sleep                = "sleep";
    case SendChat             = "send-chat";
    case ReceiveChat          = "receive-chat";
    case Enderpearl           = "enderpearl";
    case Bow                  = "bow";
    case FlyMode              = "fly-mode";
    case Eat                  = "eat";
    case Hunger               = "hunger";
    case AllowDamageAnimals   = "allow-damage-animals";
    case AllowDamageMonsters  = "allow-damage-monsters";
    case AllowLeavesDecay     = "allow-leaves-decay";
    case AllowPlantGrowth     = "allow-plant-growth";
    case AllowSpreading       = "allow-spreading";
    case AllowBlockBurn       = "allow-block-burn";
    case Priority             = "priority";

    public function getCast(): string
    {
        return match($this) {
            self::Effects, self::BlockedCmds, self::AllowedCmds                                                    => 'array',
            self::FlyMode, self::Priority                                                                          => 'int',
            self::ConsoleCmdOnEnter, self::ConsoleCmdOnLeave, self::NotifyEnter, self::NotifyLeave, self::GameMode => 'string',
            default                                                                                                => 'bool',
        };
    }

    public function getDefault(): string|int|bool|array
    {
        return match($this) {
            self::PluginBypass, self::BlockPlace, self::BlockBreak,
            self::Invincible, self::Use, self::InteractFrame, self::Explosion => false,

            self::DenyMsg, self::PVP, self::Flow, self::ExpDrops, self::FallDmg,
            self::ItemDrop, self::ItemByDeath, self::Potions, self::AllowedEnter,
            self::AllowedLeave, self::Sleep, self::SendChat, self::ReceiveChat,
            self::Enderpearl, self::Bow, self::Eat, self::Hunger,
            self::AllowDamageAnimals, self::AllowDamageMonsters,
            self::AllowLeavesDecay, self::AllowPlantGrowth, self::ItemPickup,
            self::AllowSpreading, self::AllowBlockBurn, self::ItemUse => true,

            self::ConsoleCmdOnEnter, self::ConsoleCmdOnLeave => "none",
            self::NotifyEnter, self::NotifyLeave => "",
            self::GameMode => "false",

            self::FlyMode, self::Priority => 0,

            self::Effects, self::BlockedCmds, self::AllowedCmds => [],
        };
    }

    public function getName(): string
    {
        return match($this) {
            self::PluginBypass         => "Contournement des plugins",
            self::DenyMsg              => "Message de refus",
            self::BlockPlace           => "Placement de blocs",
            self::BlockBreak           => "Destruction de blocs",
            self::PVP                  => "PVP",
            self::ConsoleCmdOnEnter    => "Commande console à l'entrée",
            self::ConsoleCmdOnLeave    => "Commande console à la sortie",
            self::Flow                 => "Écoulement des liquides",
            self::ExpDrops             => "Chute d'orbes d'expérience",
            self::Invincible           => "Invincibilité",
            self::FallDmg              => "Dégâts de chute",
            self::Effects              => "Effets de potion",
            self::BlockedCmds          => "Commandes bloquées",
            self::AllowedCmds          => "Commandes autorisées",
            self::Use                  => "Utilisation de blocs",
            self::InteractFrame        => "Interaction avec cadres",
            self::ItemDrop             => "Chute d'objets",
            self::ItemByDeath          => "Chute d'objets à la mort",
            self::Explosion            => "Explosions",
            self::NotifyEnter          => "Notification à l'entrée",
            self::NotifyLeave          => "Notification à la sortie",
            self::Potions              => "Potions",
            self::AllowedEnter         => "Entrée autorisée",
            self::AllowedLeave         => "Sortie autorisée",
            self::GameMode             => "Mode de jeu",
            self::Sleep                => "Sommeil",
            self::SendChat             => "Envoi de messages",
            self::ReceiveChat          => "Réception de messages",
            self::Enderpearl           => "Perles de l'Ender",
            self::Bow                  => "Arc",
            self::FlyMode              => "Mode vol",
            self::Eat                  => "Consommation de nourriture",
            self::Hunger               => "Faim",
            self::AllowDamageAnimals   => "Dégâts aux animaux",
            self::AllowDamageMonsters  => "Dégâts aux monstres",
            self::AllowLeavesDecay     => "Décomposition des feuilles",
            self::AllowPlantGrowth     => "Croissance des plantes",
            self::AllowSpreading       => "Propagation des blocs",
            self::AllowBlockBurn       => "Combustion des blocs",
            self::Priority             => "Priorité",
            self::ItemUse              => "Utilisation d'objets",
            self::ItemPickup           => "Récupération d'objets",
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::PluginBypass         => "Permet aux autres plugins de contourner les protections de la région",
            self::DenyMsg              => "Affiche un message au joueur lorsqu'une action est refusée",
            self::BlockPlace           => "Interdit le placement de blocs dans la région",
            self::BlockBreak           => "Interdit la destruction de blocs dans la région",
            self::PVP                  => "Autorise les combats entre joueurs",
            self::ConsoleCmdOnEnter    => "Commande console exécutée à l'entrée (%player%)",
            self::ConsoleCmdOnLeave    => "Commande console exécutée à la sortie (%player%)",
            self::Flow                 => "Autorise l'écoulement des liquides",
            self::ExpDrops             => "Autorise le drop d'orbes d'expérience",
            self::Invincible           => "Rend les joueurs invincibles",
            self::FallDmg              => "Autorise les dégâts de chute",
            self::Effects              => "Effets de potion appliqués aux joueurs",
            self::BlockedCmds          => "Commandes interdites (blacklist)",
            self::AllowedCmds          => "Commandes autorisées (whitelist)",
            self::Use                  => "Autorise l'interaction avec les blocs",
            self::InteractFrame        => "Autorise l'interaction avec les cadres",
            self::ItemDrop             => "Autorise le drop d'items",
            self::ItemByDeath          => "Autorise le drop d'items à la mort",
            self::Explosion            => "Autorise les explosions",
            self::NotifyEnter          => "Message affiché à l'entrée",
            self::NotifyLeave          => "Message affiché à la sortie",
            self::Potions              => "Autorise l'utilisation de potions",
            self::AllowedEnter         => "Autorise l'entrée dans la région",
            self::AllowedLeave         => "Autorise la sortie de la région",
            self::GameMode             => "Force un mode de jeu",
            self::Sleep                => "Autorise le sommeil",
            self::SendChat             => "Autorise l'envoi de messages",
            self::ReceiveChat          => "Autorise la réception de messages",
            self::Enderpearl           => "Autorise les perles de l'Ender",
            self::Bow                  => "Autorise l'utilisation de l'arc",
            self::FlyMode              => "Mode de vol (0=vanilla, 1=on, 2=off, 3=supervised)",
            self::Eat                  => "Autorise la consommation de nourriture",
            self::Hunger               => "Active la perte de faim",
            self::AllowDamageAnimals   => "Autorise les dégâts aux animaux",
            self::AllowDamageMonsters  => "Autorise les dégâts aux monstres",
            self::AllowLeavesDecay     => "Autorise la décomposition des feuilles",
            self::AllowPlantGrowth     => "Autorise la croissance des plantes",
            self::AllowSpreading       => "Autorise la propagation des blocs",
            self::AllowBlockBurn       => "Autorise la combustion des blocs",
            self::Priority             => "Priorité de la région",
            self::ItemUse              => "Autorise l'utilisation d'objets",
            self::ItemPickup           => "Autorise la récupération d'objets",
        };
    }

    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $flag) {
            $result[$flag->value] = $flag->getDefault();
        }
        return $result;
    }

    public function getPermission(): string
    {
        return "nepheliaworldguard.flag." . str_replace("-", "", $this->value);
    }

    public function getPermissionByRegion(Region $region): string
    {
        return $this->getPermission() . "." . $region->name;
    }

    public function getDenyMessage(): ?string
    {
        return MessagesUtils::getMessage('denied.' . $this->value);
    }


}