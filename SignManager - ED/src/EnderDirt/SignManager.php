<?php

namespace EnderDirt;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Color;

use EnderDirt\SignManagerListener;

class SignManager extends PluginBase {
	
    public $prefix = '§7[§eSign-Manager§7]';
    public $listener;

    public function onEnable() {
    	
        $this->listener = new SignManagerListener($this);

        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::GREEN . " EnderDirt!");
        
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	if ($command->getName() === "SignManager") {
    	
    	    $this->sendGui($sender);
            
        }
        
    	return true;
    
    }

    public function sendGui(Player $player) {
    	
        $fdata = [];

        $fdata['title'] = '§eSign-Manager';
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = 'form';

        $fdata['buttons'][] = ['text' => '§bBedWars'];
        $fdata['buttons'][] = ['text' => '§6MLG§eRush'];
        $fdata['buttons'][] = ['text' => '§4SOON'];

        $pk = new ModalFormRequestPacket();
        $pk->formId = 1000;
        $pk->formData = json_encode($fdata);

        $player->sendDataPacket($pk);
        
    }
    
    public function sendBedWarsSign(Player $player) {
    	
        $fdata = [];

        $fdata['title'] = '§eSign-Manager';
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = 'form';

        $fdata['buttons'][] = ['text' => '§2ON'];
        $fdata['buttons'][] = ['text' => '§4OFF'];

        $pk = new ModalFormRequestPacket();
        $pk->formId = 1001;
        $pk->formData = json_encode($fdata);

        $player->sendDataPacket($pk);
        
    }
    
    public function sendMLGRushSign(Player $player) {
    	
        $fdata = [];

        $fdata['title'] = '§eSign-Manager';
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = 'form';

        $fdata['buttons'][] = ['text' => '§2ON'];
        $fdata['buttons'][] = ['text' => '§4OFF'];

        $pk = new ModalFormRequestPacket();
        $pk->formId = 1002;
        $pk->formData = json_encode($fdata);

        $player->sendDataPacket($pk);
        
    }
    
}