<?php

namespace vennv\orespawner;

use pocketmine\world\Position;

final class PositionUtil {

    public static function toString(Position $position) : string {
        return $position->getWorld()->getFolderName() . ":" . $position->getX() . ":" . $position->getY() . ":" . $position->getZ();
    }

    public static function toPosition(string $string) : Position {
        $array = explode(":", $string);
        return new Position((int) $array[1], (int) $array[2], (int) $array[3], Main::getInstance()->getServer()->getWorldManager()->getWorldByName($array[0]));
    }
}
