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
        foreach ($arrMember as $k => $v) {
            $this->Template->$k = $v;
        }
        // create Member Object
        $objMember = json_decode(json_encode($arrMember));

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

        if ($arrMember['vdb_nachrichten_erlauben']) {
            $this->Template->allowEmail = true;
            $this->handlePersonalMessages($objMember);
        }
    }

    /**
     *
     */
    private function handlePersonalMessages($objMember)
    {
        $arrFieldName = array
        (
            'name' => 'name',
            'label' => &$GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['name'],
            'inputType' => 'text',
            'eval' => array('mandatory' => true, 'required' => true, 'rgxp' => 'alpha')
        );
        $arrFieldEmail = array
        (
            'name' => 'replyto',
            'label' => &$GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['replyto'],
            'inputType' => 'text',
            'eval' => array('mandatory' => true, 'required' => true, 'rgxp' => 'email')
        );
        $arrFieldMessage = array
        (
            'name' => 'message',
            'label' => &$GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['message'],
            'inputType' => 'textarea',
            'eval' => array('mandatory' => true, 'required' => true, 'rows' => 4, 'cols' => 40, 'decodeEntities' => true)
        );
        $arrFieldCaptcha = array
        (
            'name' => 'captcha',
            'label' => $GLOBALS['TL_LANG']['MSC']['captcha'],
            'inputType' => 'captcha',
            'eval' => array('mandatory' => true)
        );
        $objNameWidget = new FormTextField($this->prepareForWidget($arrFieldName, $arrFieldName['name'], ''));
        $objEmailWidget = new FormTextField($this->prepareForWidget($arrFieldEmail, $arrFieldEmail['name'], ''));
        $objMessageWidget = new FormTextArea($this->prepareForWidget($arrFieldMessage, $arrFieldMessage['name'], ''));
        $objCaptchaWidget = new FormCaptcha($this->prepareForWidget($arrFieldCaptcha, $arrFieldCaptcha['name'], ''));

        // Validate widget
        if ($this->Input->post('FORM_SUBMIT') == 'tl_send_email') {
            $objNameWidget->validate();
            $objMessageWidget->validate();
            $objEmailWidget->validate();
            $objCaptchaWidget->validate();

            if (!$objNameWidget->hasErrors() && !$objMessageWidget->hasErrors() && !$objEmailWidget->hasErrors() && !$objCaptchaWidget->hasErrors()) {
                $this->sendPersonalMessage($objMember, $objNameWidget, $objMessageWidget, $objEmailWidget);
            } else {
                $this->Template->hasErrors = true;
            }
        }

        if ($_SESSION['TL_EMAIL_SENT'] === true) {
            unset($_SESSION['TL_EMAIL_SENT']);
            $this->Template->confirm = $GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['message_sent'];
        } else {
            //display the personal message form
            $this->Template->sendEmail = $GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['send_email'];
            $this->Template->action = $this->getIndexFreeRequest();
            $this->Template->nameWidget = $objNameWidget;
            $this->Template->emailWidget = $objEmailWidget;
            $this->Template->messageWidget = $objMessageWidget;
            $this->Template->captchaWidget = $objCaptchaWidget;
            $this->Template->submit = $GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['send_message'];
        }
    }


    /**
     * @param $objMember
     * @param $objNameWidget
     * @param $objMessageWidget
     * @param $objEmailWidget
     */
    private function sendPersonalMessage($objMember, $objNameWidget, $objMessageWidget, $objEmailWidget)
    {
        $objEmail = new Email();
        $objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
        // https://github.com/contao/core/issues/4560
        // $objEmail->from = $objEmailWidget->value;
        $objEmail->fromName = $objNameWidget->value;
        $objEmail->text = $objMessageWidget->value;
        $objEmail->subject = sprintf($GLOBALS['TL_LANG']['vereinsdatenbank_suchmodul']['subject'], $objEmailWidget->value, $this->Environment->host);
        $objEmail->replyTo($objNameWidget->value . ' <' . $objEmailWidget->value . '>');

        // Send e-mail
        $objEmail->sendTo($objMember->email);
        $_SESSION['TL_EMAIL_SENT'] = true;
        $this->reload();
    }


}