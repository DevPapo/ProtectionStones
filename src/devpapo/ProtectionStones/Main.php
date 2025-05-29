<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\block\Block;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use devpapo\ProtectionStones\commands\ProtectionStoneCommand;
use devpapo\ProtectionStones\commands\PSAdminCommand;

class Main extends PluginBase implements Listener {

    private $database;
    private $formsManager;
    private $worldGuard;
    private $config;

    public function onEnable(): void {
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        
        $this->database = new DatabaseManager($this);
        $this->formsManager = new FormsManager($this);
        
        $this->worldGuard = $this->getServer()->getPluginManager()->getPlugin("WorldGuard");
        if($this->worldGuard === null) {
            $this->getLogger()->error("WorldGuard no está instalado. El plugin no funcionará correctamente.");
        }
        
        $this->getServer()->getCommandMap()->register("pstone", new ProtectionStoneCommand($this));
        $this->getServer()->getCommandMap()->register("psadmin", new PSAdminCommand($this));
        
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("ProtectionStones activado!");
    }

    public function onDisable(): void {
        $this->database->close();
    }

    public function getDatabase(): DatabaseManager {
        return $this->database;
    }

    public function getFormsManager(): FormsManager {
        return $this->formsManager;
    }

    public function getWorldGuard() {
        return $this->worldGuard;
    }

    public function getCustomConfig(): Config {
        return $this->config;
    }

    public function onPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $config = $this->getCustomConfig()->get("protection_blocks", []);
        
        foreach($config as $stone) {
            if($block->getId() === Block::get($stone["block_id"])->getId()) {
                if($this->database->getProtectionStoneByBlock($block) !== null) {
                    $player->sendMessage($this->getCustomConfig()->get("messages")["region_already_exists"]);
                    $event->cancel();
                    return;
                }
                $this->formsManager->sendCreateRegionForm($player, $block);
                break;
            }
        }
    }

    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $psData = $this->database->getProtectionStoneByBlock($block);
        
        if($psData !== null) {
            if($psData["owner"] !== $player->getName() && !$player->hasPermission("pstone.admin")) {
                $player->sendMessage($this->getCustomConfig()->get("messages")["not_region_owner"]);
                $event->cancel();
                return;
            }
            
            if($this->worldGuard !== null) {
                $this->worldGuard->getRegionManager($block->getPosition()->getWorld())->removeRegion($psData["region_id"]);
            }
            
            $this->database->removeProtectionStone($psData["id"]);
            $player->sendMessage($this->getCustomConfig()->get("messages")["region_removed"]);
        }
    }

    public function onInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $psData = $this->database->getProtectionStoneByBlock($block);
        
        if($psData !== null) {
            $event->cancel();
            if($psData["owner"] === $player->getName() || $player->hasPermission("pstone.admin")) {
                $this->formsManager->sendRegionManagementForm($player, $psData);
            } else {
                $player->sendMessage($this->getCustomConfig()->get("messages")["not_region_owner"]);
            }
        }
    }
}