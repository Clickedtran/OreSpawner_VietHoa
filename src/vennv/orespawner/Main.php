<?php

namespace vennv\orespawner;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\player\Player;

# <-- Form Header -->
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\MenuOption;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
# <-- Form Footer -->

class Main extends PluginBase implements Listener {

    private static ?Main $instance = null;

    /**
     * @return Main
     */
    public static function getInstance() : Main {
        return self::$instance;
    }
    
    public static function getSpawnerOres() :array {
    	return ["stone", "cobblestone", "coal", "coal_ore", "iron", "iron_ore", "gold", "gold_ore", "diamond", "diamond_ore", "emerald", "emerald_ore"];
    }

    public function onEnable() : void {
        self::$instance = $this;
        Provider::init();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getScheduler()->scheduleRepeatingTask(new OreSpawnerTask(), 1);
        $this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
    }
    
    public function getUpgradeForm(Player $player) : MenuForm{
    	$level = ItemSpawner::getLevelSpawner($player);
    	$type = ItemSpawner::getTypeSpawner($player);
    	$currentHoldingItem = $player->getInventory()->getItemInHand();
    	$upgradeCostPerLevel = 5000;
    	if($level === null && $type === null) {
    		$message = "§aBạn phải cầm trên tay một §bOreSpawner §ađể nâng cấp";
    		$options = [];
    	} else {
    		$cost = $upgradeCostPerLevel * ($level + 1);
    		$message = "§bOreSpawner§a của bạn đang là cấp độ $level, Bạn có muốn nâng lên cấp độ: " . $level + 1 . " với giá \$$cost?\nSố tiền của bạn: " . $this->eco->myMoney($player) . "\n§cCảnh báo: Bạn cần để lại 1 khoảng trống trong inventory của mình!§r";
    		$options = [
    				new MenuOption("Có, tôi đồng ý nâng cấp"),
    				new MenuOption("Không và Thoát")
    			];
    	}
    	if($level >= 10) {
    		$message = "§aBạn hiện đang giữ một §bOreSpawner §avới cấp độ §ctối đa";
    		$options = [];
    	}
    	$cost = isset($cost) ? $cost : 0;
    	return new MenuForm(
			"Upgrade OreSpawner",
			$message,
			$options,
			function(Player $player, $data) use ($cost, $currentHoldingItem, $level, $type) : void{
				switch($data) {
					case 0:
						if($this->eco->myMoney($player) >= $cost) {
							$this->eco->reduceMoney($player, $cost);
							$item = ItemSpawner::getItemSpawner($level + 1, 1, $type);
							$player->getInventory()->setItemInHand($currentHoldingItem->setCount($currentHoldingItem->getCount() - 1));
							$player->getInventory()->addItem($item);
							$player->sendForm($this->getUpgradeForm($player));
							$player->sendMessage("§aTuyệt, §bOreSpawner §acủa bạn đã được nâng cấp lên cấp " . $level + 1);
						} else {
							$player->sendMessage("§cBạn không đủ \$$cost Money để nâng cấp!");
						}
					break;
					case 1:
						$player->sendForm($this->getMainForm($player));
					break;
					default:
					break;
				}
			},
			function(Player $player) : void{
				$player->sendForm($this->getMainForm($player));
			}
		);
    }
    
    public function getBuyForm(Player $player) : CustomForm{
    	return new CustomForm(
			"Buy OreSpawner",
			[
				new Label("label", "$10000/1 OreSpawner\nSố tiền bạn đang có: " . $this->eco->myMoney($player)),
				new Input("count", "Số lượng OreSpawner muốn mua ?", "Ví Dụ: 1,...", "1"),
				new Input("type", "Nhập loại quặng bạn muốn mua, nếu là quặng thì hãy ghi §cgold_ore, .....", "Ví dụ: stone,...")
			],
			function(Player $player, CustomFormResponse $data) : void{
				$data = $data->getAll();
				$count = $data["count"] == "" ? 1 : ((int) $data["count"] < 0 ? 1 : (int) $data["count"]);
				$cost = 10000;
				$cost = $count * $cost;
				$type = $data["type"] == "" ? "stone" : $data["type"];
				if($this->eco->myMoney($player) >= $cost) {
					$item = ItemSpawner::getItemSpawner(1, $count, $type);
					if($player->getInventory()->canAddItem($item)) {
						$this->eco->reduceMoney($player, $cost);
						$player->getInventory()->addItem($item);
						$player->sendMessage("§aBạn đã mua $type Spawner level 1 với số lượng §cx$count!");
					} else {
						$player->sendMessage("§cTúi đồ của bạn đã đầy, đã hủy mua hàng!");
					}
				} else {
					$player->sendMessage("§cBạn không có đủ \$$cost money để mua $count ore spawner!");
				}
			},
			function(Player $player) : void{
				$player->sendForm($this->getMainForm($player));
			}
		);
    }
    
    public function getGiveForm(Player $player) : CustomForm{
    	return new CustomForm(
			"Give OreSpawner",
			[
				new Label("label", "Chỗ này để nhận OreSpawner giành cho OP!"),
				new Input("name", "Nhập tên người chơi mà bạn muốn tặng thêm OreSpawner", "Ví Dụ: ClickedTran,..."),
				new Input("level", "Nhập cấp độ của OreSpawner", "Ví dụ: 1,...", "1"),
				new Input("count", "Nhập số lượng OreSpawner mà bạn muốn tặng", "Ví dụ: 1,..", "1"),
				new Input("type", "Nhập loại quặng được tạo ra bởi OreSapwner", "Ví Dụ: stone, gold_ore...")
			],
			function(Player $player, CustomFormResponse $data) : void{
				$data = $data->getAll();
				$name = $data["name"] == "" ? $player->getName() : $data["name"];
				$level = $data["level"] == "" ? 1 : ((int) $data["level"] < 0 ? 1 : (int) $data["level"]);
				$count = $data["count"] == "" ? 1 : ((int) $data["count"] < 0 ? 1 : (int) $data["count"]);
				$type = $data["type"] == "" ? "stone" : $data["type"];
				$this->getServer()->dispatchCommand(new ConsoleCommandSender($this->getServer(), $this->getServer()->getLanguage()), "os give $name $level $count $type");
			},
			function(Player $player) : void{
				$player->sendForm($this->getMainForm($player));
			}
		);
    }
    
    public function getMainForm(Player $player) : MenuForm{
    	$options = [
    			new MenuOption("§aMua §bOreSpawner"),
    			new MenuOption("§cNâng Cấp §bOreSpawner")
    		];
    	if($this->getServer()->isOp($player->getName())) {
    		$options = array_merge($options, [new MenuOption("§aTrao Tặng§b OreSpawner")]);
   	 }
    	return new MenuForm(
			"OreSpawner Menu",
			"",
			$options,
			function(Player $player, $data) : void{
				switch($data) {
					case 0:
						$player->sendForm($this->getBuyForm($player));
					break;
					case 1:
						$player->sendForm($this->getUpgradeForm($player));
					break;
					case 2:
						$player->sendForm($this->getGiveForm($player));
					break;
					default:
					break;
				}
			},
			function(Player $player) : void{}
		);
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * 
     * @return bool
     * 
     * use this method to handle the command
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        if (in_array($command->getName(), ["orespawner", "osp", "os", "oregenerator", "generator", "oregen"])) {
            if (isset($args[0])) {
                if ($args[0] === "give") {
                    if (!$sender->hasPermission('orespawner.command.give')) return true;
                    if (isset($args[1]) && isset($args[2]) && isset($args[3]) && isset($args[4])) {
                    	if(!in_array(strtolower($args[4]), $this->getSpawnerOres())) {
                    		$sender->sendMessage("§cUndefined ore type {$args[4]} (Available type: " . implode(", ", self::getSpawnerOres()) . ")");
                    		return false;
                   	 }
                   	 if(($player = $this->getServer()->getPlayerByPrefix($args[1])) === null) {
                   	 	$sender->sendMessage("§c{$args[1]} đang không online!");
                   		 return false;
                  	  }
                        $player->getInventory()->addItem(ItemSpawner::getItemSpawner((int) $args[2] > 10 ? 10 : (int) $args[2], (int) $args[3] < 1 ? 1 : (int) $args[3], $args[4]));
                        $player->sendMessage("§aBạn đã được tặng (x{$args[3]}) " . strtolower($args[4]) . " OreSpawner với cấp độ {$args[2]}");
                        $sender->sendMessage("§aBạn đã tặng thành công OreSpawner!");
                    } else {
                        $sender->sendMessage("§clệnh không hợp lệ! Vui lòng sử dụng§b /orespawner help§c để xem lệnh!");
                    }
                } else {
                	foreach([
                		"§aOreSpawner help:",
                		"§a/orespawner help - show help",
                		"§a/orespawner give <name> <level> <count> <type> - give ore spawner"
            		] as $message) {
                    	$sender->sendMessage($message);
                	}
                }
            } else {
                if (!$sender instanceof Player){
                    $sender->sendMessage("§r§cVui lòng sử dụng lệnh trong game!");
                    return true;
                }
                $sender->sendForm($this->getMainForm($sender));
            }
        }
        return true;
    }
}
