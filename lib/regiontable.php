<?php
namespace Jubiks\Geolocation;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class RegionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> COUNTRY_ID int mandatory
 * <li> NAME string(255) mandatory
 * <li> NAME_EN string(255) optional
 * </ul>
 *
 * @package Bitrix\Geolocation
 **/

class RegionTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'jubiks_geolocation_region';
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
                    'title' => Loc::getMessage('REGION_ENTITY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'COUNTRY_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('REGION_ENTITY_COUNTRY_ID_FIELD')
                ]
            ),
            new StringField(
                'NAME',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateName'],
                    'title' => Loc::getMessage('REGION_ENTITY_NAME_FIELD')
                ]
            ),
            new StringField(
                'NAME_EN',
                [
                    'validation' => [__CLASS__, 'validateNameEn'],
                    'title' => Loc::getMessage('REGION_ENTITY_NAME_EN_FIELD')
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
}