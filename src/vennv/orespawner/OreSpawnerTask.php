<?php

namespace vennv\orespawner;

use pocketmine\block\VanillaBlocks;
use pocketmine\scheduler\Task;

class OreSpawnerTask extends Task {

    private static array $delay = [];

    public function __construct() {}

    public function onRun() : void {
        $data = Provider::getData()->getAll();
        foreach($data as $key => $value) {
            $position = PositionUtil::toPosition($key);
            $block = $position->getWorld()->getBlock($position->asVector3());
            if (
                $block->getId() == 247 &&
                $position->getWorld()->getBlock($position->asVector3()->add(0, 1, 0))->getId() == 0 &&
                $position->getWorld()->isLoaded() &&
                $position->getWorld()->isChunkLoaded($position->getFloorX() >> 4, $position->getFloorZ() >> 4)
            ) {
                $delay = abs(2*($value["level"]-(20+1)));
                if (!isset(self::$delay[$key])) {
                    self::$delay[$key] = $delay;
                }
                if (--self::$delay[$key] <= 0) {
                    $block = VanillaBlocks::{strtoupper($value["ore"])}();
                    $position->getWorld()->setBlock($position->asVector3()->add(0, 1, 0), $block);
                    self::$delay[$key] = $delay;
                }
            } elseif(
		$block->getId() != 247 &&
                $position->getWorld()->isLoaded() &&
                $position->getWorld()->isChunkLoaded($position->getFloorX() >> 4, $position->getFloorZ() >> 4)
            ) {
            	$data->remove($key);
            	$data->save();
            }
        }
    }
}
