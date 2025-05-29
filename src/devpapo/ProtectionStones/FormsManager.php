<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones;

use pocketmine\player\Player;
use pocketmine\block\Block;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use devpapo\ProtectionStones\forms\MainForm;
use devpapo\ProtectionStones\forms\RegionForm;
use devpapo\ProtectionStones\forms\MembersForm;
use devpapo\ProtectionStones\forms\FlagsForm;

class FormsManager {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function sendMainMenu(Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data) {
            if($data === null) return;
            
            switch($data) {
                case 0: // Crear región
                    $player->sendMessage("Coloca un bloque de protección para crear una región");
                    break;
                case 1: // Mis regiones
                    $this->sendMyRegionsForm($player);
                    break;
                case 2: // Ayuda
                    $this->sendHelpForm($player);
                    break;
            }
        });
        
        $form->setTitle("§l§5Protection§fStones");
        $form->setContent("Selecciona una opción:");
        $form->addButton("§l§aCrear Región\n§r§7Colocar bloque de protección");
        $form->addButton("§l§eMis Regiones\n§r§7Ver/Gestionar mis regiones");
        $form->addButton("§l§9Ayuda\n§r§7Información del plugin");
        $player->sendForm($form);
    }

    public function sendCreateRegionForm(Player $player, Block $block): void {
        $form = new CustomForm(function(Player $player, ?array $data) use ($block) {
            if($data === null) return;
            
            $regionName = $data[0] ?? "ps_" . uniqid();
            $size = $this->getBlockProtectionSize($block);
            
            if($this->plugin->getWorldGuard() !== null) {
                $world = $block->getPosition()->getWorld();
                $pos1 = $block->getPosition()->add(-$size, -5, -$size);
                $pos2 = $block->getPosition()->add($size, 256, $size);
                
                $region = new \pocketmine\world\Position[]($pos1, $pos2);
                $this->plugin->getWorldGuard()->getRegionManager($world)->addRegion($regionName, $region);
            }
            
            $this->plugin->getDatabase()->addProtectionStone([
                "owner" => $player->getName(),
                "region_id" => $regionName,
                "world" => $block->getPosition()->getWorld()->getFolderName(),
                "x" => $block->getPosition()->getX(),
                "y" => $block->getPosition()->getY(),
                "z" => $block->getPosition()->getZ(),
                "block_id" => $block->getId()
            ]);
            
            $player->sendMessage($this->plugin->getCustomConfig()->get("messages")["region_created"]);
        });
        
        $form->setTitle("§l§5Crear Región Protegida");
        $form->addInput("Nombre de la región (opcional):", "ps_region123");
        $player->sendForm($form);
    }

    private function getBlockProtectionSize(Block $block): int {
        foreach($this->plugin->getCustomConfig()->get("protection_blocks", []) as $stone) {
            if($block->getId() === Block::get($stone["block_id"])->getId()) {
                return $stone["protection_size"];
            }
        }
        return 16;
    }

    // ... otros métodos de forms
}