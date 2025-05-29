<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use devpapo\ProtectionStones\Main;

class MainForm {

    public static function create(Main $plugin, Player $player): SimpleForm {
        $form = new SimpleForm(function(Player $player, ?int $data) use ($plugin) {
            if($data === null) return;
            
            switch($data) {
                case 0:
                    $player->sendMessage("§aColoca un bloque de protección para crear una región");
                    break;
                case 1:
                    MyRegionsForm::create($plugin, $player);
                    break;
                case 2:
                    HelpForm::create($plugin, $player);
                    break;
            }
        });
        
        $form->setTitle("§l§5Protection§fStones");
        $form->setContent("§7Selecciona una opción:");
        $form->addButton("§l§aCrear Región\n§r§7Colocar bloque de protección");
        $form->addButton("§l§eMis Regiones\n§r§7Ver/Gestionar mis regiones");
        $form->addButton("§l§9Ayuda\n§r§7Información del plugin");
        
        return $form;
    }
}