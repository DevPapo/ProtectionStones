<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones\forms;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\player\Player;
use devpapo\ProtectionStones\Main;

class FlagsForm {

    public static function create(Main $plugin, Player $player, array $regionData): void {
        $currentFlags = $plugin->getDatabase()->getRegionFlags($regionData["region_id"]);
        
        $form = new CustomForm(function(Player $player, ?array $data) use ($plugin, $regionData) {
            if($data === null) return;
            
            $flagsToUpdate = [
                "pvp" => (bool)$data[0],
                "mob-spawning" => (bool)$data[1],
                "use" => (bool)$data[2],
                "interact" => (bool)$data[3]
            ];
            
            foreach($flagsToUpdate as $flag => $value) {
                $plugin->getDatabase()->updateRegionFlag($regionData["region_id"], $flag, $value ? "allow" : "deny");
            }
            
            $player->sendMessage($plugin->getCustomConfig()->get("messages")["flag_updated"]);
        });
        
        $form->setTitle("§l§bConfiguración de Banderas");
        $form->addToggle("§7PvP Permitido", $currentFlags["pvp"] ?? true);
        $form->addToggle("§7Generación de Mobs", $currentFlags["mob-spawning"] ?? true);
        $form->addToggle("§7Usar Bloques", $currentFlags["use"] ?? true);
        $form->addToggle("§7Interactuar", $currentFlags["interact"] ?? true);
        
        $player->sendForm($form);
    }
}