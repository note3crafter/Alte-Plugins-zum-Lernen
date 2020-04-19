<?php

namespace EnderDirt;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Color;
use pocketmine\Player;

class ShopListener implements Listener {
	
    private $plugin;

    public function __construct(Shop $plugin) {
    	
        $this->plugin = $plugin;
        $this->plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
        
    }

    public function onPacketReceive(DataPacketReceiveEvent $event) {
    	
        $pk = $event->getPacket();
        $player = $event->getPlayer();
        if ($pk instanceof ModalFormResponsePacket) {
        	
            $id = $pk->formId;
            $data = json_decode($pk->formData);
            if ($id == 1) {
            	
                if ($data !== NULL) {
                	
                    if ($data == 0) {
                    	
                    	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        if ($pf->get("VIP") === true) {
                        	
                        	$this->sendSprengMeister($player);
                        	
                        } else {
                        	
                        	if ($pf->get("SprengMeister") === true) {
                        	    
                        	    $this->sendSprengMeister($player);
                        	    
                            } else {
                            	
                            	$this->sendSprengMeisterBuy($player);
                            	
                            }
                            
                        }
                        
                    } else if ($data == 1) {
                    	
                        $player->sendMessage(Color::RED . "Dieses Kit ist bald benutzbar");
                        
                    } else if ($data == 2) {
                    	
                        $player->sendMessage(Color::RED . "Dieses Kit ist bald benutzbar");
                        
                    }
                    
                }
                
            } else if ($id == 2) {
            	
            	if ($data !== NULL) {
            	
            	    if ($data == 0) {
            	
            	        $pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        $pf->set("MLGKit", "SprengMeister");
                        $pf->save();
                        $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "SprengMeister" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                        
                    }
                    
                }

            } else if ($id == 3) {
            	
            	if ($data !== null) {
            	
            	    if ($data == 0) {
            	
            	        $pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
                        if ($pc->get("coins") >= 2000) {
                        	
                        	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("SprengMeister", true);
                            $pf->save();
                            $pc->set("coins", $pc->get("coins")-5000);
                            $pc->save();
                            $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "SprengMeister" . Color::WHITE . " wurde erfolgreich gekauft");
                            
                        } else {
                        	
                        	$player->sendMessage(Color::RED . "Du hast zu wenig Coins");
                        	
                        }
                        
                    } else if ($data == 1) {
                    	
                    	$player->sendMessage(Color::WHITE . "====> " . Color::YELLOW . "SPRENGMEISTER" . Color::WHITE . " <====");
                        $player->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "1 TNT");
                        $player->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "8 CobWebs");
                        $player->sendMessage(Color::YELLOW . "Kosten: " . Color::WHITE . "2000 Coins");
                        $player->sendMessage(Color::WHITE . "====> " . Color::YELLOW . "SPRENGMEISTER" . Color::WHITE . " <====");
                    	
                    }
                    
                }
            	
            }
            
        }
        
    }
    
    public function sendSprengMeister(Player $player) {
    	
        $fdata = [];

        $fdata['title'] = '§l§4SPRENGMEISTER';
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = 'form';

        $fdata['buttons'][] = ['text' => '§l§2Benutzen'];

        $pk = new ModalFormRequestPacket();
        $pk->formId = 2;
        $pk->formData = json_encode($fdata);

        $player->sendDataPacket($pk);
        
    }
    
    public function sendSprengMeisterBuy(Player $player) {
    	
        $fdata = [];

        $fdata['title'] = '§l§4SPRENGMEISTER';
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = 'form';

        $fdata['buttons'][] = ['text' => '§l§2Kaufen'];
        $fdata['buttons'][] = ['text' => '§l§eInfo'];

        $pk = new ModalFormRequestPacket();
        $pk->formId = 3;
        $pk->formData = json_encode($fdata);

        $player->sendDataPacket($pk);
        
    }
    
}