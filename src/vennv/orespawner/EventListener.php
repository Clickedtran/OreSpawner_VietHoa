<?php

namespace vennv\orespawner;

use pocketmine\block\BlockFactory;

use pocketmine\block\BlockLegacyIds;

use pocketmine\event\Listener;

use pocketmine\event\block\BlockBreakEvent;

use pocketmine\event\block\BlockPlaceEvent;

class EventListener implements Listener {

    public function __construct() {}

    /**

     * @param BlockBreakEvent $event

     * 

     * @return void

     * 

     * remove ore spawner when player break it

     */

    public function onBreak(BlockBreakEvent $event) : void {

        $player = $event->getPlayer();

        $block = $event->getBlock();

        $position = $block->getPosition();

        $data = Provider::getData();

        if ($data->exists(PositionUtil::toString($position))) {

            $ev = new events\RemoveOreSpawnerEvent($player, $position);

            $ev->call();

            if ($ev->isCancelled()) return;

            $keyData = $data->get(PositionUtil::toString($position));

            if($keyData["owner"] !== $player->getName()) {

            	$player->sendMessage("§cBạn không sở hữu §bOreSpawner §ccnày để có thể phá vỡ!");            	$event->cancel();

            }

            $event->setDrops([]);

            $item = ItemSpawner::getItemSpawner($keyData["level"], 1, $keyData["ore"]);

            if($player->getInventory()->canAddItem($item)) {

            	$player->getInventory()->addItem($item);

            } else {

            	$player->sendPopup("§l§cInventory Full Vật Phẩm Đã Rớt Ra!");

            	$event->setDrops([$item]);

            }

            $data->remove(PositionUtil::toString($position));

            $data->save();

            $player->sendMessage("§aBạn đã loại bỏ thành công §bOre Spawner!");

            $event->cancel();

            $player->getWorld()->setBlock($position, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, 0));

        }

    }

    /**

     * @param BlockPlaceEvent $event

     * 

     * @return void

     * 

     * add ore spawner when player place it

     */

    public function onPlace(BlockPlaceEvent $event) : void {

        $player = $event->getPlayer();

        $block = $event->getBlock();

        $position = $block->getPosition();

        $key = PositionUtil::toString($position);

        $data = Provider::getData()->getAll();

        if (isset($data[$key])) {

            $player->sendMessage("§cBạn không thể đặt khối ở đây!");

            $event->cancel();

        }

        if ($player->getInventory()->getItemInHand()->getNamedTag()->getTag("OreSpawner") !== null) {

            $ev = new events\CreateOreSpawnerEvent($player, $position);

            $ev->call();

            if ($ev->isCancelled()) return;

            Provider::getData()->set($key, ["level" => (int) $player->getInventory()->getItemInHand()->getNamedTag()->getString("OreSpawner"), "ore" => $player->getInventory()->getItemInHand()->getNamedTag()->getString("OreSpawnerOre"), "owner" => $player->getName()]);

            Provider::getData()->save();

            $player->sendMessage("§aBạn đã đặt thành công §bOre Spawner!");

        }

    }

}
