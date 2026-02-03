<?php

namespace fenomeno\NepheliaWorldGuard\Flags;

use fenomeno\NepheliaWorldGuard\Enums\Flags;

final readonly class FlagResult
{

    public function __construct(
        public bool    $cancelled,
        public ?Flags  $flag = null,
        public ?string $message = null
    ){}

    public static function allow(?Flags $flag = null, ?string $message = null): self
    {
        return new self(false, $flag, $message);
    }

    public static function deny(Flags $flag, ?string $message = null): self
    {
        return new self(true, $flag, $message);
    }

    public static function denySilent(Flags $flag): self
    {
        return new self(true, $flag, null);
    }

}