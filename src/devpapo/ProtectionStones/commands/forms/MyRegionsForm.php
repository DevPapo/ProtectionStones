<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use devpapo\ProtectionStones\Main;
use devpapo\ProtectionStones\DatabaseManager;

class MyRegionsForm {

    public static function create(Main $plugin, Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data) use ($plugin) {
            if($data === null) return;
            
            $regions = $plugin->getDatabase()->getPlayerRegions($player->getName());
            if(isset($regions[$data])) {
                RegionForm::create($plugin, $player, $regions[$data]);
            }
        });
        
        $form->setTitle("§l§eMis Regiones Protegidas");
        
        $regions = $plugin->getDatabase()->getPlayerRegions($player->getName());
        if(empty($regions)) {
            $form->setContent("§cNo tienes ninguna región protegida.");
            $form->addButton("§l§cVolver");
        } else {
            foreach($regions as $region) {
                $form->addButton("§l§a" . $region["region_id"] . "\n§r§7" . $region["world"] . " (" . $region["x"] . "," . $region["y"] . "," . $region["z"] . ")");
            }
        }
        
        $player->sendForm($form);
    }
}