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

class Main extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::DARK_PURPLE . "eGroups" . Color::WHITE . "] ";
	public $players = [];
	
	public function onEnable() {
		
		@mkdir($this->getDataFolder());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::GREEN . " EnderDirt!");
        
    }
    
    public function onChat(PlayerChatEvent $event) {
    	
    	$player = $event->getPlayer();
        $msg = $event->getMessage();
        $pf = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
        $clan = $pf->get("Clan");
        if ($pf->get("ClanStatus") === true) {
        	
        	if ($pf->get("Default") === true) {
        	
        	    $event->setFormat($player->getDisplayName() . Color::WHITE . " [" . Color::YELLOW . $clan . Color::WHITE . "]" . Color::GRAY . " > " . $msg);
        
            } else {
            	
            	$event->setFormat($player->getDisplayName() . Color::WHITE . " [" . Color::YELLOW . $clan . Color::WHITE . "]" . " > " . $msg);
            	
            }
        	
        } else {
        	
        	$event->setFormat($player->getDisplayName() . " > " . $msg);
        	
        }
        
    }
    
    public function onJoin(PlayerJoinEvent $event) {
    	
    	$player = $event->getPlayer();
        $pf = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
        if ($pf->get("Default") === true) {
        	
             $player->setDisplayName(Color::DARK_GRAY . "Spieler" . Color::WHITE . " : " . Color::GRAY . $player->getName());
             $player->setNameTag(Color::DARK_GRAY . "Spieler" . Color::WHITE . " : " . Color::GRAY . $player->getName());
                
        } else if ($pf->get("Owner") === true) {
        	
             $player->setDisplayName(Color::DARK_RED . "Owner" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
             $player->setNameTag(Color::DARK_RED . "Owner" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
                
        } else if ($pf->get("Builder") === true) {
        	
             $player->setDisplayName(Color::GREEN . "Builder" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
             $player->setNameTag(Color::GREEN . "Builder" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
                
        } else if ($pf->get("Moderator") === true) {
        	
             $player->setDisplayName(Color::RED . "Moderator" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
             $player->setNameTag(Color::RED . "Moderator" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
                
        } else if ($pf->get("Supporter") === true) {
        	
             $player->setDisplayName(Color::BLUE . "Supporter" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
             $player->setNameTag(Color::BLUE . "Supporter" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
                
        } else if ($pf->get("YouTuber") === true) {
        	
             $player->setDisplayName(Color::DARK_PURPLE . "YouTuber" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
             $player->setNameTag(Color::DARK_PURPLE . "YouTuber" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
                
        } else if ($pf->get("VIP+") === true) {
        	
             $player->setDisplayName(Color::GOLD . "VIP+" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
             $player->setNameTag(Color::GOLD . "VIP+" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
                
        } else if ($pf->get("VIP") === true) {
        	
             $player->setDisplayName(Color::GOLD . "VIP" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
             $player->setNameTag(Color::GOLD . "VIP" . Color::WHITE . " : " . Color::GRAY . $player->getName() . Color::WHITE);
                
        }
        
    }
    
    public function onPlayerLogin(PlayerLoginEvent $event) {
    
        $player = $event->getPlayer();        
        if (!is_file("/home/EnderCloud/eGroups/" . $player->getName() . ".yml")) {
        
            $playerfile = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("Default", true);
            $playerfile->set("Owner", false);
            $playerfile->set("Admin", false);
            $playerfile->set("Builder", false);
            $playerfile->set("Moderator", false);
            $playerfile->set("Supporter", false);
            $playerfile->set("YouTuber", false);
            $playerfile->set("VIP", false);
            $playerfile->set("VIP+", false);
            $playerfile->set("Nick", false);
            $playerfile->set("NickP", false);
            $playerfile->save();
            
        }
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
     	   case "Group":
            if (isset($args[0])) {
            	
            	if (file_exists("/home/EnderCloud/eGroups/" . $args[0] . ".yml")) {
            	
            	    $playerfile = new Config("/home/EnderCloud/eGroups/" . $args[0] . ".yml", Config::YAML);
            	    if ($sender->isOp()) {
            	
            	        if (isset($args[1])) {
            	
            	            if (strtolower($args[1]) === "default") {
            	
            	                $playerfile->set("Default", true);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("Supporter", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP+", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", false);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "owner") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", true);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("Supporter", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP+", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "admin") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", true);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("Supporter", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP+", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "builder") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", true);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("Supporter", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP+", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "moderator") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", true);
                                $playerfile->set("Supporter", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP+", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "supporter") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("Supporter", true);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP+", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "youtuber") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("Supporter", false);
                                $playerfile->set("YouTuber", true);
                                $playerfile->set("VIP+", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "vip+") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("Supporter", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP+", true);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "vip") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("Supporter", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP+", false);
                                $playerfile->set("VIP", true);
                                $playerfile->set("NickP", false);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            }
                            
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        if ($command->getName() === "VIP") {
        	
        	if (isset($args[0])) {
        	    $playerfile = new Config("/home/EnderCloud/players/" . $args[0] . ".yml", Config::YAML);
                $playerfile->set("VIP", true);
                $playerfile->save();
                $sender->sendMessage("Erfolgreich");
            }
        	
        }
        
        return true;
        
    }
	
}