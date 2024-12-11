<?php
namespace Jubiks\Geolocation;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator,
    Bitrix\Main\DB\SqlExpression;

Loc::loadMessages(__FILE__);

/**
 * Class IpAddressTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> INET4 int optional
 * <li> INET6 string(16) mandatory
 * <li> COUNTRY_ID int mandatory
 * <li> REGION_ID int mandatory
 * <li> CITY_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Geolocation
 **/

class IpAddressTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'jubiks_geolocation_ip_address';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('IP_ADDRESS_ENTITY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'INET4',
                [
                    'title' => Loc::getMessage('IP_ADDRESS_ENTITY_INET4_FIELD')
                ]
            ),
            new StringField(
                'INET6',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateInet6'],
                    'title' => Loc::getMessage('IP_ADDRESS_ENTITY_INET6_FIELD')
                ]
            ),
            new IntegerField(
                'COUNTRY_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('IP_ADDRESS_ENTITY_COUNTRY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'REGION_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('IP_ADDRESS_ENTITY_REGION_ID_FIELD')
                ]
            ),
            new IntegerField(
                'CITY_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('IP_ADDRESS_ENTITY_CITY_ID_FIELD')
                ]
            ),
        ];
    }

    /**
     * Returns validators for INET6 field.
     *
     * @return array
     */
    public static function validateInet6()
    {
        return [
            new LengthValidator(null, 16),
        ];
    }

    // Переопределяем метод add для обработки IP-адреса
    public static function add(array $data)
    {
        if (isset($data['INET4'])) {
            $ip = $data['INET4'];
            // Проверяем, является ли IP адрес IPv4
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                // Преобразуем IPv4 адрес
                $data['INET4'] = new SqlExpression('INET_ATON(?)', $ip);
            } else {
                $data['INET4'] = null;
            }
        }

        if (isset($data['INET6'])) {
            $ip = $data['INET6'];
            // Проверяем, является ли IP адрес IPv4 или IPv6
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                // Преобразуем IPv4 или IPv6 адрес
                $data['INET6'] = new SqlExpression('INET6_ATON(?)', $ip);
            } else {
                $data['INET6'] = null;
            }
        }

        return parent::add($data);
    }

    // Метод для выборки по IP
    public static function getByIp($ip)
    {
        $query = self::query();
        $selectFields = [
            'ID',
            'COUNTRY_ID',
            'REGION_ID',
            'CITY_ID',
            'city' => 'GEO_CITY.NAME',
            'city_en' => 'GEO_CITY.NAME_EN',
            'region' => 'GEO_REGION.NAME',
            'region_en' => 'GEO_REGION.NAME_EN',
            'country' => 'GEO_COUNTRY.NAME',
            'country_en' => 'GEO_COUNTRY.NAME_EN',
            'country_iso' => 'GEO_COUNTRY.ISO_CODE',
            'latitude' => 'GEO_CITY.LAT',
            'longitude' => 'GEO_CITY.LON',
        ];
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // IPv4
            //$selectFields['IP_ADDRESS'] = new SqlExpression('INET_NTOA(?s)', 'INET4');
            $query->where('INET4',new SqlExpression('INET_ATON(?s)', $ip));
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // IPv6
            //$selectFields['IP_ADDRESS'] = new SqlExpression('INET6_NTOA(?s)', 'INET6');
            $query->where('INET6',new SqlExpression('INET6_ATON(?s)', $ip));
        } else {
            throw new \InvalidArgumentException("Invalid IP address format: $ip");
        }
        //d($selectFields); die;
        $query->setSelect($selectFields);

        $query->registerRuntimeField('GEO_COUNTRY', [
            'data_type' => CountryTable::getEntity(),
            'reference' => ['=this.COUNTRY_ID' => 'ref.ID'],
        ]);
        $query->registerRuntimeField('GEO_REGION', [
            'data_type' => RegionTable::getEntity(),
            'reference' => ['=this.REGION_ID' => 'ref.ID'],
        ]);
        $query->registerRuntimeField('GEO_CITY', [
            'data_type' => CityTable::getEntity(),
            'reference' => ['=this.CITY_ID' => 'ref.ID'],
        ]);
        $query->setLimit(1);
        $query->setCacheTtl(3600);
        $query->cacheJoins(true);

        return $query->exec();
    }
}