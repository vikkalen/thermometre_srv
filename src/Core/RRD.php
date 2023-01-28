<?php
namespace App\Core;
use \Exception;

class RRD
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    private function getFilename($sonde)
    {
        return $this->path . '/' . $sonde . '.rrd';
    }

    public function update($data, $sonde)
    {
        $this->open($sonde);

        $dataStr = 'N'
            . ':' . $data['temperature']
            . ':' . $data['voltage']
            . ':' . $data['intensity'];

        if(!rrd_update($this->getFilename($sonde), array($dataStr)))
        {
            throw new Exception(rrd_error());
        }
    }

    public function info($sonde)
    {
        $this->open($sonde);

        $info = rrd_info($this->getFilename($sonde));
        foreach($info as &$value)
        {
            if(is_float($value) && (is_nan($value) || is_infinite($value))) $value = null;
        }

        return $info;
    }

    public function graph($datasource, $period, $width, $height, $options)
    {
        $graphObj = new \RRDGraph('-');
        $graphObj->setOptions($this->getGraphOptions($datasource, $period, $width, $height, $options));
        $data = $graphObj->saveVerbose();

        if($data === false)
        {
            throw new Exception(rrd_error());
        }

        return $data['image'];
    }

    protected function open($sonde)
    {
        $filename = $this->getFilename($sonde);
        if(!file_exists($filename))
        {
            if (!rrd_create($filename, $this->getCreateOptions()))
            {
                throw new Exception(rrd_error());
            }
        }
    }

    protected function getCreateOptions()
    {
        $options = array(
            "--step", "300",
            "DS:temperature:GAUGE:600:-50:U",
            "DS:voltage:GAUGE:600:0:U",
            "DS:intensity:GAUGE:600:0:U",
            "RRA:AVERAGE:0.5:1:576",
            "RRA:AVERAGE:0.5:12:336",
            "RRA:AVERAGE:0.5:12:1440",
            "RRA:AVERAGE:0.5:288:730",
            "RRA:MIN:0.5:1:576",
            "RRA:MIN:0.5:12:336",
            "RRA:MIN:0.5:12:1440",
            "RRA:MIN:0.5:288:730",
            "RRA:MAX:0.5:1:576",
            "RRA:MAX:0.5:12:336",
            "RRA:MAX:0.5:12:1440",
            "RRA:MAX:0.5:288:730",
        );

        return $options;
    }

    protected function getGraphOptions($datasource, $period, $width, $height, $config)
    {
        switch($period)
        {
            case 'daily':
                $start = 'end-25hours';
                break;
            case 'weekly':
                $start = 'end-8days';
                break;
            case 'monthly':
                $start = 'end-1month-1day';
                break;
            case 'yearly':
                $start = 'end-13months';
                break;
        }

        $probeConf = $config['probes'][$datasource];
        $res = $probeConf['res'];
        $title = $probeConf['name'];

        $options =  array(
            "--title", $title,
            "--width", $width,
            "--height", $height,
            "--full-size-mode",
            "--slope-mode",
            "--start", $start,
            "--end", "now",
            "--dynamic-labels",
            "--color", "BACK#00000000",
            "--color", "CANVAS#00000000",
            "--color", "SHADEA#00000000",
            "--color", "SHADEB#00000000",
            "--color", "ARROW#FFFFFF",
            "--color", "AXIS#FFFFFF",
            "--color", "FONT#FFFFFF",
            "--imgformat", "PNG",
            "COMMENT:            ",
            "COMMENT:Min",
            "COMMENT:Max",
            "COMMENT:Avg",
            "COMMENT:Cur\j",
        );

        foreach($config['sondes'] as $sonde)
        {
            $sondeId = $sonde['id'];
            $sondeFile = $this->getFilename($sondeId);
            $color = $sonde['color'];
            $name = str_pad($sonde['name'], 10, ' ', STR_PAD_LEFT);
            $options[] = "DEF:val_min_$sondeId=$sondeFile:$datasource:MIN";
            $options[] = "DEF:val_max_$sondeId=$sondeFile:$datasource:MAX";
            $options[] = "DEF:val_avg_$sondeId=$sondeFile:$datasource:AVERAGE";
            $options[] = "DEF:val_last_$sondeId=$sondeFile:$datasource:LAST";

            $options[] = "VDEF:min_$sondeId=val_min_$sondeId,MINIMUM";
            $options[] = "VDEF:max_$sondeId=val_max_$sondeId,MAXIMUM";
            $options[] = "VDEF:avg_$sondeId=val_avg_$sondeId,AVERAGE";
            $options[] = "VDEF:last_$sondeId=val_last_$sondeId,LAST";

            $options[] = "LINE1:val_avg_$sondeId$color:$name";

            //$options[] = "COMMENT:$name";
            $options[] = "GPRINT:min_$sondeId:%${res}lf";
            $options[] = "GPRINT:max_$sondeId:%${res}lf";
            $options[] = "GPRINT:avg_$sondeId:%${res}lf";
            $options[] = "GPRINT:last_$sondeId:%${res}lf\j";
        }

        return $options;

    }
}
