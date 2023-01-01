<?php

namespace vennv\orespawner\events;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use pocketmine\player\Player;
use pocketmine\world\Position;

class RemoveOreSpawnerEvent extends Event implements Cancellable{

    private bool $isCancelled = false;

    public function __construct(
        private ?Player $player,
        private Position $position
    ){}

    public function getPlayer() : ?Player{
        return $this->player;
    }

    public function getPosition() : Position{
        return $this->position;
    }

    public function isCancelled() : bool{
        return $this->isCancelled;
    }

    public function cancel() : void{
        $this->isCancelled = true;
    }

    public function uncancel() : void{
        $this->isCancelled = false;
    }
}
