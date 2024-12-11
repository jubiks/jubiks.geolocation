<?php
namespace Jubiks\Geolocation;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class CityTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> COUNTRY_ID int mandatory
 * <li> REGION_ID int optional
 * <li> NAME string(255) mandatory
 * <li> NAME_EN string(255) optional
 * <li> LAT string(12) optional
 * <li> LON string(12) optional
 * </ul>
 *
 * @package Bitrix\Geolocation
 **/

class CityTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'jubiks_geolocation_city';
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
                    'title' => Loc::getMessage('CITY_ENTITY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'COUNTRY_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('CITY_ENTITY_COUNTRY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'REGION_ID',
                [
                    'title' => Loc::getMessage('CITY_ENTITY_REGION_ID_FIELD')
                ]
            ),
            new StringField(
                'NAME',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateName'],
                    'title' => Loc::getMessage('CITY_ENTITY_NAME_FIELD')
                ]
            ),
            new StringField(
                'NAME_EN',
                [
                    'validation' => [__CLASS__, 'validateNameEn'],
                    'title' => Loc::getMessage('CITY_ENTITY_NAME_EN_FIELD')
                ]
            ),
            new StringField(
                'LAT',
                [
                    'validation' => [__CLASS__, 'validateLat'],
                    'title' => Loc::getMessage('CITY_ENTITY_LAT_FIELD')
                ]
            ),
            new StringField(
                'LON',
                [
                    'validation' => [__CLASS__, 'validateLon'],
                    'title' => Loc::getMessage('CITY_ENTITY_LON_FIELD')
                ]
            ),
        ];
    }

    /**
     * Returns validators for NAME field.
     *
     * @return array
     */
    public static function validateName()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for NAME_EN field.
     *
     * @return array
     */
    public static function validateNameEn()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for LAT field.
     *
     * @return array
     */
    public static function validateLat()
    {
        return [
            new LengthValidator(null, 12),
        ];
    }

    /**
     * Returns validators for LON field.
     *
     * @return array
     */
    public static function validateLon()
    {
        return [
            new LengthValidator(null, 12),
        ];
    }
}