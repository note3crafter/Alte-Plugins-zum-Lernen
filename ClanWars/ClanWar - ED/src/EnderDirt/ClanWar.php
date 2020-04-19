<?php

namespace EnderDirt;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as Color;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\inventory\BaseInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\Server;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\entity\Effect;
use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\PopSound;
use pocketmine\level\particle\Particle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\event\player\PlayerChatEvent;

class ClanWar extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::AQUA . "ClanWar" . Color::WHITE . "] ";
	
	public function onEnable() {
		
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::GREEN . " EnderDirt!");
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	if ($command->getName() === "ClanWar") {
    	
    	    if (isset($args[0])) {
    	        
    	        if (strtolower($args[0]) === "make") {
    	
    	            if (isset($args[1])) {
    	
    	                $sf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                        if ($sf->get("ClanStatus") === false) {
                        	
                        	$sender->sendMessage(Color::RED . "Du bist in keinem Clan");
                        
                        } else {
                        	
                        	if ($sf->get("Clan") === $args[1]) {
                        	
                        	    $sender->sendMessage(Color::RED . "Du kannst nicht deinen Clan herausfordern");
                        
                            } else {
                            	
                            	$sff = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
                                if ($sff->get("ClanParty") === false) {
                                	
                                	$sender->sendMessage(Color::RED . "Du bist in keiner ClanParty");
                                
                                } else {
                                	
                                	if ($sff->get("ClanPartyOnline") < 4) {
                                	
                                	    $sender->sendMessage(Color::RED . "Es sind zu wenig Spieler in der Clan Party");
                                
                                    } else if ($sff->get("ClanPartyOnline") === 4) {
                                    	
                                    	if (file_exists("/home/EnderCloud/Clans/" . $args[1] . ".yml")) {
    	
                                           $sender->sendMessage($this->prefix . "Du hast erfolgreich den Clan: [" . Color::YELLOW . $args[1] . Color::WHITE . "] herausgefordert");
    
                                        } else {
                                        	
                                            $sender->sendMessage(Color::RED . "Diesen Clan gibt es nicht!");
                                        	
                                        }
                                    	
                                    }
                                	
                                }
                            	
                            }
                        	
                        }
                        
                    } else {
                    	
                    	$sender->sendMessage(Color::AQUA . "-> " . Color::WHITE . "/cw make <Clan>");
                    	
                    }
                    
                } else {
                	
                	$sender->sendMessage(Color::AQUA . "-> " . Color::WHITE . "/cw make <Clan>");
                	
                }
    	        
            } else {
            	
               $sender->sendMessage(Color::AQUA . "-> " . Color::WHITE . "/cw make <Clan>");
                
           }
        
        }
        
        return true;
        
    }
	
}