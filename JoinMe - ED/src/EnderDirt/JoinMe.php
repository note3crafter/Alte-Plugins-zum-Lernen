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
use pocketmine\event\player\PlayerBedEnterEvent;
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
use pocketmine\event\inventory\CraftItemEvent;

class JoinMe extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::DARK_PURPLE . "JoinMe" . Color::WHITE . "] ";
	
	public function onEnable() {
		
		if (is_dir("/home/EnderCloud/JoinMe") !== true) {
			
            mkdir("/home/EnderCloud/JoinMe");
            
        }
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::DARK_PURPLE . " EnderDirt!");
		
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	if ($command->getName() === "JoinMe") {
    	
    	    if (isset($args[0])) {
    	
    	        if (file_exists("/home/EnderCloud/players/" . $args[0] . ".yml")) {
    	
    	            $pf = new Config("/home/EnderCloud/players/" . $args[0] . ".yml", Config::YAML);
                    $sf = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
                    $port = $pf->get("Port");
                    if ($pf->get("JoinMe") === true) {
                    	
                    	if ($port === 20201) {
                    	
                	        $sf->set("Port", 20201);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 20201);
                	
                        } else if ($port === 30101) {
                    	
                	        $sf->set("Port", 30101);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 30101);
                	
                        } else if ($port === 20202) {
                    	
                	        $sf->set("Port", 20202);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 20202);
                	
                        } else if ($port === 20203) {
                    	
                	        $sf->set("Port", 20203);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 20203);
                	
                        } else if ($port === 20204) {
                    	
                	        $sf->set("Port", 20204);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 20204);
                	
                        } else if ($port === 20205) {
                    	
                	        $sf->set("Port", 20205);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 20205);
                	
                        } else if ($port === 20206) {
                    	
                	        $sf->set("Port", 20206);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 20206);
                	
                        } else if ($port === 20207) {
                    	
                	        $sf->set("Port", 20207);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 20207);
                	
                        } else if ($port === 20208) {
                    	
                	        $sf->set("Port", 20208);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 20208);
                	
                        } else if ($port === 20209) {
                    	
                	        $sf->set("Port", 20209);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 20209);
                	
                        } else if ($port === 20210) {
                    	
                	        $sf->set("Port", 20210);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 20210);
                	
                        } else if ($port === 19201) {
                    	
                	        $sf->set("Port", 19201);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 19201);
                	
                        } else if ($port === 19202) {
                    	
                	        $sf->set("Port", 19202);
                            $sf->save();
                        	$sender->transfer("84.200.84.61", 19202);
                	
                        }
                    	
                    } else {
                    	
                    	$sender->sendMessage(Color::RED . "Dieser Spieler hat keine JoinMe Nachricht versendet");
                    	
                    }
    
                } else {
                	
                	$sender->sendMessage(Color::RED . "Diesen Spieler gibt es nicht");
                	
                }
                
            } else {
            	
            	$pg = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                if ($pg->get("NickP") === true) {
                	
                	$pf = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
                	$joinme = new Config("/home/EnderCloud/JoinMe/JoinMe.yml", Config::YAML);
                
                    $pf->set("JoinMe", true);
                    $pf->save();
                    
                    if ($pf->get("Port") === 20201) {
                    	
                    	$joinme->set("Server", "BedWars 2x1");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 30101) {
                    	
                    	$joinme->set("Server", "Private-BowFight 2x1");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 20202) {
                    	
                    	$joinme->set("Server", "BedWars 2x4");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 20203) {
                    	
                    	$joinme->set("Server", "BedWars 2x4");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 20204) {
                    	
                    	$joinme->set("Server", "BedWars 2x1");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 20205) {
                    	
                    	$joinme->set("Server", "BedWars 2x4");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 20206) {
                    	
                    	$joinme->set("Server", "BedWars 2x4");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 20207) {
                    	
                    	$joinme->set("Server", "BedWars 2x4");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 20208) {
                    	
                    	$joinme->set("Server", "BedWars 2x4");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 20209) {
                    	
                    	$joinme->set("Server", "BedWars 8x1");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 20210) {
                    	
                    	$joinme->set("Server", "BedWars 4x2");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 19201) {
                    	
                    	$joinme->set("Server", "UHCMeetup 16x1");
                        $joinme->save();
                    	
                    } else if ($pf->get("Port") === 19202) {
                    	
                    	$joinme->set("Server", "UHCMeetup 16x1");
                        $joinme->save();
                    	
                    }
                    
                    $joinme->set("Player", $sender->getName());
                    $joinme->save();
                    $joinme->set("Status", true);
                    $joinme->save();
                    
                    $sender->sendMessage(Color::GREEN . "Deine JoinMe Nachricht wurde erfolgreich verschickt");
                    
                } else {
                	
                	$sender->sendMessage(Color::RED . "Du hast keine Berechtigung für diesen Command");
                	
                }
            	
            }
            
        }
        
        return true;
        
    }
    
}