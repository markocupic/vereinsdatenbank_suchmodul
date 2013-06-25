-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

-- --------------------------------------------------------

--
-- Table `tl_module`
--

CREATE TABLE `tl_module` (
  `vdb_viewable_fields` blob NULL,
  `vdb_showMap` char(1) NOT NULL default '',
  `vdb_mapHeight` smallint(5) unsigned NOT NULL default '0',
  `vdb_mapWidth` smallint(5) unsigned NOT NULL default '0',

) ENGINE=MyISAM DEFAULT CHARSET=utf8;



