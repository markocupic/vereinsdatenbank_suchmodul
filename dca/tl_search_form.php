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
 * Table tl_member
 */
$GLOBALS['TL_DCA']['tl_search_form']['fields']['tags'] = array(
    'label' => 'Stichwortsuche',
    'inputType' => 'text',
    'eval' => array('mandatory' => false, 'maxlength' => 255, 'tl_class' => '', 'placeholder' => &$GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['tags'])
);

$GLOBALS['TL_DCA']['tl_search_form']['fields']['postal'] = array(
    'label' => 'Postleitzahlenbereich',
    'inputType' => 'text',
    'eval' => array('mandatory' => false, 'maxlength' => 5, 'tl_class' => '', 'placeholder' => &$GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['plz'])
);


// Categories
$options = array();
$reference = array();
$i = 0;
foreach ($GLOBALS['TL_DCA']['tl_member']['fields']['categories'] as $field) {
    $reference[$i] = strlen($GLOBALS['TL_LANG']['tl_member'][$field][0]) ? $GLOBALS['TL_LANG']['tl_member'][$field][0] : $field;
    $options[] = $i;
    $i++;
}
$GLOBALS['TL_DCA']['tl_search_form']['fields']['cat'] = array(
    'inputType' => 'checkbox',
    'default' => -1,
    'options' => $options,
    'reference' => $reference,
    'eval' => array('multiple' => true, 'includeBlankOption' => false, 'tl_class' => '', )
);

$GLOBALS['TL_DCA']['tl_search_form']['fields']['search'] = array(
    'inputType' => 'checkbox',
    'options' => array('AND'),
    'reference' => array('AND' => $GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['msg_1']),
    'eval' => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_search_form']['fields']['country'] = array(
    'inputType' => 'checkbox',
    'options' => array('ch', 'de', 'fr'),
    'reference' => array('ch' => $GLOBALS['TL_LANG']['CNT']['ch'], 'de' => $GLOBALS['TL_LANG']['CNT']['de'], 'fr' => $GLOBALS['TL_LANG']['CNT']['fr']),
    'eval' => array('tl_class' => '')
);


$GLOBALS['TL_DCA']['tl_search_form']['fields']['address'] = array(
    'label' => &$GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['address'],
    'inputType' => 'text',
    'eval' => array('mandatory' => false, 'maxlength' => 255, 'tl_class' => '', 'paceholder' => 'Ort, Land')
);

$GLOBALS['TL_DCA']['tl_search_form']['fields']['radius'] = array(
    'label' => &$GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['radius'],
    'inputType' => 'select',
    'options' => array('0' => &$GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['all'], '5' => '5 km', '10' => '10 km', '15' => '15 km', '20' => '20 km', '25' => '25 km', '30' => '30 km', '40' => '40 km', '50' => '50 km', '75' => '75 km', '100' => '100 km', '150' => '150 km', '200' => '200 km', '300' => '300 km', '400' => '400 km', '500' => '500 km'),
    'eval' => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_search_form']['fields']['limit'] = array(
    'inputType' => 'select',
    'options' => array('10', '20', '30', '40', '50', '100'),
    'eval' => array('tl_class' => '', 'labelInline' => 'after')
);

$GLOBALS['TL_DCA']['tl_search_form']['fields']['direction'] = array(
    'inputType' => 'select',
    'options' => array('asc', 'desc'),
    'eval' => array('tl_class' => '', 'labelInline' => 'after')
);


$GLOBALS['TL_DCA']['tl_search_form']['fields']['orderby'] = array(
    'inputType' => 'select',
    'options' => array('name' => 'Name Verein', 'postal' => 'PLZ', 'phone' => 'Telefonnummer', 'since' => 'Gründungsdatum', 'personen' => 'aktiv engagierte Mitglieder'),
    'eval' => array('tl_class' => '',  'labelInline' => 'after')
);


?>