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
 * Frontend modules
 */
$GLOBALS['FE_MOD']['vereinsdatenbank']['vereinsuche'] = 'ModuleVereinsdatenbankSearch';
$GLOBALS['FE_MOD']['vereinsdatenbank']['vereinsuche_detail_view'] = 'ModuleVereinsdatenbankSearchDetailView';


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array('VereinsdatenbankSucheMaintenance', 'addFulltextKey');
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array('VereinsdatenbankSucheMaintenance', 'addStoredProcedure');

?>