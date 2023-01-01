<?php

namespace vennv\orespawner;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;

final class ItemSpawner {

    /**
     * @param string $level
     * @param int $count
     * 
     * @return Item
     * 
     * get item spawner
     */
    public static function getItemSpawner(string $level, int $count, string $type) : Item {
        $item = (new ItemFactory())->get(247, 0, $count);
        $item->setCustomName("§r§l§bOre Spawner §e(§r§a" . strtoupper($type) . "§l§e)");
        $item->setLore([
            "§r§7Level: §r§b" . $level
        ]);
        $item->getNamedTag()->setString("OreSpawner", $level);
        $item->getNamedTag()->setString("OreSpawnerOre", $type);
        return $item;
    }

    /**
     * @param Player $player
     * 
     * @return string
     *
     * get level of spawner
     */
    public static function getLevelSpawner(Player $player) : string|null {
        if ($player->getInventory()->getItemInHand()->getNamedTag()->getTag("OreSpawner") !== null) {
            return $player->getInventory()->getItemInHand()->getNamedTag()->getString("OreSpawner");
        }
        return null;
    }
    
    /**
     * @param Player $player
     * 
     * @return string
     *
     * get type of spawner
     */
    public static function getTypeSpawner(Player $player) : string|null {
        if ($player->getInventory()->getItemInHand()->getNamedTag()->getTag("OreSpawnerOre") !== null) {
            return $player->getInventory()->getItemInHand()->getNamedTag()->getString("OreSpawnerOre");
        }
        return null;
    }
}
