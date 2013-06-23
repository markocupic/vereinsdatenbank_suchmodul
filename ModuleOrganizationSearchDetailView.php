<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 * Formerly known as TYPOlight Open Source CMS.
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 * PHP version 5
 * @copyright  Marko Cupic 2013
 * @author     Marko Cupic <m.cupic@gmx.ch>
 * @package    OrganizationSearch
 * @license    LGPL
 * @filesource
 */

class ModuleOrganizationSearchDetailView extends Module
{
    protected $strTemplate = 'mod_organization_search_detail_view';

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ORGANISATIONEN-SUCHE-DETAILANSICHT ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();
        }
        return parent::generate();
    }

    protected function compile()
    {
        $this->loadLanguageFile('tl_member');
        $this->loadDataContainer('tl_member');

        $objMember = $this->Database->prepare('SELECT * FROM tl_member WHERE vdb_belongs_to_vdb=? AND id=? LIMIT 0,1')->execute(true, $this->Input->get('show'));
        $arrMember = $objMember->fetchAssoc();

        // all fields
        $this->Template->arrRecord = $arrMember;

        // add sections
        $arrSections = array();
        foreach ($arrMember as $k => $v) {
            if (strpos($k, 'db_bereich_')) {
                if ($v) {
                    $arrSections[] = $GLOBALS['TL_LANG']['tl_member'][$k][0];
                }
            }
        }
        $this->Template->arrSections = $arrSections;
        $this->Template->strSections = implode('<br>', $arrSections);

        // add image
        if (is_file(TL_ROOT . '/' . $arrMember['vdb_bild'])) {
            $objFile = new File($arrMember['vdb_bild']);
            if ($objFile->isGdImage) {
                $this->Template->image = $this->generateImage($this->getImage($arrMember['vdb_bild'], 200, 300));
            }
        }
    }


}