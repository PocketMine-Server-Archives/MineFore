<?php

namespace DaPigGuy\PiggyCustomEnchants\Tasks;

use DaPigGuy\PiggyCustomEnchants\Main;
use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;

/**
 * Class HallucinationTask
 * @package DaPigGuy\PiggyCustomEnchants
 */
class HallucinationTask extends Task
{
    /** @var Main */
    private $plugin;
    /** @var Player */
    private $player;
    /** @var Position */
    private $position;
    /** @var int */
    private $time = 0;

    /**
     * HallucinationTask constructor.
     * @param Main     $plugin
     * @param Player   $player
     * @param Position $position
     */
    public function __construct(Main $plugin, Player $player, Position $position)
    {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->position = $position;
    }

    /**
     * @param $currentTick
     */
    public function onRun(int $currentTick)
    {
        $position = $this->position;
        $this->time++;
        for ($x = $position->x - 1; $x <= $position->x + 1; $x++) {
            for ($y = $position->y - 1; $y <= $position->y + 2; $y++) {
                for ($z = $position->z - 1; $z <= $position->z + 1; $z++) {
                    $pos = new Position($x, $y, $z, $position->getLevel());
                    if ($this->time >= 20 * 30) {
                        $position->getLevel()->sendBlocks([$this->player], [$position->getLevel()->getBlock($pos)]);
                    } else {
                        if ($pos->equals($position) !== true) {
                            if ($pos->equals($position->add(0, 1)) !== true) {
                                $block = Block::get(Block::BEDROCK);
                            } else {
                                $block = Block::get(Block::WALL_SIGN);
                                $nbtWriter = new NetworkLittleEndianNBTStream();
                                $nbt = $nbtWriter->write(new CompoundTag("", [
                                    new StringTag("id", Tile::SIGN),
                                    new StringTag("Text1", TextFormat::RED . "§lBạn sắp bị"),
                                    new StringTag("Text2", TextFormat::RED . "§lnhốt..."),
                                    new StringTag("Text3", ""),
                                    new StringTag("Text4", ""),
                                    new IntTag("x", $pos->x),
                                    new IntTag("y", $pos->y),
                                    new IntTag("z", $pos->z)
                                ]));
                                $pk = new BlockActorDataPacket();
                                $pk->x = (int)$pos->x;
                                $pk->y = (int)$pos->y;
                                $pk->z = (int)$pos->z;
                                $pk->namedtag = $nbt;
                                $this->player->sendDataPacket($pk);
                            }
                        } else {
                            $block = Block::get(Block::LAVA);
                        }
                        $block->setComponents((int)$pos->x, (int)$pos->y, (int)$pos->z);
                        $position->getLevel()->sendBlocks([$this->player], [$block]);
                    }
                }
            }
        }
        if ($this->time >= 20 * 30) {
            unset($this->plugin->hallucination[$this->player->getLowerCaseName()]);
            $this->plugin->getScheduler()->cancelTask($this->getHandler()->getTaskId());
        }
    }
}
