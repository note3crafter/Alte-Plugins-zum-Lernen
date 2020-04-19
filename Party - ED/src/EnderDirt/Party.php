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
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
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
use pocketmine\level\sound\GhastShootSound;
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

class Party extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::DARK_PURPLE . "Party" . Color::WHITE . "] ";
	
	public function onEnable() {
    	
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new PartyTask($this), 20);
        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::GREEN . " EnderDirt!");
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
    	    case "Party":
            if (isset($args[0])) {
            	
           	 if (strtolower($args[0]) === "accept") {
            	
               	$sf = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
                   $sf->set("Party", true);
                   $sf->save();
                   $pf = new Config("/home/EnderCloud/players/" . $sf->get("PartyAnfrage") . ".yml", Config::YAML);
                   if ($pf->get("PartyOn") === 1) {
                	
                   	$pf->set("PartyMember1", $sender->getName());
                       $pf->set("PartyOn", 2);
                       $pf->save();
                       $p = $this->getServer()->getPlayerExact($sf->get("PartyAnfrage"));
                       if (!$p == null) {
                    	
                       	$p->sendMessage($this->prefix . "Der Spieler: " . Color::YELLOW . $sender->getName() . Color::WHITE . " ist deiner Party beigetreten");
                           $sender->sendMessage(Color::GREEN . "Du hast erfolgreich die Party betreten");
                           $sf->set("PartyLeader", $sf->get("PartyAnfrage"));
                           $sf->set("PartyAnfrage", "");
                           $sf->save();
                    	
                       }
                	
                   } else if ($pf->get("PartyOn") === 2) {
                	
                   	$pf->set("PartyMember2", $sender->getName());
                       $pf->set("PartyOn", 3);
                       $pf->save();
                       $p = $this->getServer()->getPlayerExact($sf->get("PartyAnfrage"));
                       if (!$p == null) {
                    	
                       	$p->sendMessage($this->prefix . "Der Spieler: " . Color::YELLOW . $sender->getName() . Color::WHITE . " ist deiner Party beigetreten");
                           $sender->sendMessage(Color::GREEN . "Du hast erfolgreich die Party betreten");
                           $sf->set("PartyLeader", $sf->get("PartyAnfrage"));
                           $sf->set("PartyAnfrage", "");
                           $sf->save();
                    	
                       }
                	
                   } else if ($pf->get("PartyOn") === 3) {
                	
                   	$pf->set("PartyMember3", $sender->getName());
                       $pf->set("PartyOn", 4);
                       $pf->save();
                       $p = $this->getServer()->getPlayerExact($sf->get("PartyAnfrage"));
                       if (!$p == null) {
                    	
                       	$p->sendMessage($this->prefix . "Der Spieler: " . Color::YELLOW . $sender->getName() . Color::WHITE . " ist deiner Party beigetreten");
                           $sender->sendMessage(Color::GREEN . "Du hast erfolgreich die Party betreten");
                           $sf->set("PartyLeader", $sf->get("PartyAnfrage"));
                           $sf->set("PartyAnfrage", "");
                           $sf->save();
                    	
                       }
                	
                   }
            	
               } else if (strtolower($args[0]) === "invite") {
            	
            	    if (isset($args[1])) {
            	
            	        $sf = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
                        if ($sf->get("Party") === false) {
                    	    
                            $sf->set("Party", true);
                            $sf->save();
                            $sender->sendMessage(Color::GREEN . "Deine Party wurde erfolgreich erstellt");
                            if (file_exists("/home/EnderCloud/players/" . $args[1] . ".yml")) {
                            	
                            	$pf = new Config("/home/EnderCloud/players/" . $args[1] . ".yml", Config::YAML);
                                if ($pf->get("Party") === false) {
                                	
                                	$p = $this->getServer()->getPlayerExact($args[1]);
                                    if (!$p == null) {
                                    	
                                    	$pf->set("PartyAnfrage", $sender->getName());
                                        $pf->save();
                                        $sf->set("PartyOn", 1);
                                        $sf->save();
                                    	$p->sendMessage($this->prefix . "Der Spieler: " . Color::YELLOW . $sender->getName() . Color::WHITE . " hat dir eine Party Anfrage gesendet");
                                        $p->sendMessage($this->prefix . "-> /party accept");
                                        $sender->sendMessage(Color::GREEN . "Die Party Anfrage wurde erfolgreich versendet");
                                    	
                                    } else {
                                    	
                                    	$sender->sendMessage(Color::RED . "Dieser Spieler ist nicht Online");
                                    	
                                    }
                                	
                                } else {
                                	
                                	$sender->sendMessage(Color::RED . "Der Spieler ist schon in einer Party");
                                	
                                }
                            	
                            } else {
                            	
                            	$sender->sendMessage(Color::RED . "Diesen Spieler gibt es nicht");
                            	
                            }
                    	    
                        } else {
                    	    
                    	   if (file_exists("/home/EnderCloud/players/" . $args[1] . ".yml")) {
                    	
                    	       $pf = new Config("/home/EnderCloud/players/" . $args[1] . ".yml", Config::YAML);
                                if ($pf->get("Party") === false) {
                                	
                                	$p = $this->getServer()->getPlayerExact($args[1]);
                                    if (!$p == null) {
                                    	
                                    	$pf->set("PartyAnfrage", $sender->getName());
                                        $pf->save();
                                        if ($sf->get("PartyOn") === 1) {
                                        	
                                        	$sf->set("PartyOn", 2);
                                            $sf->save();
                                            
                                        } else if ($sf->get("PartyOn") === 2) {
                                        	
                                        	$sf->set("PartyOn", 3);
                                            $sf->save();
                                            
                                        } else if ($sf->get("PartyOn") === 3) {
                                        	
                                        	$sf->set("PartyOn", 4);
                                            $sf->save();
                                            
                                        }
                                        
                                    	$p->sendMessage($this->prefix . "Der Spieler: " . Color::YELLOW . $sender->getName() . Color::WHITE . " hat dir eine Party Anfrage gesendet");
                                        $p->sendMessage($this->prefix . "-> /party accept");
                                        $sender->sendMessage(Color::GREEN . "Die Party Anfrage wurde erfolgreich versendet");
                                    	
                                    } else {
                                    	
                                    	$sender->sendMessage(Color::RED . "Dieser Spieler ist nicht Online");
                                    	
                                    }
                                	
                                } else {
                                	
                                	$sender->sendMessage(Color::RED . "Der Spieler ist schon in einer Party");
                                	
                                }
                                
                           }
                    	   
                        }
                        
                    } else {
                    	
                    	$sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/party invite <PlayerName>");
                        $sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/party del");
                        $sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/party accept");
                    	
                    }
            	    
                }
                
            } else {
            	
            	$pf = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
                if ($pf->get("Party") === true) {
                	
                	//PartyChat
                	
                } else {
                	
                	$sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/party invite <PlayerName>");
                    $sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/party del");
                    $sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/party accept");
                	
                }
            	
            }
            
        }
        
        return true;
    	
    }
	
}

class PartyTask extends PluginTask
{
	
	public function __construct($plugin)
    {

        $this->plugin = $plugin;
        parent::__construct($plugin);

    }

    public function onRun($tick)
    {
    	
    	$all = $this->plugin->getServer()->getOnlinePlayers();
    	foreach ($all as $player) {
    	
    	    $pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
            $sf = new Config("/home/EnderCloud/players/" . $pf->get("PartyLeader") . ".yml", Config::YAML);
            if ($sf->get("Transfer") === true) {
            	
            	$port = $sf->get("Port");
                if ($port === 30101) {
                	
                	$p = $this->plugin->getServer()->getPlayerExact($sf->get("PartyMember1"));
                    if (!$p == null) {
                    	
                    	$sf->set("Transfer", false);
                        $sf->save();
                    	$p->transfer("84.200.84.61", 30101);
                    
                	}
                
                }
            	
            }
            
        }
    	
    }
    
}