<?php

namespace fenomeno\NepheliaWorldGuard\Flags;

use fenomeno\NepheliaWorldGuard\Enums\Flags;
use fenomeno\NepheliaWorldGuard\Flags\Types\BlockHandler;
use fenomeno\NepheliaWorldGuard\Flags\Types\ChatHandler;
use fenomeno\NepheliaWorldGuard\Flags\Types\DamageHandler;
use fenomeno\NepheliaWorldGuard\Flags\Types\EnvironmentHandler;
use fenomeno\NepheliaWorldGuard\Flags\Types\InteractionHandler;
use fenomeno\NepheliaWorldGuard\Flags\Types\ItemHandler;
use fenomeno\NepheliaWorldGuard\Flags\Types\MovementHandler;
use fenomeno\NepheliaWorldGuard\Flags\Types\PlayerStateHandler;
use fenomeno\NepheliaWorldGuard\Main;

class FlagRegistry
{
    /** @var self|null */
    private static ?FlagRegistry $instance = null;

    /** @var array<string, FlagHandlerInterface> */
    private array $handlers = [];

    /** @var array<string, string[]> */
    private array $eventMapping = [];

    private function __construct(
        private readonly Main $main
    ){}

    public static function getInstance(Main $main) : self{
        if(self::$instance === null){
            self::$instance = new self($main);
            self::$instance->registerDefaultHandlers();
        }
        return self::$instance;
    }

    private function registerDefaultHandlers(): void
    {
        $this->registerHandler(new BlockHandler($this->main));
        $this->registerHandler(new DamageHandler($this->main));
        $this->registerHandler(new MovementHandler($this->main));
        $this->registerHandler(new InteractionHandler($this->main));
        $this->registerHandler(new ChatHandler($this->main));
        $this->registerHandler(new ItemHandler($this->main));
        $this->registerHandler(new EnvironmentHandler($this->main));
        $this->registerHandler(new PlayerStateHandler($this->main));
    }

    public function registerHandler(FlagHandlerInterface $handler): void
    {
        $className = get_class($handler);
        $this->handlers[$className] = $handler;

        uasort($this->handlers, fn($a, $b) => $a::getPriority() <=> $b::getPriority());
    }

    public function getHandler(string $className): ?FlagHandlerInterface
    {
        return $this->handlers[$className] ?? null;
    }

    /**
     * @return FlagHandlerInterface[]
     */
    public function getHandlers(): array
    {
        return array_values($this->handlers);
    }

    public static function getDefaultFlags(): array
    {
        return Flags::toArray();
    }

}