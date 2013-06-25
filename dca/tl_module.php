<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
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
 * @copyright  Marko Cupic 2013
 * @author     Marko Cupic <m.cupic@gmx.ch>
 * @package    OrganizationSearch
 * @license    LGPL
 * @filesource
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['vereinsuche'] = '{title_legend},name,headline,type;{map_legend},vdb_showMap;{config_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['vereinsuche_detail_view'] = '{title_legend},name,headline,type;{map_legend},vdb_showMap;{config_legend},vdb_viewable_fields;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'vdb_showMap';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['vdb_showMap'] = 'vdb_mapWidth,vdb_mapHeight';


$GLOBALS['TL_DCA']['tl_module']['fields']['vdb_viewable_fields'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['vdb_viewable_fields'],
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'options_callback' => array('tl_module_vereinsdatenbank_suchmodul', 'getViewableMemberProperties'),
    'eval' => array('mandatory' => true, 'multiple' => true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['vdb_showMap'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['vdb_showMap'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true, 'tl_class' => 'clr')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['vdb_mapWidth'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['vdb_mapWidth'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50', 'rgxp' => 'digit')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['vdb_mapHeight'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['vdb_mapHeight'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50', 'rgxp' => 'digit')
);

/**
 * Class tl_module_memberlist
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2008-2011
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Controller
 */
class tl_module_vereinsdatenbank_suchmodul extends Backend
{

    /**
     * Return all editable fields of table tl_member
     * @return array
     */
    public function getViewableMemberProperties()
    {
        $options = array();

        $this->loadLanguageFile('tl_member');
        $this->loadDataContainer('tl_member');

        foreach ($GLOBALS['TL_DCA']['tl_member']['fields'] as $k => $v) {
            if ($k == 'password' || $k == 'newsletter' || $k == 'publicFields' || $k == 'allowEmail') {
                continue;
            }

            if ($v['eval']['feViewable']) {
                $options[$k] = $GLOBALS['TL_DCA']['tl_member']['fields'][$k]['label'][0];
            }
        }

        return $options;
    }
}
