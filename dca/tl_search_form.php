<?php


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
    'options' => $options,
    'reference' => $reference,
    'eval' => array('multiple' => true, 'tl_class' => '')
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
    'options' => array('10', '20', '30', '40', '50', '50'),
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




