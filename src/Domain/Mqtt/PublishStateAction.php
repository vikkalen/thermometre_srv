<?php
namespace App\Domain\Mqtt;

use App\Application\Settings\SettingsInterface;
use PhpMqtt\Client\MqttClient;
use Psr\Container\ContainerInterface;

class PublishStateAction extends BasePublishAction
{
    public function execute(MqttClient $client)
    {
        $topic = $this->settings()['state_topic'];
        $config = $this->container->get('json')->read();
        foreach($config['sondes'] as $sonde) {
            $id = $sonde['id'];
            $info = $this->container->get('rrd')->info($id);
            $stateData = [];
    
            $temperature = $info['ds[temperature].last_ds'];
            if($temperature !== null) $stateData['temperature'] = round((float)$temperature, 2);

            $voltage = $info['ds[voltage].last_ds'];
            if($voltage !== null) $stateData['voltage'] = round((float)$voltage, 3);
            if($voltage !== null) $stateData['battery'] = (int)(((float)$voltage - 2) * 100);
    
            $intensity = $info['ds[intensity].last_ds'];
            if($intensity !== null) $stateData['linkquality'] = (int)$intensity;
    
            if($stateData) $this->publish($client, "$topic/thermo-$id", $stateData);
        }
   }
}
