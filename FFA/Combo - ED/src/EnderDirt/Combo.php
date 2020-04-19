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
//ItemUndBlock
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
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
use pocketmine\level\sound\GhastSound;
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

class Combo extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::YELLOW . "Combo" . Color::WHITE . "] ";
	
	public function onEnable() {
		
		if (is_dir($this->getDataFolder()) !== true) {
        	
            mkdir($this->getDataFolder());
            
        }
        
        $this->saveDefaultConfig();
        $this->reloadConfig();

        $config = $this->getConfig();
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new PlayerSender($this), 10);
        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::GREEN . " EnderDirt!");
		
    }
    
    public function onJoin(PlayerJoinEvent $event) {
    	
    	$player = $event->getPlayer();
        $config = $this->getConfig();
        $event->setJoinMessage(Color::GRAY . "> " . Color::DARK_GRAY . "> " . $player->getDisplayName() . Color::GRAY . " hat den Server Betreten!");
        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
        $player->teleport($spawn, 0, 0);
        $player->setGamemode(0);
        $player->setHealth(20);
        $player->setFood(20);
        $player->getInventory()->clearAll();
        $helm = Item::get(310, 0, 1);
        $chest = Item::get(311, 0, 1);
        $hose = Item::get(312, 0, 1);
        $boots = Item::get(313, 0, 1);
        /*$e1 = Enchantment::getEnchantment(0);
        $e2 = Enchantment::getEnchantment(1);
        $helm->addEnchantment($e2);
        $chest->addEnchantment($e1);
        $chest->addEnchantment($e2);
        $hose->addEnchantment($e2);
        $boots->addEnchantment($e2);*/
        $player->getInventory()->addItem(Item::get(320, 0, 64));
        $player->getArmorInventory()->setHelmet($helm);
        $player->getArmorInventory()->setChestplate($chest);
        $player->getArmorInventory()->setLeggings($hose);
        $player->getArmorInventory()->setBoots($boots);
    	
    }
    
    public function onQuit(PlayerQuitEvent $event) {
    	
    	$player = $event->getPlayer();
        $event->setQuitMessage(Color::GRAY . "< " . Color::DARK_GRAY . "< " . $player->getDisplayName() . Color::GRAY . " hat den Server verlassen!");
        
    }
    
    public function onMove(PlayerMoveEvent $event) {
    	
    	$player = $event->getPlayer();
        $y = $player->getY();
        if ($y > 21) {
        	
        	$effect = Effect::getEffect(10);
            $effect->setAmplifier(5);
            $effect->setDuration(150);
            $player->addEffect($effect);
        	
        }
    	
    }
    
    public function onDamage(EntityDamageEvent $event) {
    	
    	$player = $event->getEntity();
        $config = $this->getConfig();
        $y = $player->getY();
        if ($event instanceof EntityDamageByEntityEvent) {
        	
        	$damager = $event->getDamager();
            $player = $event->getEntity();
            if ($damager instanceof Player) {
            	
            } else {
            	
            	$event->setCancelled(true);
            	
            }
            
        }
        
        if ($y > 21) {
        	
        	$event->setCancelled(true);
        
        } else {
        	
        	$event->setCancelled(false);
        	
        }
        
    }
    
    public function onPlace(BlockPlaceEvent $event) {
    
        $player = $event->getPlayer();
        $config = $this->getConfig();
        $event->setCancelled(true);
        
    }
    
    public function onBreak(BlockBreakEvent $event) {
    
        $player = $event->getPlayer();
        $config = $this->getConfig();
        $event->setCancelled(true);
        
    }
    
    public function onDeath(PlayerDeathEvent $event) {
    	
    	$player = $event->getEntity();
        $event->setDrops(array());
        $event->setDeathMessage($this->prefix . Color::GREEN . $player->getDisplayName() . Color::WHITE . " ist Gestorben!");
    	
    }
    
    public function onRespawn(PlayerRespawnEvent $event) {
    	
    	$player = $event->getPlayer();
        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
        $player->teleport($spawn, 0, 0);
        $player->setGamemode(0);
        $player->setHealth(20);
        $player->setFood(20);
        $player->getInventory()->clearAll();
        $helm = Item::get(310, 0, 1);
        $chest = Item::get(311, 0, 1);
        $hose = Item::get(312, 0, 1);
        $boots = Item::get(313, 0, 1);
        /*$e1 = Enchantment::getEnchantment(0);
        $e2 = Enchantment::getEnchantment(1);
        $helm->addEnchantment($e2);
        $chest->addEnchantment($e1);
        $chest->addEnchantment($e2);
        $hose->addEnchantment($e2);
        $boots->addEnchantment($e2);*/
        $player->getInventory()->addItem(Item::get(320, 0, 64));
        $player->getArmorInventory()->setHelmet($helm);
        $player->getArmorInventory()->setChestplate($chest);
        $player->getArmorInventory()->setLeggings($hose);
        $player->getArmorInventory()->setBoots($boots);
        
    }
	
}

class PlayerSender extends PluginTask
{
	
	public function __construct($plugin)
    {

        $this->plugin = $plugin;
        parent::__construct($plugin);

    }

    public function onRun($tick)
    {
    	
    	$config = $this->plugin->getConfig();
        $all = $this->plugin->getServer()->getOnlinePlayers();
        $config->set("players", count($all));
        $config->save();
        if (count($all) === 0) {

            if ($config->get("state") === true) {

                $config->set("ingame", false);
                $config->set("state", false);
                $config->set("reset", false);
                $config->set("rtime", 10);
                $config->set("time", 60);
                $config->set("playtime", 3600);
                $config->save();

            }

        }
    	
    }
	
}