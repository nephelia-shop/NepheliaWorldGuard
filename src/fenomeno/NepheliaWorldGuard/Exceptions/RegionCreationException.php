<?php

namespace fenomeno\NepheliaWorldGuard\Exceptions;

use Exception;

class RegionCreationException extends Exception
{

    public static function invalidStage(int $stage): self
    {
        return new self("Stage '$stage' n'est pas valide pour la création de région.");
    }

    public static function stageAlreadySet(int $stage): self
    {
        return new self("Le stage '$stage' a déjà été défini pour la création de région.");
    }

    public static function positionNotSet(int $stage): self
    {
        return new self("La position pour le stage '$stage' n'a pas encore été définie.");
    }

    public static function noNameSet(): self
    {
        return new self("Aucun nom n'a été défini pour la création de région.");
    }

}