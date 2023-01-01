<?php

namespace vennv\orespawner;

use pocketmine\utils\Config;

final class Provider {

    private static ?Config $data = null;

    /**
     * @return void
     * 
     * load data.yml
     */
    public static function init() : void {
        self::getData();
    }

    /**
     * @return Config
     * 
     * get data
     */
    public static function getData() : Config {
        if (self::$data === null) {
            self::$data = new Config(Main::getInstance()->getDataFolder() . "data.yml", Config::YAML);
        }
        return self::$data;
    }
}
