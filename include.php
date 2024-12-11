<?php
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php')) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/vendor/autoload.php')) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/local/bitrix/autoload.php';
}


$module = 'jubiks.geolocation';

CModule::AddAutoloadClasses(
    $module,
    array()
);

class GeoLocationService
{
    private $dadataClient;
    private $sypexGeo;
    private $sypexGeoDatabasePath = '/upload/SxGeo/SxGeoCity.dat';
    private $ipGeolocationApi;
    private $ipGeoApi;
    private $dbConnection;
    private $dadataApiKey = '';
    private $ipGeolocationApiKey = '';

    public function __construct()
    {
        // Инициализация Dadata API Client, если доступен
        if (class_exists('\Dadata\DadataClient')) {
            $this->dadataClient = new \Dadata\DadataClient($this->dadataApiKey, null);
        }

        // Инициализация Sypex Geo, если доступен
        $this->sypexGeoDatabasePath = $_SERVER['DOCUMENT_ROOT'] . $this->sypexGeoDatabasePath;
        if (class_exists('\Jubiks\Geolocation\SxGeo') && file_exists($this->sypexGeoDatabasePath)) {
            $this->sypexGeo = new \Jubiks\Geolocation\SxGeo($this->sypexGeoDatabasePath);
        }

        // URL для IP Geolocation API
        $this->ipGeolocationApi = "https://api.ipgeolocation.io/ipgeo?apiKey={$this->ipGeolocationApiKey}&lang=ru&ip=";

        // URL для IP-API
        $this->ipGeoApi = 'http://ip-api.com/json/{ip}?lang=ru&fields=status,message,country,countryCode,region,regionName,city,lat,lon,query';
    }

    // Метод для получения информации о местоположении по IP
    public function getCityByIp($ip)
    {
        // Сначала проверим, есть ли IP в базе данных
        $location = $this->getLocationFromDatabase($ip);
        if ($location) {
            return $location;
        }

        // Попробуем получить местоположение через Dadata
        if ($this->dadataClient) {
            $location = $this->getLocationFromDadata($ip);
            if ($location) {
                $this->saveLocationToDatabase($ip, $location);
                return $location;
            }
        }

        // Попробуем получить местоположение через Sypex Geo
        if ($this->sypexGeo) {
            $location = $this->getLocationFromSypex($ip);
            if ($location) {
                $this->saveLocationToDatabase($ip, $location);
                return $location;
            }
        }

        // Попробуем получить местоположение через IP-API
        $location = $this->getLocationFromIpGeoApi($ip);
        if ($location) {
            $this->saveLocationToDatabase($ip, $location);
            return $location;
        }

        // Попробуем получить местоположение через IP Geolocation API
        $location = $this->getLocationFromIpGeolocation($ip);
        if ($location) {
            $this->saveLocationToDatabase($ip, $location);
            return $location;
        }

        // Если не удалось определить местоположение, вернуть null
        return null;
    }

    // Метод для получения информации о местоположении из базы данных
    private function getLocationFromDatabase($ip)
    {
        return \Kami\Geolocation\IpAddressTable::getByIp($ip)->fetch();
    }

    // Метод для получения информации о местоположении через Dadata API
    private function getLocationFromDadata($ip)
    {
        try {
            $result = $this->dadataClient->iplocate($ip);
            if ($result && $result['data']) {
                return [
                    'city' => $result['data']['city'] ?? null,
                    'city_en' => null,
                    'region' => $result['data']['region'] ?? null,
                    'region_en' => null,
                    'country' => $result['data']['country'] ?? null,
                    'country_en' => null,
                    'country_iso' => $result['data']['country_iso_code'] ?? null,
                    'latitude' => $result['data']['geo_lat'] ?? null,
                    'longitude' => $result['data']['geo_lon'] ?? null,
                ];
            }
        } catch (Exception $e) {
            // Логирование ошибки или обработка исключения
        }
        return null;
    }

    // Метод для получения информации о местоположении через Sypex Geo
    private function getLocationFromSypex($ip)
    {
        try {
            $result = $this->sypexGeo->getCityFull($ip);
            if ($result) {
                return [
                    'city' => $result['city']['name_ru'] ?? null,
                    'city_en' => $result['city']['name_en'] ?? null,
                    'region' => $result['region']['name_ru'] ?? null,
                    'region_en' => $result['region']['name_en'] ?? null,
                    'country' => $result['country']['name_ru'] ?? null,
                    'country_en' => $result['country']['name_en'] ?? null,
                    'country_iso' => $result['country']['iso'] ?? null,
                    'latitude' => $result['city']['lat'] ?? null,
                    'longitude' => $result['city']['lon'] ?? null,
                ];
            }
        } catch (Exception $e) {
            // Логирование ошибки или обработка исключения
        }
        return null;
    }

    // Метод для получения информации о местоположении через IP-API
    private function getLocationFromIpGeoApi($ip)
    {
        try {
            $url = str_replace('{ip}', $ip, $this->ipGeoApi);
            $response = file_get_contents($url);
            $result = json_decode($response, true);
            if ($result) {
                return [
                    'city' => $result['city'] ?? null,
                    'city_en' => null,
                    'region' => $result['regionName'] ?? null,
                    'region_en' => null,
                    'country' => $result['country'] ?? null,
                    'country_en' => null,
                    'country_iso' => $result['countryCode'] ?? null,
                    'latitude' => $result['lat'] ?? null,
                    'longitude' => $result['lon'] ?? null,
                ];
            }
        } catch (Exception $e) {
            // Логирование ошибки или обработка исключения
        }
        return null;
    }

    // Метод для получения информации о местоположении через IP Geolocation API
    private function getLocationFromIpGeolocation($ip)
    {
        try {
            $url = $this->ipGeolocationApi . $ip;
            $response = file_get_contents($url);
            $result = json_decode($response, true);
            if ($result) {
                return [
                    'city' => $result['city'] ?? null,
                    'city_en' => $result['city'] ?? null,
                    'region' => $result['state_prov'] ?? null,
                    'region_en' => $result['state_prov'] ?? null,
                    'country' => $result['country_name'] ?? null,
                    'country_en' => $result['country_name'] ?? null,
                    'country_iso' => $result['country_name'] ?? null,
                    'latitude' => $result['latitude'] ?? null,
                    'longitude' => $result['longitude'] ?? null,
                ];
            }
        } catch (Exception $e) {
            // Логирование ошибки или обработка исключения
        }
        return null;
    }

    // Метод для сохранения информации о местоположении в базу данных
    private function saveLocationToDatabase($ip, $location)
    {
        if (!empty($location['country_iso'])) {
            $dbCountry = \Jubiks\Geolocation\CountryTable::query()
                ->setSelect(['ID'])
                ->where('ISO_CODE', mb_strtoupper($location['country_iso']))
                ->setLimit(1)
                ->setCacheTtl(3600)
                ->cacheJoins(true)
                ->exec();

            if (!($countryId = intval($dbCountry->fetch()['ID'])) && !empty($location['country'])) {
                if (!(defined('BX_UTF') && BX_UTF === true)) {
                    $location['country'] = mb_convert_encoding($location['country'], "Windows-1251", "UTF-8");
                }
                $fieldsCountry = [
                    'ISO_CODE' => mb_strtoupper($location['country_iso']),
                    'NAME' => $location['country'],
                    'NAME_EN' => $location['country_en'],
                ];

                $result = \Jubiks\Geolocation\CountryTable::add($fieldsCountry);
                if ($result->isSuccess()) {
                    $countryId = $result->getId();
                }
            }
        }

        if ($countryId && !empty($location['region'])) {
            $dbRegion = \Jubiks\Geolocation\RegionTable::query()
                ->setSelect(['ID'])
                ->where('COUNTRY_ID', $countryId)
                ->whereLike('NAME', $location['region'] . '%')
                ->setLimit(1)
                ->setCacheTtl(3600)
                ->cacheJoins(true)
                ->exec();

            if (!($regionId = intval($dbRegion->fetch()['ID']))) {
                if (!(defined('BX_UTF') && BX_UTF === true)) {
                    $location['region'] = mb_convert_encoding($location['region'], "Windows-1251", "UTF-8");
                }
                $fieldsRegion = [
                    'COUNTRY_ID' => $countryId,
                    'NAME' => $location['region'],
                    'NAME_EN' => $location['region_en'],
                ];

                $result = \Jubiks\Geolocation\RegionTable::add($fieldsRegion);
                if ($result->isSuccess()) {
                    $regionId = $result->getId();
                }
            }
        }

        if ($countryId && $regionId && !empty($location['city'])) {
            $dbCity = \Jubiks\Geolocation\CityTable::query()
                ->setSelect(['ID'])
                ->where('COUNTRY_ID', $countryId)
                ->where('REGION_ID', $regionId)
                ->where('NAME', $location['city'])
                ->setLimit(1)
                ->setCacheTtl(3600)
                ->cacheJoins(true)
                ->exec();

            if (!($cityId = intval($dbCity->fetch()['ID']))) {
                if (!(defined('BX_UTF') && BX_UTF === true)) {
                    $location['city'] = mb_convert_encoding($location['city'], "Windows-1251", "UTF-8");
                }
                $fieldsCity = [
                    'COUNTRY_ID' => $countryId,
                    'REGION_ID' => $regionId,
                    'NAME' => $location['city'],
                    'NAME_EN' => $location['city_en'],
                    'LAT' => $location['latitude'],
                    'LON' => $location['longitude'],
                ];

                $result = \Jubiks\Geolocation\CityTable::add($fieldsCity);
                if ($result->isSuccess()) {
                    $cityId = $result->getId();
                }
            }
        }

        if ($countryId && $cityId && $ip) {
            $location = $this->getLocationFromDatabase($ip);
            if (!$location) {
                $fields = [
                    'COUNTRY_ID' => intval($countryId),
                    'REGION_ID' => intval($regionId),
                    'CITY_ID' => intval($cityId),
                    'INET4' => $ip,
                    'INET6' => $ip,
                ];

                \Jubiks\Geolocation\IpAddressTable::add($fields);
            } elseif (
                intval($location['ID']) && (
                    intval($location['COUNTRY_ID']) != intval($countryId) ||
                    intval($location['REGION_ID']) != intval($regionId) ||
                    intval($location['CITY_ID']) != intval($cityId)
                )
            ) {
                $fields = [
                    'COUNTRY_ID' => intval($countryId),
                    'REGION_ID' => intval($regionId),
                    'CITY_ID' => intval($cityId),
                ];

                \Jubiks\Geolocation\IpAddressTable::update($location['ID'], $fields);
            }
        }
    }

    public static function baseUpdater()
    {
        $return = "\GeoLocationService::baseUpdater();";

        self::GeoSxBaseUpdater();

        return $return;
    }

    public static function GeoSxBaseUpdater()
    {
        // Обновление файла базы данных Sypex Geo
        // Настройки
        $url = 'https://sypexgeo.net/files/SxGeoCity_utf8.zip';  // Путь к скачиваемому файлу
        $dat_file_dir = $_SERVER['DOCUMENT_ROOT'] . '/upload/SxGeo'; // Каталог в который сохранять dat-файл
        $last_updated_file = $_SERVER['DOCUMENT_ROOT'] . '/upload/SxGeo/SxGeo.dat'; // Файл в котором хранится дата последнего обновления
        define('INFO', false); // Вывод сообщений о работе, true заменить на false после установки в cron
        // Конец настроек

        $t = microtime(1);

        if (!file_exists($dat_file_dir)) {
            @mkdir($dat_file_dir, BX_DIR_PERMISSIONS, true);
        }

        if (!file_exists($dat_file_dir)) return false;

        chdir($dat_file_dir);
        $types = array(
            'Country' => 'SxGeo.dat',
            'City' => 'SxGeoCity.dat',
            'Max' => 'SxGeoMax.dat',
        );
        // Скачиваем архив
        preg_match("/(Country|City|Max)/", pathinfo($url, PATHINFO_BASENAME), $m);
        $type = $m[1];
        $dat_file = $types[$type];
        if (INFO) echo "Скачиваем архив с сервера\n";

        $fp = fopen($dat_file_dir . '/SxGeoTmp.zip', 'wb');
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_FILE => $fp,
            CURLOPT_HTTPHEADER => file_exists($last_updated_file) ? array("If-Modified-Since: " . file_get_contents($last_updated_file)) : array(),
        ));
        if (!curl_exec($ch)) die ('Ошибка при скачивании архива');
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        if ($code == 304) {
            @unlink($dat_file_dir . '/SxGeoTmp.zip');
            if (INFO) echo "Архив не обновился, с момента предыдущего скачивания\n";
            return true;
        }

        if (INFO) echo "Архив с сервера скачан\n";
        // Распаковываем архив
        $fp = fopen('zip://' . $dat_file_dir . '/SxGeoTmp.zip#' . $dat_file, 'rb');
        $fw = fopen($dat_file, 'wb');
        if (!$fp) {
            return false;
        }
        if (INFO) echo "Распаковываем архив\n";
        stream_copy_to_stream($fp, $fw);
        fclose($fp);
        fclose($fw);
        if (filesize($dat_file) == 0) return false;
        @unlink($dat_file_dir . '/SxGeoTmp.zip');
        if (!rename($dat_file_dir . DIRECTORY_SEPARATOR . $dat_file, $dat_file_dir . DIRECTORY_SEPARATOR . $dat_file)) return false;
        file_put_contents($last_updated_file, gmdate('D, d M Y H:i:s') . ' GMT');
        if (INFO) echo "Перемещен файл в {$dat_file_dir}{$dat_file}\n";
        return true;
    }
}

