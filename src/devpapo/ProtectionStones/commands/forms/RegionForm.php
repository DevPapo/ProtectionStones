<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use devpapo\ProtectionStones\Main;

class RegionForm {

    public static function create(Main $plugin, Player $player, array $regionData): void {
        $form = new SimpleForm(function(Player $player, ?int $data) use ($plugin, $regionData) {
            if($data === null) return;
            
            switch($data) {
                case 0:
                    MembersForm::create($plugin, $player, $regionData);
                    break;
                case 1:
                    FlagsForm::create($plugin, $player, $regionData);
                    break;
                case 2:
                    // Info
                    break;
                case 3:
                    // Eliminar
                    break;
            }
        });
        
        $form->setTitle("§l§aGestión de Región");
        $form->setContent("§7Región: §f" . $regionData["region_id"] . "\n§7Mundo: §f" . $regionData["world"] . "\n§7Ubicación: §f" . $regionData["x"] . ", " . $regionData["y"] . ", " . $regionData["z"]);
        
        $form->addButton("§l§eMiembros\n§r§7Administrar jugadores");
        $form->addButton("§l§bConfiguración\n§r§7Ajustar banderas");
        $form->addButton("§l§aInformación\n§r§7Ver detalles");
        $form->addButton("§l§cEliminar Región\n§r§7Eliminar protección");
        
        $player->sendForm($form);
    }
}