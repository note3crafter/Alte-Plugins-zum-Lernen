<?php

namespace EnderDirt;

//Base
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
//Utils
use pocketmine\utils\TextFormat as Color;
use pocketmine\utils\Config;
//EventListener
use pocketmine\event\Listener;
//PlayerEvents
use pocketmine\Player;
use pocketmine\event\player\PlayerHungerChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
//ItemUndBlock
use pocketmine\block\Block;
use pocketmine\item\Item;
//BlockEvents
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
//EntityEvents
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\Effect;
//Level
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
//Sounds
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\sound\GhastShootSound;
//Commands
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
//Tile
use pocketmine\tile\Sign;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
//Nbt
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
//Inventar
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\Inventory;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;

class AntiCheat extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::DARK_PURPLE . "eAntiCheat" . Color::WHITE . "] ";
	
	public function onEnable() {
		
		$this->getLogger()->info($this->prefix . Color::GREEN . "lade das Plugin!");
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CheckHack($this), 20);
		
		$this->getLogger()->info($this->prefix . Color::GREEN . "das AntiCheat Plugin wurde erfolgreich geladen!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::DARK_PURPLE . " EnderDirt!");
		
    }
    
    public function onPlayerGameModeChange(PlayerGameModeChangeEvent $event) {
    	
    	$player = $event->getPlayer();
        if ($player->isOp()) {
        	
        } else {
        	
        	$cloudplayer = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
            $cloudplayer->set("Ban", true);
            $cloudplayer->set("BanGrund", "Force OP");
            $cloudplayer->set("BanTime", 7);
            $cloudplayer->save();
            $player->kick(
            Color::RED . "Du wurdest Gebannt\n" .
            Color::GRAY . "Grund: " . Color::YELLOW . $cloudplayer->get("BanGrund") . "\n" .
            Color::GRAY . "Dauer: " . Color::YELLOW . $cloudplayer->get("BanTime") . " Tag/e\n", false
             );
        	
        }
    	
    }
	
}

class CheckHack extends PluginTask {
	
	public function __construct($plugin)
    {

        $this->plugin = $plugin;
        parent::__construct($plugin);

    }

    public function onRun($tick)
    {
    	
    	$all = $this->plugin->getServer()->getOnlinePlayers();
        foreach ($all as $player) {
        	
        	$cloudplayer = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
            $x = $player->getX();
            $y = $player->getY();
            $z = $player->getZ();
            if ($cloudplayer->get("Y")+5 >= $y) {
            	
            	$cloudplayer->set("Ban", true);
                $cloudplayer->set("BanGrund", "Tower/Fly Hack");
                $cloudplayer->set("BanTime", "Permanent");
                $cloudplayer->save();
                $player->kick(
                Color::RED . "Du wurdest Gebannt\n" .
                Color::GRAY . "Grund: " . Color::YELLOW . $cloudplayer->get("BanGrund") . "\n" .
                Color::GRAY . "Dauer: " . Color::YELLOW . $cloudplayer->get("BanTime") . " Tag/e\n", false
                );
            	
            } else {
            	
            	$cloudplayer->set("Y", $y);
                $cloudplayer->save();
            	
            }
        	
        }
    	
    }
	
}