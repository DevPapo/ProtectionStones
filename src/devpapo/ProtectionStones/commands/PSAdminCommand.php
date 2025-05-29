<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use devpapo\ProtectionStones\Main;

class PSAdminCommand extends Command {

    private $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("psadmin", "Comandos de administración de ProtectionStones");
        $this->plugin = $plugin;
        $this->setPermission("pstone.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if(!$sender instanceof Player) {
            $sender->sendMessage("§cEste comando solo puede usarse en el juego.");
            return false;
        }
        
        if(!$this->testPermission($sender)) {
            return false;
        }
        
        if(empty($args)) {
            $sender->sendMessage("§6Uso: /psadmin <info|delete|reload>");
            return false;
        }
        
        switch(strtolower($args[0])) {
            case "reload":
                $this->plugin->reloadConfig();
                $sender->sendMessage("§aConfiguración recargada correctamente!");
                break;
            default:
                $sender->sendMessage("§cSubcomando no reconocido. Usa /psadmin info para ayuda");
        }
        
        return true;
    }
}