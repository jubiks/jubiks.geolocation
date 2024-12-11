CREATE TABLE IF NOT EXISTS jubiks_geolocation_ip_address (
  ID INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  INET4 INT(11) UNSIGNED DEFAULT NULL,
  INET6 VARBINARY(16) NOT NULL,
  COUNTRY_ID INT(11) UNSIGNED NOT NULL,
  REGION_ID INT(11) UNSIGNED NOT NULL,
  CITY_ID INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (ID)
)
ENGINE = INNODB,
CHARACTER SET cp1251,
COLLATE cp1251_general_ci,
ROW_FORMAT = DYNAMIC;

CREATE TABLE IF NOT EXISTS jubiks_geolocation_country (
  ID INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  ISO_CODE CHAR(2) NOT NULL,
  NAME VARCHAR(255) NOT NULL,
  NAME_EN VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (ID)
)
ENGINE = INNODB,
CHARACTER SET cp1251,
COLLATE cp1251_general_ci,
ROW_FORMAT = DYNAMIC;

CREATE TABLE IF NOT EXISTS jubiks_geolocation_region (
  ID INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  COUNTRY_ID INT(11) UNSIGNED NOT NULL,
  NAME VARCHAR(255) NOT NULL,
  NAME_EN VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (ID)
)
ENGINE = INNODB,
CHARACTER SET cp1251,
COLLATE cp1251_general_ci,
ROW_FORMAT = DYNAMIC;

CREATE TABLE IF NOT EXISTS jubiks_geolocation_city (
  ID INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  COUNTRY_ID INT(11) UNSIGNED NOT NULL,
  REGION_ID INT(11) UNSIGNED DEFAULT NULL,
  NAME VARCHAR(255) NOT NULL,
  NAME_EN VARCHAR(255) DEFAULT NULL,
  LAT VARCHAR(12) DEFAULT NULL,
  LON VARCHAR(12) DEFAULT NULL,
  PRIMARY KEY (ID)
)
ENGINE = INNODB,
CHARACTER SET cp1251,
COLLATE cp1251_general_ci,
ROW_FORMAT = DYNAMIC;


-- Проверка существования индекса IDX_jubiks_geolocation_ip_address_INET4
SET @index_exists = (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'jubiks_geolocation_ip_address'
    AND index_name = 'IDX_jubiks_geolocation_ip_address_INET4'
);

-- Если индекс не существует, создаем его
SET @create_index_sql = IF(@index_exists = 0,
  'ALTER TABLE jubiks_geolocation_ip_address ADD INDEX IDX_jubiks_geolocation_ip_address_INET4(INET4);',
  'SELECT "Index IDX_jubiks_geolocation_ip_address_INET4 already exists";');

PREPARE stmt FROM @create_index_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Проверка существования уникального индекса UK_jubiks_geolocation_ip_address_INET6
SET @unique_index_exists = (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'jubiks_geolocation_ip_address'
    AND index_name = 'UK_jubiks_geolocation_ip_address_INET6'
);

-- Если уникальный индекс не существует, создаем его
SET @create_unique_index_sql = IF(@unique_index_exists = 0,
  'ALTER TABLE jubiks_geolocation_ip_address ADD UNIQUE INDEX UK_jubiks_geolocation_ip_address_INET6(INET6);',
  'SELECT "Unique index UK_jubiks_geolocation_ip_address_INET6 already exists";');

PREPARE stmt FROM @create_unique_index_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;