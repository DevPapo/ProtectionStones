<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use devpapo\ProtectionStones\Main;

class ProtectionStoneCommand extends Command {

    private $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("pstone", "Comando principal de ProtectionStones");
        $this->setAliases(["ps"]);
        $this->plugin = $plugin;
        $this->setPermission("pstone.use");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if(!$sender instanceof Player) {
            $sender->sendMessage("§cEste comando solo puede usarse en el juego.");
            return false;
        }
        
        if(!$this->testPermission($sender)) {
            return false;
        }
        
        $this->plugin->getFormsManager()->sendMainMenu($sender);
        return true;
    }
}