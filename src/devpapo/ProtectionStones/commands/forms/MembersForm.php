<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones\forms;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use devpapo\ProtectionStones\Main;

class MembersForm {

    public static function create(Main $plugin, Player $player, array $regionData): void {
        $form = new SimpleForm(function(Player $player, ?int $data) use ($plugin, $regionData) {
            if($data === null) return;
            
            switch($data) {
                case 0:
                    self::sendAddMemberForm($plugin, $player, $regionData);
                    break;
                case 1:
                    self::sendRemoveMemberForm($plugin, $player, $regionData);
                    break;
            }
        });
        
        $form->setTitle("§l§eGestión de Miembros");
        $form->setContent("§7Región: §f" . $regionData["region_id"]);
        
        $form->addButton("§l§aAñadir Miembro\n§r§7Agregar jugador");
        $form->addButton("§l§cEliminar Miembro\n§r§7Quitar jugador");
        
        $player->sendForm($form);
    }

    private static function sendAddMemberForm(Main $plugin, Player $player, array $regionData): void {
        $form = new CustomForm(function(Player $player, ?array $data) use ($plugin, $regionData) {
            if($data === null) return;
            
            $memberName = $data[0] ?? "";
            if(empty($memberName)) {
                $player->sendMessage("§cDebes especificar un nombre de jugador");
                return;
            }
            
            $plugin->getDatabase()->addMemberToRegion($regionData["region_id"], $memberName);
            $player->sendMessage(str_replace("%player", $memberName, $plugin->getCustomConfig()->get("messages")["member_added"]));
        });
        
        $form->setTitle("§l§aAñadir Miembro");
        $form->addInput("Nombre del jugador:", "Steve");
        $player->sendForm($form);
    }

    private static function sendRemoveMemberForm(Main $plugin, Player $player, array $regionData): void {
        $members = $plugin->getDatabase()->getRegionMembers($regionData["region_id"]);
        
        $form = new SimpleForm(function(Player $player, ?int $data) use ($plugin, $regionData, $members) {
            if($data === null) return;
            
            if(isset($members[$data])) {
                $plugin->getDatabase()->removeMemberFromRegion($regionData["region_id"], $members[$data]["player"]);
                $player->sendMessage(str_replace("%player", $members[$data]["player"], $plugin->getCustomConfig()->get("messages")["member_removed"]));
            }
        });
        
        $form->setTitle("§l§cEliminar Miembro");
        
        if(empty($members)) {
            $form->setContent("§7Esta región no tiene miembros.");
        } else {
            $form->setContent("§7Selecciona un miembro para eliminar:");
            foreach($members as $member) {
                $form->addButton("§l§f" . $member["player"] . "\n§r§7Click para eliminar");
            }
        }
        
        $player->sendForm($form);
    }
}