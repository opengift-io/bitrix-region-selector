<?php
namespace OpenGift\BitrixRegionManager;

class RegionManager {
    public static function getRegionByIp($ip) {
        $reader = new \GeoIp2\Database\Reader(realpath(dirname(__FILE__).'/../../'). '/db/GeoLite2-City.mmdb', ['ru']);
        return $reader->city($ip);
    }
}