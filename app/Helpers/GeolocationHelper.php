<?php
namespace App\Helpers;

use App\Models\User;
use GeoIp2\Database\Reader;

class GeolocationHelper {

    private Reader $reader;

   public function __construct()
   {
       $this->reader = new Reader(storage_path(config('site.geoip_db_path')));
   }

   public function country(User $user)
   {
       $ip = $user->ip_address ?? $user->ip_address_reg;
       if (!$ip) {
           return null;
       }

       try {
           $country = $this->reader->country($ip);
           return $country->country->name;
       } catch (\Exception $e) {
           return '-';
       }
   }

}
