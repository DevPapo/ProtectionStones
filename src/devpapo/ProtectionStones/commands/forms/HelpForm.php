<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use devpapo\ProtectionStones\Main;

class HelpForm {

    public static function create(Main $plugin, Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data) {
            // No action needed
        });
        
        $form->setTitle("§l§9Ayuda de ProtectionStones");
        $form->setContent(
            "§7Protege áreas colocando bloques especiales:\n\n" .
            "§5End Portal Frame §7- Protección 16x16\n" .
            "§fEnd Stone §7- Protección 32x32\n\n" .
            "§7Interactúa con el bloque para gestionar:\n" .
            "§f- Añadir/eliminar miembros\n" .
            "§f- Configurar permisos\n" .
            "§f- Ver información\n\n" .
            "§7Comandos:\n" .
            "§f/pstone §7- Menú principal\n" .
            "§f/psadmin §7- Herramientas de admin"
        );
        
        $player->sendForm($form);
    }
}