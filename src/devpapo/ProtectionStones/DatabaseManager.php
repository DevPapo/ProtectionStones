<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones;

use pocketmine\plugin\PluginBase;
use poggit\libasynql\libasynql;
use poggit\libasynql\Database;
use pocketmine\block\Block;
use pocketmine\world\World;
use pocketmine\utils\TextFormat;

class DatabaseManager {

    private $database;

    public function __construct(PluginBase $plugin) {
        $config = $plugin->getCustomConfig()->get("database", []);
        
        $this->database = libasynql::create($plugin, [
            "type" => $config["type"] ?? "sqlite",
            "sqlite" => [
                "file" => $config["file"] ?? "protection_stones.db"
            ],
            "worker-limit" => 1
        ], [
            "sqlite" => "sqlite.sql"
        ]);
        
        $this->database->executeGeneric("init.tables");
        $this->database->waitAll();
    }

    public function addProtectionStone(array $data): void {
        $this->database->executeInsert("add.ps", [
            "owner" => $data["owner"],
            "region_id" => $data["region_id"],
            "world" => $data["world"],
            "x" => $data["x"],
            "y" => $data["y"],
            "z" => $data["z"],
            "block_id" => $data["block_id"]
        ]);
    }

    public function getProtectionStoneByBlock(Block $block): ?array {
        $result = $this->database->executeSelect("get.ps.by.block", [
            "x" => $block->getPosition()->getX(),
            "y" => $block->getPosition()->getY(),
            "z" => $block->getPosition()->getZ(),
            "world" => $block->getPosition()->getWorld()->getFolderName()
        ]);
        
        return $result[0] ?? null;
    }

    public function getPlayerRegions(string $playerName): array {
        return $this->database->executeSelect("get.player.regions", ["owner" => $playerName]);
    }

    public function getRegionById(string $regionId): ?array {
        $result = $this->database->executeSelect("get.ps.by.id", ["region_id" => $regionId]);
        return $result[0] ?? null;
    }

    public function getRegionMembers(string $regionId): array {
        return $this->database->executeSelect("get.region.members", ["region_id" => $regionId]);
    }

    public function addMemberToRegion(string $regionId, string $playerName): void {
        $this->database->executeInsert("add.member", [
            "region_id" => $regionId,
            "player" => $playerName
        ]);
    }

    public function removeMemberFromRegion(string $regionId, string $playerName): void {
        $this->database->executeGeneric("remove.member", [
            "region_id" => $regionId,
            "player" => $playerName
        ]);
    }

    public function isRegionMember(string $regionId, string $playerName): bool {
        $result = $this->database->executeSelect("check.member", [
            "region_id" => $regionId,
            "player" => $playerName
        ]);
        return !empty($result);
    }

    public function getRegionFlags(string $regionId): array {
        $flags = [];
        $result = $this->database->executeSelect("get.region.flags", ["region_id" => $regionId]);
        
        foreach($result as $row) {
            $flags[$row["flag"]] = $row["value"];
        }
        
        // Flags por defecto
        return array_merge([
            "pvp" => "allow",
            "mob-spawning" => "allow",
            "use" => "allow",
            "interact" => "allow",
            "build" => "deny"
        ], $flags);
    }

    public function updateRegionFlag(string $regionId, string $flag, string $value): void {
        $this->database->executeGeneric("update.flag", [
            "region_id" => $regionId,
            "flag" => $flag,
            "value" => $value
        ]);
    }

    public function removeProtectionStone(int $id): void {
        $this->database->executeGeneric("remove.ps", ["id" => $id]);
    }

    public function removeAllPlayerRegions(string $playerName): void {
        $this->database->executeGeneric("remove.player.regions", ["owner" => $playerName]);
    }

    public function close(): void {
        $this->database->close();
    }

    public function getDatabaseStats(): array {
        $stats = [];
        
        $stats['total_regions'] = $this->database->executeSelect("count.regions")[0]['count'] ?? 0;
        $stats['total_members'] = $this->database->executeSelect("count.members")[0]['count'] ?? 0;
        
        return $stats;
    }
}