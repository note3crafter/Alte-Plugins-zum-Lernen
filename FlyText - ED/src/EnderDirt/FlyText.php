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
//BlockEvents
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
//EntityEvents
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityLevelChangeEvent;
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
use pocketmine\tile\Tile;
//Nbt
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
//Inventar
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\Inventory;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
//Partikel
use pocketmine\level\particle\FloatingTextParticle;

class FlyText extends PluginBase implements Listener {
	
	public function onEnable() {
		
		@mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
    }
    
    public function onJoin(PlayerJoinEvent $event) {
    	
    	$player = $event->getPlayer();
    	$config = $this->getConfig();
        $level = $this->getServer()->getDefaultLevel();
        if (!is_file("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml")) {
        	
        	$playerfile = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("Kills", 0);
            $playerfile->set("Deaths", 0);
            $playerfile->set("KD", 0);
            $playerfile->save();
            $bfx = $config->get("bfx");
            $bfy = $config->get("bfy");
            $bfz = $config->get("bfz");
            $level->addParticle(new FloatingTextParticle(new Vector3($bfx, $bfy, $bfz),"", Color::WHITE . "<< " . Color::YELLOW . "BowFight" . Color::WHITE . " >>\n" .
                                                                                                                                                     "\n" .
                                                                                                                                                     Color::YELLOW . "    Kills: " . Color::WHITE . $playerfile->get("Kills") . "\n" .
                                                                                                                                                     Color::YELLOW . "    Deaths: " . Color::WHITE . $playerfile->get("Deaths") . "\n" .
                                                                                                                                                     Color::YELLOW . "    K/D: " . Color::WHITE . $playerfile->get("KD")));
        	
        } else {
        	
        	$playerfile = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
            $bfx = $config->get("bfx");
            $bfy = $config->get("bfy");
            $bfz = $config->get("bfz");
            $deaths = $playerfile->get("Deaths");
            $kills = $playerfile->get("Kills");
            if ($deaths === 0) {
              	
              $kd = $kills;
              
            } else {
              	
              $kd = $kills/$deaths;
              
            }
            
            $level->addParticle(new FloatingTextParticle(new Vector3($bfx, $bfy, $bfz),"", Color::WHITE . "<< " . Color::YELLOW . "BowFight" . Color::WHITE . " >>\n" .
                                                                                                                                                     "\n" .
                                                                                                                                                     Color::YELLOW . "    Kills: " . Color::WHITE . $playerfile->get("Kills") . "\n" .
                                                                                                                                                     Color::YELLOW . "    Deaths: " . Color::WHITE . $playerfile->get("Deaths") . "\n" .
                                                                                                                                                     Color::YELLOW . "    K/D: " . Color::WHITE . $kd));
        	
        }
        
        
        
    }
    
    public function stringToInstance($name, $vec3) {
    	
    	switch(strtolower($name)) {
			
           case "text":
           return new FloatingTextParticle($vec3);
           
        }
              
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	if ($command->getName() === "Text") {
    	
    	    if ($sender->isOp()) {
    	
    	        if (isset($args[0])) {
    	
    	            if ($args[0] === "BowFight") {
    	
    	               $x = $sender->getX();
                       $y = $sender->getY();
                       $z = $sender->getZ();
                       $config = $this->getConfig();
                       $config->set("bfx", $x);
                       $config->set("bfy", $y);
                       $config->set("bfz", $z);
                       $config->save();
                       
                    }
                    
                }
                
            }
            
        }
    	
    	return true;
    
    }
	
}