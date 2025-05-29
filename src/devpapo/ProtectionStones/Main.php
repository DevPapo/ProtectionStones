<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\block\Block;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    private $database;
    private $formsManager;
    private $config;
    private $protections = [];

    public function onEnable(): void {
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        
        $this->database = new DatabaseManager($this);
        $this->formsManager = new FormsManager($this);
        $this->loadAllProtections();
        
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("ProtectionStones activado!");
    }

    private function loadAllProtections(): void {
        $this->protections = [];
        $all = $this->database->getAllProtections();
        foreach($all as $protection) {
            $this->cacheProtection($protection);
        }
    }

    private function cacheProtection(array $protection): void {
        $world = $this->getServer()->getWorldManager()->getWorldByName($protection['world']);
        if($world !== null) {
            $this->protections[$protection['region_id']] = $protection;
        }
    }

    public function isInProtectedArea(Player $player, Position $pos): bool {
        foreach($this->protections as $protection) {
            if($protection['world'] === $pos->getWorld()->getFolderName()) {
                $center = new Position($protection['x'], $protection['y'], $protection['z'], $pos->getWorld());
                $size = $this->getProtectionSize($protection['block_id']);
                
                if($this->isPositionInArea($pos, $center, $size)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function isPositionInArea(Position $pos, Position $center, int $size): bool {
        return abs($pos->getX() - $center->getX()) <= $size &&
               abs($pos->getZ() - $center->getZ()) <= $size &&
               $pos->getY() >= ($center->getY() - 5) &&
               $pos->getY() <= ($center->getY() + 5);
    }

    private function getProtectionSize(int $blockId): int {
        foreach($this->config->get("protection_blocks", []) as $stone) {
            if(Block::get($stone["block_id"])->getId() === $blockId) {
                return $stone["protection_size"];
            }
        }
        return 16;
    }

    // Evento para proteger contra construcción
    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        
        foreach($this->protections as $protection) {
            if($protection['world'] === $block->getPosition()->getWorld()->getFolderName()) {
                $center = new Position($protection['x'], $protection['y'], $protection['z'], $block->getPosition()->getWorld());
                $size = $this->getProtectionSize($protection['block_id']);
                
                if($this->isPositionInArea($block->getPosition(), $center, $size)) {
                    if($protection['owner'] !== $player->getName() && !$player->hasPermission("pstone.admin")) {
                        $event->cancel();
                        $player->sendMessage($this->config->get("messages")["not_region_owner"]);
                        return;
                    }
                }
            }
        }
    }

    // Evento para proteger contra rotura de bloques
    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        
        // Verificar si es un bloque de protección
        $psData = $this->database->getProtectionStoneByBlock($block);
        if($psData !== null) {
            if($psData["owner"] !== $player->getName() && !$player->hasPermission("pstone.admin")) {
                $player->sendMessage($this->config->get("messages")["not_region_owner"]);
                $event->cancel();
                return;
            }
            
            // Eliminar protección
            unset($this->protections[$psData["region_id"]]);
            $this->database->removeProtectionStone($psData["id"]);
            $player->sendMessage($this->config->get("messages")["region_removed"]);
            return;
        }
        
        // Verificar si está en área protegida
        foreach($this->protections as $protection) {
            if($protection['world'] === $block->getPosition()->getWorld()->getFolderName()) {
                $center = new Position($protection['x'], $protection['y'], $protection['z'], $block->getPosition()->getWorld());
                $size = $this->getProtectionSize($protection['block_id']);
                
                if($this->isPositionInArea($block->getPosition(), $center, $size)) {
                    if($protection['owner'] !== $player->getName() && 
                       !$this->database->isRegionMember($protection['region_id'], $player->getName()) &&
                       !$player->hasPermission("pstone.admin")) {
                        $event->cancel();
                        $player->sendMessage($this->config->get("messages")["not_region_owner"]);
                        return;
                    }
                }
            }
        }
    }

    // Proteger contra PvP
    public function onEntityDamage(EntityDamageByEntityEvent $event): void {
        $victim = $event->getEntity();
        $attacker = $event->getDamager();
        
        if($victim instanceof Player && $attacker instanceof Player) {
            foreach($this->protections as $protection) {
                if($protection['world'] === $victim->getPosition()->getWorld()->getFolderName()) {
                    $center = new Position($protection['x'], $protection['y'], $protection['z'], $victim->getPosition()->getWorld());
                    $size = $this->getProtectionSize($protection['block_id']);
                    
                    if($this->isPositionInArea($victim->getPosition(), $center, $size) ||
                       $this->isPositionInArea($attacker->getPosition(), $center, $size)) {
                        $flags = $this->database->getRegionFlags($protection['region_id']);
                        if($flags['pvp'] === 'deny') {
                            $event->cancel();
                            $attacker->sendMessage("§cNo puedes atacar en áreas protegidas!");
                            return;
                        }
                    }
                }
            }
        }
    }
}