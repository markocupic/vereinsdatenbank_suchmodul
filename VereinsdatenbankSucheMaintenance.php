<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    VereinsdatenbankSuchmodul
 * @filesource
 */


/**
 * Class VereinsdatenbankSucheMaintenance
 *
 * Provide methods regarding club properties.
 * @copyright  Marko Cupic 2013
 * @author     m.cupic@gmx.ch
 * @package    VereinsdatenbankSuchmodul
 *
 * requires mysql MyISAM Table and MySQL > 4.0.1
 * ft_min_word_len=3
 * take care about stopwords -> http://dev.mysql.com/doc/refman/5.1/de/fulltext-stopwords.html
 */
class VereinsdatenbankSucheMaintenance extends System
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @param $strContent
     * @param $strTemplate
     * @return mixed
     */
    public function addFulltextKey($strContent, $strTemplate)
    {
        $this->import('Database');

        // check for MySQL ver. > 4.0.1
        $objVer = $this->Database->query("SHOW VARIABLES LIKE 'version'");
        if (version_compare($objVer->first()->Value, '4.0.1', '<')) {
            die('Sorry, the extension requires MySQL > 4.0.1');
        }

        // add FULLTEXT KEY to tl_member
        try {
            $this->Database->query('ALTER TABLE tl_member DROP INDEX `vdb_vereinsdatenbank_suche`');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $this->arrSearchableFields = array(vdb_vereinsname, firstname, lastname, city, vdb_taetigkeitsmerkmale, vdb_taetigkeitsmerkmale_zweitsprache, vdb_egagiert_fuer, vdb_egagiert_fuer_zweitsprache, vdb_besondere_aktion, vdb_besondere_aktion_zweitsprache);
        $objKey = $this->Database->prepare('SHOW INDEX FROM tl_member WHERE Key_name=?')->execute('vdb_vereinsdatenbank_suche');
        if (!$objKey->numRows) {
            $this->Database->query('ALTER TABLE tl_member ADD FULLTEXT KEY `vdb_vereinsdatenbank_suche` (' . implode(',', $this->arrSearchableFields) . ')');
        }

        return $strContent;
    }

    /**
     * @param $strContent
     * @param $strTemplate
     * @return mixed
     */
    public function addStoredProcedure($strContent, $strTemplate)
    {
        $this->import('Database');
        try {
            // first drop stored procedure and then recreate it
            $this->Database->query("DROP FUNCTION IF EXISTS GoogleDistance_KM;");
            $this->Database->query("
                CREATE FUNCTION `GoogleDistance_KM`(
                    geo_breitengrad_p1 double,
                    geo_laengengrad_p1 double,
                    geo_breitengrad_p2 double,
                    geo_laengengrad_p2 double ) RETURNS double
                    RETURN (6371 * acos( cos( radians(geo_breitengrad_p2) ) * cos( radians( geo_breitengrad_p1 ) )
                    * cos( radians( geo_laengengrad_p1 ) - radians(geo_laengengrad_p2) )
                    + sin( radians(geo_breitengrad_p2) ) * sin( radians( geo_breitengrad_p1 ) ) )
                );
            ");
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $strContent;
    }
}
