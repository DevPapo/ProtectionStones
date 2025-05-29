<?php
declare(strict_types=1);

namespace devpapo\ProtectionStones;

use pocketmine\block\Block;
use pocketmine\world\Position;

class ProtectionStone {

    private $id;
    private $owner;
    private $regionId;
    private $world;
    private $position;
    private $blockId;
    private $createdAt;

    public function __construct(
        int $id,
        string $owner,
        string $regionId,
        string $world,
        Position $position,
        int $blockId,
        string $createdAt
    ) {
        $this->id = $id;
        $this->owner = $owner;
        $this->regionId = $regionId;
        $this->world = $world;
        $this->position = $position;
        $this->blockId = $blockId;
        $this->createdAt = $createdAt;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getOwner(): string {
        return $this->owner;
    }

    public function getRegionId(): string {
        return $this->regionId;
    }

    public function getWorld(): string {
        return $this->world;
    }

    public function getPosition(): Position {
        return $this->position;
    }

    public function getBlockId(): int {
        return $this->blockId;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function getProtectionSize(): int {
        $plugin = ProtectionStones::getInstance();
        foreach($plugin->getCustomConfig()->get("protection_blocks", []) as $stone) {
            if($this->blockId === Block::get($stone["block_id"])->getId()) {
                return $stone["protection_size"];
            }
        }
        return 16;
    }
}