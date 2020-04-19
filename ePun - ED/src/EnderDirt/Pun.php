<?php

namespace EnderDirt;

//Base
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
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

class Pun extends PluginBase implements Listener
{

    public $prefix = Color::WHITE . "[" . Color::DARK_PURPLE . "e" . Color::WHITE . "Pun] ";

    public function onEnable()
    {

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info($this->prefix . Color::GREEN . "die Player Cloud wurde erfolgreich geladen!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::DARK_PURPLE . " EnderDirt!");

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {

        if ($command->getName() === "Ban") {

            $group = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
            if ($group->get("Moderator") === true) {
            	
            	if (isset($args[0])) {
            	
            	    if (file_exists("/home/EnderCloud/players/" . $args[0] . ".yml")) {
            	
            	        $p = $this->getServer()->getPlayerExact($args[0]);
                        if (!$p == null) {
                        	
                        	$cloudban = new Config("/home/EnderCloud/CloudBan.yml", Config::YAML);
                            $pf = new Config("/home/EnderCloud/players/" . $args[0] . ".yml", Config::YAML);
                    
                        	$p->kick(
                            Color::RED . "Du wurdest von " . $sender->getName() . " gebannt!\n" .
                            Color::WHITE . "Du kannst einen entbannungsantrag auf:\n" .
                            Color::YELLOW . "Discord: " . Color::GRAY . "https://discord.gg/XwXKuvy", false
                            );
                        
                            $cloudban->set("Ban", $p->getName());
                            $cloudban->save();
                            $pf->set("Ban", $sender->getName());
                            $pf->save();
                        	
                        }
                        
                    } else {
                    	
                    	$sender->sendMessage(Color::RED . "Diesen Spieler gibt es nicht");
                    
                    }
                    
                } else {
                	
                	$sender->sendMessage(Color::RED . "-> /pun <Player>");
                	
                }
            	
            }
            
            if ($sender->isOp()) {
            	
            	if (isset($args[0])) {
            	
            	    if (file_exists("/home/EnderCloud/players/" . $args[0] . ".yml")) {
            	
            	        $p = $this->getServer()->getPlayerExact($args[0]);
                        if (!$p == null) {
                        	
                        	$cloudban = new Config("/home/EnderCloud/CloudBan.yml", Config::YAML);
                            $pf = new Config("/home/EnderCloud/players/" . $args[0] . ".yml", Config::YAML);
                    
                        	$p->kick(
                            Color::RED . "Du wurdest von " . $sender->getName() . " gebannt!\n" .
                            Color::WHITE . "Du kannst einen entbannungsantrag auf:\n" .
                            Color::YELLOW . "Discord: " . Color::GRAY . "https://discord.gg/XwXKuvy", false
                            );
                        
                            $cloudban->set("Ban", $p->getName());
                            $cloudban->save();
                            $pf->set("Ban", $sender->getName());
                            $pf->save();
                        	
                        }
                        
                    } else {
                    	
                    	$sender->sendMessage(Color::RED . "Diesen Spieler gibt es nicht");
                    
                    }
                    
                } else {
                	
                	$sender->sendMessage(Color::RED . "-> /pun <Player>");
                	
                }
                
            }

        }
        
        return true;

    }

}