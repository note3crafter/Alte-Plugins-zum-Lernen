<?php

namespace EnderDirt;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Color;
use pocketmine\Player;

class ManagerListener implements Listener {
	
    private $plugin;

    public function __construct(Manager $plugin) {
    	
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
                    	
                    	$this->sendWartungen($player);
                        
                    } else if ($data == 1) {
                    	
                        $this->sendServerLog($player);
                        
                    } else if ($data == 2) {
                    	
                        $pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        if ($pf->get("MultiChat") === false) {
                        	
                        	$pf->set("MultiChat", true);
                            $pf->save();
                            $player->sendMessage("Der: " . Color::YELLOW . "Multi-Chat" . Color::WHITE . " wurde erfolgreich aktiviert");
                        	
                        } else {
                        	
                        	$pf->set("MultiChat", false);
                            $pf->save();
                            $player->sendMessage("Der: " . Color::YELLOW . "Multi-Chat" . Color::WHITE . " wurde erfolgreich deaktiviert");
                        	
                        }
                        
                    }
                    
                }
                
            } else if ($id == 2) {
            	
            	if ($data !== NULL) {
            	
            	    if ($data == 0) {
            	
            	        $pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        if ($pf->get("MultiChat") === false) {
                        	
                        	$pf->set("MultiChat", true);
                            $pf->save();
                            $player->sendMessage("Der: " . Color::YELLOW . "Multi-Chat" . Color::WHITE . " wurde erfolgreich aktiviert");
                        	
                        } else {
                        	
                        	$pf->set("MultiChat", false);
                            $pf->save();
                            $player->sendMessage("Der: " . Color::YELLOW . "Multi-Chat" . Color::WHITE . " wurde erfolgreich deaktiviert");
                        	
                        }
                        
                    }
                    
                }

            } else if ($id == 3) {
            	
            	if ($data !== NULL) {
            	
            	    if ($data == 0) {
            	
            	        $clouddata = new Config("/home/EnderCloud/Daten.yml", Config::YAML);
                        $clouddata->set("Main", true);
                        $clouddata->save();
                        $sender->sendMessage("Der Server: " . Color::GOLD . "Main" . Color::WHITE . " wurde hochgefahren!");
                        
                    } else if ($data == 1) {
            	
            	        $clouddata = new Config("/home/EnderCloud/Daten.yml", Config::YAML);
                        $clouddata->set("Main", false);
                        $clouddata->save();
                        $sender->sendMessage("Der Server: " . Color::GOLD . "Main" . Color::WHITE . " wurde heruntergefahren!");
                        
                    }
                    
                }
            	
            } else if ($id == 4) {
            	
            	if ($data !== NULL) {
            	
            	    if ($data == 0) {
            	
            	        $cloudplayer = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
                    	$cloudplayer->set("ServerLog", true);
                        $cloudplayer->save();
                        $sender->sendMessage("Der " . Color::GOLD . "Server-Log" . Color::WHITE . " wurde " . Color::GREEN . "Aktiviert");
                        
                    } else if ($data == 1) {
            	
            	        $cloudplayer = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
                    	$cloudplayer->set("ServerLog", true);
                        $cloudplayer->save();
                        $sender->sendMessage("Der " . Color::GOLD . "Server-Log" . Color::WHITE . " wurde " . Color::RED . "Deaktiviert");
                        
                    }
                    
                }
            	
            }
            
        }
        
    }
    
    public function sendWartungen(Player $player) {
    	
        $fdata = [];

        $fdata['title'] = '§l§4WARTUNGSARBEITEN';
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = 'form';

        $fdata['buttons'][] = ['text' => '§l§2AN'];
        $fdata['buttons'][] = ['text' => '§l§4AUS'];

        $pk = new ModalFormRequestPacket();
        $pk->formId = 3;
        $pk->formData = json_encode($fdata);

        $player->sendDataPacket($pk);
        
    }
    
    public function sendServerLog(Player $player) {
    	
        $fdata = [];

        $fdata['title'] = '§l§6SERVER-LOG';
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = 'form';

        $fdata['buttons'][] = ['text' => '§l§2AN'];
        $fdata['buttons'][] = ['text' => '§l§4AUS'];

        $pk = new ModalFormRequestPacket();
        $pk->formId = 4;
        $pk->formData = json_encode($fdata);

        $player->sendDataPacket($pk);
        
    }
    
}