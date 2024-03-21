<?php

namespace UnitM\Solute\Core;

interface SoluteCreateTableSql
{
    public const UM_SOLUTE_CREATE_TABLE_GROUPS = <<<HEREDOC
CREATE TABLE IF NOT EXISTS `um_solute_attribute_groups` (
  `OXID` varchar(32) COLLATE 'latin1_general_ci' NOT NULL COMMENT '= md5(OXTITLE)',
  `UM_SORT` int(11) NOT NULL,
  `UM_TITLE` varchar(128) NOT NULL COMMENT 'title of attribute group, multilanguage field',
  `UM_TITLE_1` varchar(128) NOT NULL COMMENT 'title of attribute group, multilanguage field',
  PRIMARY KEY (`OXID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
HEREDOC;

    public const UM_SOLUTE_CREATE_TABLE_SCHEMA = <<<HEREDOC
CREATE TABLE IF NOT EXISTS `um_solute_attribute_schema` (
  `OXID` varchar(32) COLLATE 'latin1_general_ci' NOT NULL COMMENT '= md5(UM_NAME_PRIMARY)',
  `UM_NAME_PRIMARY` varchar(64) NOT NULL,
  `UM_NAME_ALTERNATIVE` varchar(64) DEFAULT NULL,
  `UM_NAME_THIRD` varchar(64) DEFAULT NULL,
  `UM_ATTRIBUTE_GROUP_ID` varchar(32) COLLATE 'latin1_general_ci' DEFAULT NULL COMMENT '= um_solute_attribute_group.OXID',
  `UM_SORT` int(11) DEFAULT NULL,
  `UM_REQUIRED` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'true(1): item is required, false(0): optional item',
  `UM_VALID_VALUES` json DEFAULT NULL COMMENT 'if there are only some valid values, e.g. yes/no or new/refurbished/used',
  `UM_VALIDATOR` json NOT NULL COMMENT 'regex expression to check the value for validity',
  `UM_DESCRIPTION` text COMMENT 'Description for this item definition to display for shop user. Multilanguage field.',
  `UM_DESCRIPTION_1` text COMMENT 'Description for this item definition to display for shop user. Multilanguage field',
  PRIMARY KEY (`OXID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
HEREDOC;

    public const UM_SOLUTE_CREATE_TABLE_MAPPING = <<<HEREDOC
CREATE TABLE IF NOT EXISTS `um_solute_attribute_mapping` (
  `OXID` varchar(32) COLLATE 'latin1_general_ci' NOT NULL,
  `UM_OBJECT_ID` varchar(32) COLLATE 'latin1_general_ci' NOT NULL COMMENT 'Possible values: oxarticles.OXID or oxcategories.OXID. If empty = shopwide mapping',
  `UM_SHOP_ID` int(11) NOT NULL COMMENT '= oxshops.OXID',
  `UM_ATTRIBUTE_ID` varchar(32) COLLATE 'latin1_general_ci' NOT NULL COMMENT '= um_solute_attribute_schema.OXID',
  `UM_DATA_RESSOURCE_ID` varchar(32) COLLATE 'latin1_general_ci' NOT NULL COMMENT '= um_solute_field_selection.OXID',
  `UM_MANUAL_VALUE` text NOT NULL COMMENT 'values in this field overwrites mapping from UM_DATARESSOURCE_ID.',
  `UM_MANUAL_VALUE_1` text NOT NULL COMMENT 'values in this field overwrites mapping from UM_DATARESSOURCE_ID. Multilanguage field',
  PRIMARY KEY (`OXID`),
  UNIQUE KEY `UM_OBJECTID` (`UM_OBJECT_ID`,`UM_SHOP_ID`,`UM_ATTRIBUTE_ID`,`UM_DATA_RESSOURCE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
HEREDOC;

    public const UM_SOLUTE_CREATE_TABLE_FIELD_SELECTION = <<<HEREDOC
CREATE TABLE IF NOT EXISTS `um_solute_field_selection` (
  `OXID` varchar(32) COLLATE 'latin1_general_ci' NOT NULL,
  `UM_SHOP_ID` int(11) NOT NULL COMMENT '= oxshops.OXID',
  `UM_DATA_RESSOURCE` json NOT NULL,
  `UM_ATTRIBUTE_GROUP_ID` varchar(32) COLLATE 'latin1_general_ci' NOT NULL COMMENT '=um_solute_attribute_group.OXID',
  `UM_FIELD_TITLE` varchar(255) NOT NULL COMMENT 'multilanguage field',
  `UM_FIELD_TITLE_1` varchar(255) NOT NULL COMMENT 'multilanguage field',
  PRIMARY KEY (`OXID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
HEREDOC;

    public const UM_SOLUTE_CREATE_TABLE_LOG = <<<HEREDOC
CREATE TABLE IF NOT EXISTS `um_solute_log` (
  `OXID` varchar(32) COLLATE 'latin1_general_ci' NOT NULL COMMENT '= oxarticles.OXID',
  `UM_LOG` json NOT NULL COMMENT 'response data from validation or request to solute',
  PRIMARY KEY (`OXID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
HEREDOC;

    public const UM_SOLUTE_CREATE_TABLE_HASH = <<<HEREDOC
CREATE TABLE IF NOT EXISTS `um_solute_hash` (
  `OXID` varchar(32) COLLATE 'latin1_general_ci' NOT NULL COMMENT '= oxarticles.OXID',
  `UM_FEED_HASH` varchar(32) COLLATE 'latin1_general_ci' NOT NULL COMMENT 'md5 feed values',
  PRIMARY KEY (`OXID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
HEREDOC;
}
