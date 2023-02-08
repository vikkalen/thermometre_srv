<?php
namespace App\Domain\Mqtt;

use App\Application\Settings\SettingsInterface;
use PhpMqtt\Client\MqttClient;
use Psr\Container\ContainerInterface;

class PublishDiscoveryAction extends BasePublishAction
{
    public function execute(MqttClient $client)
    {
        $topic = $this->settings()['discovery_topic'];
        $config = $this->container->get('json')->read();
        foreach($config['sondes'] as $sonde) {

            $deviceId = "thermo-" . $sonde['id'];
            $discoveryData = [
            	"device" => [
            	    "identifiers" => ["${deviceId}_device"],
            	    "manufacturer" => "Michal Olexa",
            	    "model" => "Temperature sensor",
            	],
            	"enabled_by_default" => true,
            	"state_class" => "measurement",
            	"state_topic" => $this->settings()['state_topic'] . "/$deviceId",
            ];

            $batteryDiscoveryData = $discoveryData;
            $batteryDiscoveryData['device_class'] = "battery";
            $batteryDiscoveryData['entity_category'] = "diagnostic";
            $batteryDiscoveryData['name'] = "$deviceId battery";
            $batteryDiscoveryData['unique_id'] = "${deviceId}_battery";
            $batteryDiscoveryData['unit_of_measurement'] = "%";
            $batteryDiscoveryData['value_template'] = "{{ value_json.battery }}";

            $temperatureDiscoveryData = $discoveryData;
            $temperatureDiscoveryData['device_class'] = "temperature";
            $temperatureDiscoveryData['name'] = "$deviceId temperature";
            $temperatureDiscoveryData['unique_id'] = "${deviceId}_temperature";
            $temperatureDiscoveryData['unit_of_measurement'] = "°C";
            $temperatureDiscoveryData['value_template'] = "{{ value_json.temperature }}";

            $voltageDiscoveryData = $discoveryData;
            $voltageDiscoveryData['device_class'] = "voltage";
            $voltageDiscoveryData['enabled_by_default'] = false;
            $voltageDiscoveryData['entity_category'] = "diagnostic";
            $voltageDiscoveryData['name'] = "$deviceId voltage";
            $voltageDiscoveryData['unique_id'] = "${deviceId}_voltage";
            $voltageDiscoveryData['unit_of_measurement'] = "V";
            $voltageDiscoveryData['value_template'] = "{{ value_json.voltage }}";
        		
            $linkqualityDiscoveryData = $discoveryData;
            $linkqualityDiscoveryData['enabled_by_default'] = false;
            $linkqualityDiscoveryData['entity_category'] = "diagnostic";
            $linkqualityDiscoveryData['icon'] = "mdi:signal";
            $linkqualityDiscoveryData['name'] = "$deviceId linkquality";
            $linkqualityDiscoveryData['unique_id'] = "${deviceId}_linkquality";
            $linkqualityDiscoveryData['unit_of_measurement'] = "lqi";
            $linkqualityDiscoveryData['value_template'] = "{{ value_json.linkquality }}";
        	
    	    $this->publish($client, "$topic/sensor/$deviceId/battery/config", $batteryDiscoveryData);
    	    $this->publish($client, "$topic/sensor/$deviceId/temperature/config", $temperatureDiscoveryData);
    	    $this->publish($client, "$topic/sensor/$deviceId/voltage/config", $voltageDiscoveryData);
    	    $this->publish($client, "$topic/sensor/$deviceId/linkquality/config", $linkqualityDiscoveryData);
        }
   }
}
