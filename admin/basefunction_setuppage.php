<?php
/* Copyright (C) 2004-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2006      Andre Cianfarani     <acianfa@free.fr>
 * Copyright (C) 2006-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2007      Auguria SARL         <info@auguria.org>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2011-2012 Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2012      Christophe Battarel  <christophe.battarel@altairis.fr>
 * Copyright (C) 2012      Cedric Salvador      <csalvador@gpcsolutions.fr>
 * Copyright (C) 2016      Charlie Benke		<charlie@patas-monkey.com>
 * Copyright (C) 2016	   Ferran Marcet		<fmarcet@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/product/admin/product.php
 *  \ingroup    produit
 *  \brief      Setup page of product module
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/baseFunction/core/modules/modBaseFunction.class.php';
$configuracions = new modBaseFunction($db); 
// Load translation files required by the page
$langs->loadLangs(array("admin","products"));

// Security check
if (! $user->admin || (empty($conf->product->enabled) && empty($conf->service->enabled)))
	accessforbidden();


$action = GETPOST('action', 'alpha');
$value = GETPOST('value', 'alpha');

if ($action == 'enableConfig')
{
    $configuracions->setEtatConfig($value, 1);
}
if($action == 'disableConfig')
{
    $configuracions->setEtatConfig($value, 0);
}

/*
 * View
 */

$title = "Configuració funcionalitats DEFI";

llxHeader('', $title);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($title, $linkback, 'title_setup');

print '<table class="noborder" width="100%">'."\n";
print '<tr class="liste_titre">'."\n";
print '  <td>'.$langs->trans("Name").'</td>';
print '  <td>'.$langs->trans("Description").'</td>';
print '  <td class="center" width="80">'.$langs->trans("Status").'</td>';
print '  <td class="center" width="60">'.$langs->trans("ShortInfo").'</td>';
print "</tr>\n";



foreach ($configuracions->getAllConfigPosibles() as $config) {
    print '<tr>'."\n";
        print '  <td>'.$config['nom'].'</td>';
        print '  <td>'.$config['descripcio'].'</td>';
        print '  <td class="center">';
        if($config['estat'] == 0){
            print '<a class="reposition" href="'.$_SERVER['PHP_SELF'].'?action=enableConfig&value='.$config['rowid'].'">';
                print img_picto($langs->trans("Disabled"), 'switch_off');
            print '</a>';
        }else{
            print '<a class="reposition" href="'.$_SERVER['PHP_SELF'].'?action=disableConfig&value='.$config['rowid'].'">';
                print img_picto($langs->trans("Activated"), 'switch_on');
            print '</a>';
        }
        print '</td>';
        print '<td class="center">';
            print $form->textwithpicto('', $config['info_extra'], 1);
        print '</td>';
    print "</tr>\n";
}


print '</table>';


// End of page
llxFooter();
$db->close();
