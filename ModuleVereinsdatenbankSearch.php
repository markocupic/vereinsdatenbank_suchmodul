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
/**
 * requires mysql MyISAM Table and MySQL > 4.0.1
 * ft_min_word_len=3
 * take care about stopwords -> http://dev.mysql.com/doc/refman/5.1/de/fulltext-stopwords.html
 */
class ModuleVereinsdatenbankSearch extends Module
{
    protected $strTemplate = 'mod_organization_search';

    public function generate()
    {
        $this->intStartTstamp = microtime();
        // check for MySQL ver. > 4.0.1
        $objVer = $this->Database->query("SHOW VARIABLES LIKE 'version'");
        if (version_compare($objVer->first()->Value, '4.0.1', '<')) {
            die('Sorry, the extension requires MySQL > 4.0.1');
        }

        // add FULLTEXT KEY to tl_member
        try {
            $this->Database->query('ALTER TABLE tl_member DROP INDEX `vdb_vereinsdatenbank_suche`');
        } catch (Exception $e) {
        }
        $this->arrSearchableFields = array(vdb_vereinsname, firstname, lastname, city, vdb_taetigkeitsmerkmale, vdb_taetigkeitsmerkmale_zweitsprache, vdb_egagiert_fuer, vdb_egagiert_fuer_zweitsprache, vdb_besondere_aktion, vdb_besondere_aktion_zweitsprache);
        $objKey = $this->Database->prepare('SHOW INDEX FROM tl_member WHERE Key_name=?')->execute('vdb_vereinsdatenbank_suche');
        if (!$objKey->numRows) {
            $this->Database->query('ALTER TABLE tl_member ADD FULLTEXT KEY `vdb_vereinsdatenbank_suche` (' . implode(',', $this->arrSearchableFields) . ')');
        }

        // first drop stored procedure and the recreate it
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



        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ORGANISATIONEN-SUCHE ###';
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

        // auto detect vdb_lat_coord && vdb_lng_coord
        if (function_exists('curl_init')) {
            $objMember = $this->Database->execute("SELECT * FROM tl_member WHERE vdb_belongs_to_vdb=true AND (vdb_lat_coord='' OR vdb_lng_coord='')");
            while ($objMember->next()) {
                $strAddress = str_replace(' ', '+', $objMember->street) . ',+' . str_replace(' ', '+', $objMember->city) . ',+' . $objMember->country;
                $arrPos = $this->curl_getCoord(sprintf('http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false', $strAddress));
                if (is_array($arrPos[results][0]['geometry'])) {
                    $latPos = $arrPos[results][0]['geometry']['location']['lat'];
                    $lngPos = $arrPos[results][0]['geometry']['location']['lng'];
                    $set = array(
                        'vdb_lat_coord' => $latPos,
                        'vdb_lng_coord' => $lngPos
                    );
                    $this->Database->prepare("UPDATE tl_member %s WHERE id=?")->set($set)->executeUncached($objMember->id);
                }
            }
        }

        $this->loadLanguageFile('tl_member');
        $this->loadDataContainer('tl_member');

        // get lat & lng & address
        $strLat = strlen($this->Input->get('lat')) ? $this->Input->get('lat') : '';
        $strLng = strlen($this->Input->get('lng')) ? $this->Input->get('lng') : '';
        $strAddress = strlen($this->Input->get('address')) ? $this->Input->get('address') : '';

        // get radius
        $intRadius = $this->Input->get('radius') > 0 ? (int)$this->Input->get('radius') : 1000000;

        // get $arrCategories
        $arrCategoriesNum = is_array($this->Input->get('cat')) ? $this->Input->get('cat') : array();
        $arrCategories = array();
        foreach ($arrCategoriesNum as $index) {
            $arrCategories[] = $GLOBALS['TL_DCA']['tl_member']['fields']['categories'][$index];
        }

        // get $arrCountries
        $arrCountries = is_array($this->Input->get('country')) ? $this->Input->get('country') : array();

        // get $strPostal
        $strPostal = strlen(trim($this->Input->get('postal'))) ? trim($this->Input->get('postal')) : '';

        // get tags
        $strTags = strlen(trim($this->Input->get('tags'))) ? trim($this->Input->get('tags')) : '';

        // get limit
        $limit = strlen($this->Input->get('limit')) ? $this->Input->get('limit') : 10;

        // get sorting direction
        $direction = strlen($this->Input->get('direction')) ? $this->Input->get('direction') : 10;

        // get OrderBy field
        $arrOrderBy = array(
            'name' => 'vdb_vereinsname',
            'postal' => 'postal',
            'phone' => 'phone',
            'since' => 'vdb_gruendungsdatum',
            'personen' => 'vdb_aktiv_engagierte_mitglieder'
        );
        $orderBy = strlen($this->Input->get('orderby')) ? $arrOrderBy[$this->Input->get('orderby')] : 'vdb_vereinsname';


        // if the form has been submitted
        // if ($this->Input->get('submit') && (count($arrCategories) or count($arrCountries) or strlen($strPostal) or strlen($strTags))) {
        if ($this->Input->get('submit')) {

            $strOperator = strlen($this->Input->get('search')) ? $this->Input->get('search') : 'OR';

            // build the query string
            $strQuery = "SELECT * FROM tl_member WHERE vdb_belongs_to_vdb=true AND disable='' AND ";

            // radius filter
            if (strlen($intRadius) && strlen($strLat) && strlen($strLng)) {
                // GoogleDistance_km(geo_breitengrad_punktA, geo_laengengrad_punktA, geo_breitengrad_punktB, geo_laengengrad_punktB) <= 100
                $strQuery .= sprintf("vdb_lat_coord > 0 AND vdb_lng_coord > 0 AND GoogleDistance_km(vdb_lat_coord, vdb_lng_coord, '%s', '%s') <= '%s' AND ", $strLat, $strLng, $intRadius);
            }

            // tag search
            if (strlen($strTags)) {
                $strQuery .= "MATCH (" . implode(',', $this->arrSearchableFields) . ") AGAINST ('" . $strTags . "' IN BOOLEAN MODE) AND ";
            }

            // postal code
            if (strlen($strPostal)) {
                $strQuery .= "postal LIKE '" . $strPostal . "%' AND ";
            }

            // country search
            if (count($arrCountries)) {
                $strQuery .= "country IN ('" . strtolower(implode("', '", $arrCountries)) . "') AND ";
            }

            // category filter
            if (count($arrCategories)) {
                $strQuery .= "(" . implode('=1 ' . $strOperator . ' ', $arrCategories) . "=1)";
                //$param_1 = array_fill(0, count($arrCategories), true);
            }

            // clean the query string
            $strQuery = preg_replace('/(WHERE|AND)$/', '', trim($strQuery));

            // order by
            $strQuery .= ' ORDER BY ' . $orderBy . ' ' . strtoupper($direction);

            // launch query for the first time for counting the results
            $objMembers = $this->Database->query($strQuery);
            $items = $objMembers->numRows;

            // launch query for the second time (for the selected pagination page)
            $objMembers = $this->Database->prepare($strQuery);
            if ($limit > 0) {
                $page = $this->Input->get('page') ? $this->Input->get('page') : 1;
                $offset = ($page - 1) * $limit;
                $objMembers = $objMembers->limit($limit, $offset);
            }
            $objMembers = $objMembers->execute();

            // save the query in $strQuery for debug usage
            $strQuery = $objMembers->query;

            if (!$items) {
                $this->Template->messageType = 'empty';
                $this->Template->message = $GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['no_results_found'];
            } else {
                // add pagination menu
                $objPagination = new Pagination($items, $limit);
                $this->Template->pagination = $objPagination->generate("\n ");

                // create the js-object and the result array
                $i = 0;
                $arrListableFields = strlen($this->vdb_viewable_fields) ? unserialize($this->vdb_viewable_fields) : array();
                $arrResults = array();
                $customMarker = is_file(TL_ROOT . '/' . $this->vdb_customMarker) ? $this->Environment->url . '/' . $this->vdb_customMarker : '';
                $memberCoord = '<script>' . "\r\n";
                $memberCoord .= 'objCoord = {' . "\r\n";
                while ($objMembers->next()) {
                    $memberCoord .= sprintf("'%s': {'street': '%s', 'city': '%s', 'country': '%s', 'title': '%s', 'lat': '%s', 'lng': '%s', 'url': '%s', 'marker': '%s'},", $i, str_replace("'", "`", $objMembers->street), str_replace("'", "`", $objMembers->city), $objMembers->country, str_replace("'", "`", $objMembers->vdb_vereinsname), $objMembers->vdb_lat_coord, $objMembers->vdb_lng_coord, sprintf($this->getJumpToHref(), $objMembers->id), $customMarker) . "\r\n";
                    $arrResults[$i]['id'] = $objMembers->id;
                    foreach ($arrListableFields as $field) {
                        $arrResults[$i][$field] = $objMembers->$field;
                    }
                    $i++;
                }
                $memberCoord .= '};' . "\r\n";
                $memberCoord .= '</script>' . "\r\n";
            }
        }
        $this->loadLanguageFile('tl_member');
        $this->loadDataContainer('tl_member');
        $this->loadLanguageFile('tl_search_form');
        $this->loadDataContainer('tl_search_form');
        // Build fields
        $fields = array();
        foreach ($GLOBALS['TL_DCA']['tl_search_form']['fields'] as $fieldname => $arrData) {
            // Map checkboxWizard to regular checkbox widget
            if ($arrData['inputType'] == 'checkboxWizard') {
                $arrData['inputType'] = 'checkbox';
            }


            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

            // Continue if the class is not defined
            if (!$this->classFileExists($strClass)) {
                continue;
            }
            $objWidget = new $strClass($this->prepareForWidget($arrData, $fieldname));
            $objWidget->tableless = true;

            // Catch the value from  $_GET[$fieldname]
            if ($this->Input->get($fieldname)) {
                $objWidget->value = is_array($this->Input->get($fieldname)) ? serialize($this->Input->get($fieldname)) : $this->Input->get($fieldname);
            }
            // Set the placeholder property
            if ($arrData['eval']['placeholder']) {
                $objWidget->placeholder = $arrData['eval']['placeholder'];
            }
            // Add the legend
            if ($arrData['legend']) {
                $objWidget->legend = $arrData['legend'];
            }

            // add the label before or after the formfield
            if ($arrData['eval']['labelInline']) {
                if ($arrData['eval']['labelInline'] == 'after') {
                    $temp = $objWidget->generate();
                    $temp .= strlen($objWidget->label) ? $objWidget->label : '';
                } else {
                    $temp = strlen($objWidget->label) ? $objWidget->label : '';
                    $temp .= $objWidget->generate();
                }
                $fields[$fieldname] = $temp;
                continue;
            }
            $fields[$fieldname] = $objWidget->parse();

            // Quick and dirty
            // Remove the hidden field ??? I don't know why its been generated
            if ($fieldname == 'cat') {
                $fields[$fieldname] = preg_replace('/<input(.*?)hidden(.*?)cat(.*?)>/', '', $fields[$fieldname]);
            }
        }
        $this->Template->fields = $fields;


        // set template vars
        $this->Template->results = is_array($arrResults) ? $arrResults : NULL;
        $this->Template->arrListableFields = $arrListableFields;
        $this->Template->publishedFields = unserialize($this->ml_fields);
        $this->Template->items = $items > 0 ? $items : NULL;
        $this->Template->status = is_array($arrResults) ? 'show_results' : 'show_form';
        $this->Template->countries = $arrCountries;
        $this->Template->categories = $arrCategories;
        $this->Template->postal = $strPostal;
        $this->Template->tags = str_replace('"', "&quot;", $strTags);
        $this->Template->jumpTo = $this->getJumpToHref();
        $this->Template->mysqlQuery = $strQuery;
        $this->Template->linkToForm = preg_replace('/\?(.*)$/', '', $this->Environment->request);
        $this->Template->action = $this->Environment->request;
        $this->Template->duration = (microtime() - $this->intStartTstamp - microtime()) / 1000;
        $this->Template->lat = $strLat;
        $this->Template->lng = $strLng;
        $this->Template->address = $strAddress;
        $this->Template->radius = $intRadius;
        $this->Template->memberCoord = strlen($memberCoord) ? $memberCoord : '';


    }

    /**
     * generate the jumpTo link to the detailview
     * @return string
     */
    private function getJumpToHref()
    {
        if ($this->jumpTo < 1) {
            $href = ampersand($this->Environment->request, true);
        } else {
            $objTarget = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
                ->limit(1)
                ->execute(intval($this->jumpTo));

            if ($objTarget->numRows < 1) {
                $href = ampersand($this->Environment->request, true);
            } else {
                if ($objTarget->numRows) {
                    $domain = $this->Environment->base;
                    $href = $domain . ampersand($this->generateFrontendUrl($objTarget->fetchAssoc())) . '?show=%s';
                }
            }
        }
        return $href;
    }

    /**
     * not in use (at the moment)
     * @param $pointA
     * @param $pointB
     */
    public
    function getDistance($pointA, $pointB)
    {
        // factor
        $f = M_PI / 180;
        $r = 6370;
        $a_lat = $pointA['lat'] * $f;
        $a_lng = $pointA['lng'] * $f;
        $b_lat = $pointB['lat'] * $f;
        $b_lng = $pointB['lng'] * $f;

        return round(acos(sin($b_lat) * sin($a_lat) + cos($b_lat) * cos($a_lat) * cos($b_lng - $a_lng)) * $r, 2);
    }

    /**
     * @param string $url
     * @return array
     */
    public function curl_getCoord($url)
    {
        // is cURL installed yet?
        if (!function_exists('curl_init')) {
            die('Sorry cURL is not installed!');
        }

        // OK cool - then let's create a new cURL resource handle
        $ch = curl_init();

        // Now set some options (most are optional)

        // Set URL to download
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set a referer
        //curl_setopt($ch, CURLOPT_REFERER, "http://www.kletterkader.com");

        // User agent
        //curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");

        // Include header in result? (0 = yes, 1 = no)
        //curl_setopt($ch, CURLOPT_HEADER, 0);

        // Should cURL return or print out the data? (true = return, false = print)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // Download the given URL, and return output
        $arrOutput = json_decode(curl_exec($ch), true);

        // Close the cURL resource, and free system resources
        curl_close($ch);

        return $arrOutput;
    }


}

