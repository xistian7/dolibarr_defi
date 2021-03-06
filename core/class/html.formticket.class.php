<?php
/* Copyright (C) 2013-2015  Jean-François FERRY     <hello@librethic.io>
 * Copyright (C) 2016       Christophe Battarel     <christophe@altairis.fr>
 * Copyright (C) 2019       Frédéric France         <frederic.france@netlogic.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file       ticket/class/html.ticket.class.php
 *       \ingroup    ticket
 *       \brief      Fichier de la classe permettant la generation du formulaire html d'envoi de mail unitaire
 */
require_once DOL_DOCUMENT_ROOT . "/core/class/html.form.class.php";
require_once DOL_DOCUMENT_ROOT . "/core/class/html.formmail.class.php";
require_once DOL_DOCUMENT_ROOT . "/core/class/html.formprojet.class.php";
require_once DOL_DOCUMENT_ROOT.'/basic_lib/ticketvasa.php';

if (!class_exists('FormCompany')) {
    include DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
}

/**
 * Classe permettant la generation du formulaire d'un nouveau ticket.
 *
 * @package Ticket

 * \remarks Utilisation: $formticket = new FormTicket($db)
 * \remarks $formticket->proprietes=1 ou chaine ou tableau de valeurs
 * \remarks $formticket->show_form() affiche le formulaire
 */
class FormTicket
{
    /**
     * @var DoliDB Database handler.
     */
    public $db;

    public $track_id;

    /**
     * @var int ID
     */
    public $fk_user_create;

    public $message;
    public $topic_title;

    public $action;

    public $withtopic;
    public $withemail;
    /**
     *
     * @var int $withsubstit Show substitution array
     */
    public $withsubstit;

    public $withfile;

    public $ispublic; // To show information or not into public form

    public $withtitletopic;
    public $withcompany; // affiche liste déroulante company
    public $withfromsocid;
    public $withfromcontactid;
    public $withnotnotifytiersatcreate;
    public $withusercreate; // Show name of creating user in form
    public $withcreatereadonly;

    public $withref; // Show ref field

    public $withcancel;

    /**
     *
     * @var array $substit Substitutions
     */
    public $substit = array();
    public $param = array();

    /**
	 * @var string Error code (or message)
	 */
	public $error;


    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->action = 'add';

        $this->withcompany = 1;
        $this->withfromsocid = 0;
        $this->withfromcontactid = 0;
        //$this->withthreadid=0;
        //$this->withtitletopic='';
        $this->withnotnotifytiersatcreate = 0;
        $this->withusercreate = 1;
        $this->withcreatereadonly = 1;
        $this->withemail = 0;
        $this->withref = 0;
        $this->withextrafields = 0;         // Show extrafields or not
        //$this->withtopicreadonly=0;
    }

    /**
     * Show the form to input ticket
     *
     * @param  int	 $withdolfichehead			With dol_fiche_head
     * @return void
     */
    public function showForm($withdolfichehead = 0)
    {
        
        //VASA modificar formulari nou ticket
        global $conf, $langs, $user, $hookmanager;

        // Load translation files required by the page
        $langs->loadLangs(array('other', 'mails', 'ticket'));

        $form = new Form($this->db);
        $formcompany = new FormCompany($this->db);
        $ticketstatic = new Ticket($this->db);

        $soc = new Societe($this->db);
        if (!empty($this->withfromsocid) && $this->withfromsocid > 0) {
            $soc->fetch($this->withfromsocid);
        }

        $ticketstat = new Ticket($this->db);

        $extrafields = new ExtraFields($this->db);
        $extralabels = $extrafields->fetch_name_optionals_label($ticketstat->table_element);

        print "\n<!-- Begin form TICKETSUP -->\n";

        if ($withdolfichehead) dol_fiche_head(null, 'card', '', 0, '');

        print '<form method="POST" '.($withdolfichehead?'':'style="margin-bottom: 30px;" ').'name="ticket" id="form_create_ticket" enctype="multipart/form-data" action="' . $this->param["returnurl"] . '">';
        print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
        print '<input type="hidden" name="action" value="' . $this->action . '">';
        foreach ($this->param as $key => $value) {
        	print '<input type="hidden" name="' . $key . '" value="' . $value . '">';
        }
        print '<input type="hidden" name="fk_user_create" value="' . $this->fk_user_create . '">';

        print '<div class="container-fluid"><div class="row">';
        print '<div class="col-md-6"';
        // Subject
        if ($this->withtitletopic) {
            print '<label for="subject"><span class="fieldrequired">' . $langs->trans("Subject").' & '.$langs->trans("Message") . '</span></label><br/>';

            // Réponse à un ticket : affichage du titre du thread en readonly
            if ($this->withtopicreadonly) {
                print $langs->trans('SubjectAnswerToTicket') . ' ' . $this->topic_title;
                
            } else {
                if ($this->withthreadid > 0) {
                    $subject = $langs->trans('SubjectAnswerToTicket') . ' ' . $this->withthreadid . ' : ' . $this->topic_title . '';
                }
                print '<input class="text" style="width:100%;" size="50" id="subject" name="subject" value="' . (GETPOST('subject', 'alpha') ? GETPOST('subject', 'alpha') : $subject) . '" /><br/><br/>';
                
            }
        }
        // MESSAGE
        $msg = GETPOSTISSET('message') ? GETPOST('message', 'none') : '';
        //print '<label for="message"><span class="fieldrequired">' . $langs->trans("Message") . '</span></label>';

        // If public form, display more information
        $toolbarname = 'dolibarr_notes';
        if ($this->ispublic)
        {
            $toolbarname = 'dolibarr_details';
            print '<div class="warning">' . ($conf->global->TICKET_PUBLIC_TEXT_HELP_MESSAGE ? $conf->global->TICKET_PUBLIC_TEXT_HELP_MESSAGE : $langs->trans('TicketPublicPleaseBeAccuratelyDescribe')) . '</div>';
        }
        include_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
        $uselocalbrowser = true;
        $doleditor = new DolEditor('message', $msg, '100%', 230, $toolbarname, 'In', true, $uselocalbrowser);
        $doleditor->Create();
        print '<br/>';
        // FK_USER_CREATE
        if ($this->withusercreate > 0 && $this->fk_user_create) {
            print '<tr><td class="titlefield">' . $langs->trans("CreatedBy") . '</td><td>';
            $langs->load("users");
            $fuser = new User($this->db);

            if ($this->withcreatereadonly) {
                if ($res = $fuser->fetch($this->fk_user_create)) {
                    print $fuser->getNomUrl(1);
                }
            }
            print ' &nbsp; ';
            print "</td></tr>\n";
        }
        print '</div>';
        print '<div class="col-md-6">';
        if ($this->withref) {
            // Ref
            $defaultref = $ticketstat->getDefaultRef();
            print '<input size="18" type="hidden" name="ref" value="' . (GETPOST("ref", 'alpha') ? GETPOST("ref", 'alpha') : $defaultref) . '">';
        }

        // TITLE
        if ($this->withemail) {
            print '<tr><td class="titlefield"><label for="email"><span class="fieldrequired">' . $langs->trans("Email") . '</span></label></td><td>';
            print '<input  class="text minwidth200" id="email" name="email" value="' . (GETPOST('email', 'alpha') ? GETPOST('email', 'alpha') : $subject) . '" />';
            print '</td></tr>';
        }

        // Si origin du ticket
        if (isset($this->param['origin']) && $this->param['originid'] > 0) {
            // Parse element/subelement (ex: project_task)
            $element = $subelement = $this->param['origin'];
            if (preg_match('/^([^_]+)_([^_]+)/i', $this->param['origin'], $regs)) {
                $element = $regs[1];
                $subelement = $regs[2];
            }

            dol_include_once('/' . $element . '/class/' . $subelement . '.class.php');
            $classname = ucfirst($subelement);
            $objectsrc = new $classname($this->db);
            $objectsrc->fetch(GETPOST('originid', 'int'));

            if (empty($objectsrc->lines) && method_exists($objectsrc, 'fetch_lines')) {
                $objectsrc->fetch_lines();
            }

            $objectsrc->fetch_thirdparty();
            $newclassname = $classname;
            print '<tr><td>' . $langs->trans($newclassname) . '</td><td colspan="2"><input name="' . $subelement . 'id" value="' . GETPOST('originid') . '" type="hidden" />' . $objectsrc->getNomUrl(1) . '</td></tr>';
        }

        // Type
        print '<tr><td class="titlefield"><span class="fieldrequired"><label for="selecttype_code">' . $langs->trans("TicketTypeRequest") . '</span></label></td><td><br/>';
        $this->selectTypesTickets((GETPOST('type_code') ? GETPOST('type_code') : $this->type_code), 'type_code', '', '2');
        print '</td></tr><br/><br/>';

        // Severity
        print '<tr><td><span class="fieldrequired"><label for="selectseverity_code">' . $langs->trans("TicketSeverity") . '</span></label></td><td><br/>';
        $this->selectSeveritiesTickets((GETPOST('severity_code') ? GETPOST('severity_code') : $this->severity_code), 'severity_code', '', '2');
        print '</td></tr><br/><br/>';

        //VASA afegim data creacio
        //
        //VASA ficar la data facturacio a la actual automaticament
        // Date start

	print '<tr><td class="nowrap"><span>'.$langs->trans("DateActionStart").'</span></td><td><br/>';
	print $this->selectDate(date("Y-m-d G:i:s"), 'datec', 1, 1, 0, "ticket", 1, 1, 0, 'fulldayend');
        print '<br/><br/>';
        
        // Group VASA de moment no ho mostrem
        print '<div style="position:absolute; opacity:0;"><tr><td><span class="fieldrequired"><label for="selectcategory_code">' . $langs->trans("TicketGroup") . '</span></label></td><td><br/>';
        $this->selectGroupTickets((GETPOST('category_code') ? GETPOST('category_code') : $this->category_code), 'category_code', '', '2');
        print '</td></tr></div>';

        //PROJECT
        if (! empty($conf->projet->enabled) && ! $this->ispublic)
        {
            //VASA seleccionar per defecte el primer projecte existent
            if(GETPOST('projectid', 'int') != ""){
                $projecte_selec = GETPOST('projectid', 'int');   
            }else{
                $ticketvasa = new Ticketvasa($this->db);
                $projecte_selec = $ticketvasa->getDefaultProject();
            }
            $formproject=new FormProjets($this->db);
            print '<label for="project"><span class="">' . $langs->trans("Project") . '</span></label><br/>';
            print $formproject->select_projects(-1, $projecte_selec, 'projectid', 0, 0, 1, 1).'<br/><br/>';
        }
        

        

        
        if ($this->withcompany) {
            // altairis: force company and contact id for external user
            if (empty($user->socid)) {
                // Company
                print '<tr><td class="titlefield">' . $langs->trans("ThirdParty") . '</td><td><br/>';
                $events = array();
                $events[] = array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php', 1), 'htmlname' => 'contactid', 'params' => array('add-customer-contact' => 'disabled'));
                print $form->select_company($this->withfromsocid, 'socid', '', 1, 1, '', $events, 0, 'minwidth200');
                print '</td></tr>';
                if (! empty($conf->use_javascript_ajax) && ! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT)) {
                    $htmlname = 'socid';
                    print '<script type="text/javascript">
                    $(document).ready(function () {
                        jQuery("#'.$htmlname.'").change(function () {
                            var obj = '.json_encode($events).';
                            $.each(obj, function(key,values) {
                                if (values.method.length) {
                                    runJsCodeForEvent'.$htmlname.'(values);
                                }
                            });
                        });

                        function runJsCodeForEvent'.$htmlname.'(obj) {
                            console.log("Run runJsCodeForEvent'.$htmlname.'");
                            var id = $("#'.$htmlname.'").val();
                            var method = obj.method;
                            var url = obj.url;
                            var htmlname = obj.htmlname;
                            var showempty = obj.showempty;
                            $.getJSON(url,
                                    {
                                        action: method,
                                        id: id,
                                        htmlname: htmlname,
                                        showempty: showempty
                                    },
                                    function(response) {
                                        $.each(obj.params, function(key,action) {
                                            if (key.length) {
                                                var num = response.num;
                                                if (num > 0) {
                                                    $("#" + key).removeAttr(action);
                                                } else {
                                                    $("#" + key).attr(action, action);
                                                }
                                            }
                                        });
                                        $("select#" + htmlname).html(response.value);
                                        if (response.num) {
                                            var selecthtml_str = response.value;
                                            var selecthtml_dom=$.parseHTML(selecthtml_str);
                                            $("#inputautocomplete"+htmlname).val(selecthtml_dom[0][0].innerHTML);
                                        } else {
                                            $("#inputautocomplete"+htmlname).val("");
                                        }
                                        $("select#" + htmlname).change();	
                                    }
                            );
                        }
                    });
                    </script>';
                }
            }
            /*
                // Contact and type
                print '<tr><td>' . $langs->trans("Contact") . '</td><td>';
                // If no socid, set to -1 to avoid full contacts list
                $selectedCompany = ($this->withfromsocid > 0) ? $this->withfromsocid : -1;
                $nbofcontacts = $form->select_contacts($selectedCompany, $this->withfromcontactid, 'contactid', 3, '', '', 0, 'minwidth200');
                print ' ';
                $formcompany->selectTypeContact($ticketstatic, '', 'type', 'external', '', 0, 'maginleftonly');
                print '</td></tr>';
            } else {
                print '<tr><td class="titlefield"><input type="hidden" name="socid" value="' . $user->socid . '"/></td>';
                print '<td><input type="hidden" name="contactid" value="' . $user->contactid . '"/></td>';
                print '<td><input type="hidden" name="type" value="Z"/></td></tr>';
            }
            
            // Notify thirdparty at creation
            if (empty($this->ispublic))
            {
                print '<tr><td><label for="notify_tiers_at_create">' . $langs->trans("TicketNotifyTiersAtCreation") . '</label></td><td>';
                print '<input type="checkbox" id="notify_tiers_at_create" name="notify_tiers_at_create"'.($this->withnotifytiersatcreate?' checked="checked"':'').'>';
                print '</td></tr>';
            }*/
        }
        
        //ASSIGNAT
        print '<br/><br/><tr><td class="titlefield">' . $langs->trans("AssignUser") . '</td><td><br/>';
        $empleats=array(3,4);
        print $form->select_dolusers($user->id, 'fk_user_assign', 1, $empleats);
        

        // Attached files VASA de moment no pujam fitxers a la creació
        /*if (!empty($this->withfile)) {
            // Define list of attached files
            $listofpaths = array();
            $listofnames = array();
            $listofmimes = array();
            if (!empty($_SESSION["listofpaths"])) {
                $listofpaths = explode(';', $_SESSION["listofpaths"]);
            }

            if (!empty($_SESSION["listofnames"])) {
                $listofnames = explode(';', $_SESSION["listofnames"]);
            }

            if (!empty($_SESSION["listofmimes"])) {
                $listofmimes = explode(';', $_SESSION["listofmimes"]);
            }

            $out .= '<tr>';
            $out .= '<td width="180">' . $langs->trans("MailFile") . '</td>';
            $out .= '<td colspan="2">';
            // TODO Trick to have param removedfile containing nb of image to delete. But this does not works without javascript
            $out .= '<input type="hidden" class="removedfilehidden" name="removedfile" value="">' . "\n";
            $out .= '<script type="text/javascript" language="javascript">';
            $out .= 'jQuery(document).ready(function () {';
            $out .= '    jQuery(".removedfile").click(function() {';
            $out .= '        jQuery(".removedfilehidden").val(jQuery(this).val());';
            $out .= '    });';
            $out .= '})';
            $out .= '</script>' . "\n";
            if (count($listofpaths)) {
                foreach ($listofpaths as $key => $val) {
                    $out .= '<div id="attachfile_' . $key . '">';
                    $out .= img_mime($listofnames[$key]) . ' ' . $listofnames[$key];
                    if (!$this->withfilereadonly) {
                        $out .= ' <input type="image" style="border: 0px;" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/delete.png" value="' . ($key + 1) . '" class="removedfile" id="removedfile_' . $key . '" name="removedfile_' . $key . '" />';
                    }
                    $out .= '<br></div>';
                }
            } else {
                $out .= $langs->trans("NoAttachedFiles") . '<br>';
            }
            if ($this->withfile == 2) { // Can add other files
                $out .= '<input type="file" class="flat" id="addedfile" name="addedfile" value="' . $langs->trans("Upload") . '" />';
                $out .= ' ';
                $out .= '<input type="submit" class="button" id="addfile" name="addfile" value="' . $langs->trans("MailingAddFile") . '" />';
            }
            $out .= "</td></tr>\n";

            print $out;
        }*/

        // Other attributes
        $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $ticketstat, $action); // Note that $action and $object may have been modified by hook
        if (empty($reshook))
        {
            print $ticketstat->showOptionals($extrafields, 'edit');
        }
        print '</div>';
        print '</div></div>';

        if ($withdolfichehead) dol_fiche_end();

        print '<br><center>';
        print '<input class="button" type="submit" name="add" value="' . $langs->trans(($this->withthreadid > 0 ? "SendResponse" : "NewTicket")) . '" />';

        if ($this->withcancel) {
            print " &nbsp; &nbsp; ";
            print "<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"" . $langs->trans("Cancel") . "\">";
        }
        print "</center>\n";

        print "</form>\n";
        print "<!-- End form TICKET -->\n";
    }

    /**
     *      Return html list of tickets type
     *
     *      @param  string $selected    Id du type pre-selectionne
     *      @param  string $htmlname    Nom de la zone select
     *      @param  string $filtertype  To filter on field type in llx_c_ticket_type (array('code'=>xx,'label'=>zz))
     *      @param  int    $format      0=id+libelle, 1=code+code, 2=code+libelle, 3=id+code
     *      @param  int    $empty       1=peut etre vide, 0 sinon
     *      @param  int    $noadmininfo 0=Add admin info, 1=Disable admin info
     *      @param  int    $maxlength   Max length of label
     *      @param	string	$morecss	More CSS
     *      @return void
     */
    public function selectTypesTickets($selected = '', $htmlname = 'tickettype', $filtertype = '', $format = 0, $empty = 0, $noadmininfo = 0, $maxlength = 0, $morecss = '')
    {
        global $langs, $user;

        $ticketstat = new Ticket($this->db);

        dol_syslog(get_class($this) . "::select_types_tickets " . $selected . ", " . $htmlname . ", " . $filtertype . ", " . $format, LOG_DEBUG);

        $filterarray = array();

        if ($filtertype != '' && $filtertype != '-1') {
            $filterarray = explode(',', $filtertype);
        }

        $ticketstat->loadCacheTypesTickets();

        print '<select id="select' . $htmlname . '" class="flat minwidth100'.($morecss?' '.$morecss:'').'" name="' . $htmlname . '">';
        if ($empty) {
            print '<option value="">&nbsp;</option>';
        }

        if (is_array($ticketstat->cache_types_tickets) && count($ticketstat->cache_types_tickets)) {
            foreach ($ticketstat->cache_types_tickets as $id => $arraytypes) {
                // On passe si on a demande de filtrer sur des modes de paiments particuliers
                if (count($filterarray) && !in_array($arraytypes['type'], $filterarray)) {
                    continue;
                }

                // We discard empty line if showempty is on because an empty line has already been output.
                if ($empty && empty($arraytypes['code'])) {
                    continue;
                }

                if ($format == 0) {
                    print '<option value="' . $id . '"';
                }

                if ($format == 1) {
                    print '<option value="' . $arraytypes['code'] . '"';
                }

                if ($format == 2) {
                    print '<option value="' . $arraytypes['code'] . '"';
                }

                if ($format == 3) {
                    print '<option value="' . $id . '"';
                }

                // Si selected est text, on compare avec code, sinon avec id
                if (preg_match('/[a-z]/i', $selected) && $selected == $arraytypes['code']) {
                    print ' selected="selected"';
                } elseif ($selected == $id) {
                    print ' selected="selected"';
                } elseif ($arraytypes['use_default'] == "1" && !$empty) {
                    print ' selected="selected"';
                }

                print '>';
                if ($format == 0) {
                    $value = ($maxlength ? dol_trunc($arraytypes['label'], $maxlength) : $arraytypes['label']);
                }

                if ($format == 1) {
                    $value = $arraytypes['code'];
                }

                if ($format == 2) {
                    $value = ($maxlength ? dol_trunc($arraytypes['label'], $maxlength) : $arraytypes['label']);
                }

                if ($format == 3) {
                    $value = $arraytypes['code'];
                }

                print $value ? $value : '&nbsp;';
                print '</option>';
            }
        }
        print '</select>';
        if ($user->admin && !$noadmininfo) {
            print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
        }

        print ajax_combobox('select'.$htmlname);
    }

    /**
     *      Return html list of ticket anaytic codes
     *
     *      @param  string $selected    Id categorie pre-selectionnée
     *      @param  string $htmlname    Nom de la zone select
     *      @param  string $filtertype  To filter on field type in llx_c_ticket_category (array('code'=>xx,'label'=>zz))
     *      @param  int    $format      0=id+libelle, 1=code+code, 2=code+libelle, 3=id+code
     *      @param  int    $empty       1=peut etre vide, 0 sinon
     *      @param  int    $noadmininfo 0=Add admin info, 1=Disable admin info
     *      @param  int    $maxlength   Max length of label
     *      @param	string	$morecss	More CSS
     *      @return void
     */
    public function selectGroupTickets($selected = '', $htmlname = 'ticketcategory', $filtertype = '', $format = 0, $empty = 0, $noadmininfo = 0, $maxlength = 0, $morecss = '')
    {
        global $langs, $user;

        $ticketstat = new Ticket($this->db);

        dol_syslog(get_class($this) . "::selectCategoryTickets " . $selected . ", " . $htmlname . ", " . $filtertype . ", " . $format, LOG_DEBUG);

        $filterarray = array();

        if ($filtertype != '' && $filtertype != '-1') {
            $filterarray = explode(',', $filtertype);
        }

        $ticketstat->loadCacheCategoriesTickets();

        print '<select id="select' . $htmlname . '" class="flat minwidth100'.($morecss?' '.$morecss:'').'" name="' . $htmlname . '">';
        if ($empty) {
            print '<option value="">&nbsp;</option>';
        }

        if (is_array($ticketstat->cache_category_tickets) && count($ticketstat->cache_category_tickets)) {
            foreach ($ticketstat->cache_category_tickets as $id => $arraycategories) {
                // On passe si on a demande de filtrer sur des modes de paiments particuliers
                if (count($filterarray) && !in_array($arraycategories['type'], $filterarray)) {
                    continue;
                }

                // We discard empty line if showempty is on because an empty line has already been output.
                if ($empty && empty($arraycategories['code'])) {
                    continue;
                }

                if ($format == 0) {
                    print '<option value="' . $id . '"';
                }

                if ($format == 1) {
                    print '<option value="' . $arraycategories['code'] . '"';
                }

                if ($format == 2) {
                    print '<option value="' . $arraycategories['code'] . '"';
                }

                if ($format == 3) {
                    print '<option value="' . $id . '"';
                }

                // Si selected est text, on compare avec code, sinon avec id
                if (preg_match('/[a-z]/i', $selected) && $selected == $arraycategories['code']) {
                    print ' selected="selected"';
                } elseif ($selected == $id) {
                    print ' selected="selected"';
                } elseif ($arraycategories['use_default'] == "1" && !$empty) {
                    print ' selected="selected"';
                }

                print '>';

                if ($format == 0) {
                    $value = ($maxlength ? dol_trunc($arraycategories['label'], $maxlength) : $arraycategories['label']);
                }

                if ($format == 1) {
                    $value = $arraycategories['code'];
                }

                if ($format == 2) {
                    $value = ($maxlength ? dol_trunc($arraycategories['label'], $maxlength) : $arraycategories['label']);
                }

                if ($format == 3) {
                    $value = $arraycategories['code'];
                }

                print $value ? $value : '&nbsp;';
                print '</option>';
            }
        }
        print '</select>';
        if ($user->admin && !$noadmininfo) {
            print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
        }

        print ajax_combobox('select'.$htmlname);
    }

    /**
     *      Return html list of ticket severitys
     *
     *      @param  string $selected    Id severity pre-selectionnée
     *      @param  string $htmlname    Nom de la zone select
     *      @param  string $filtertype  To filter on field type in llx_c_ticket_severity (array('code'=>xx,'label'=>zz))
     *      @param  int    $format      0=id+libelle, 1=code+code, 2=code+libelle, 3=id+code
     *      @param  int    $empty       1=peut etre vide, 0 sinon
     *      @param  int    $noadmininfo 0=Add admin info, 1=Disable admin info
     *      @param  int    $maxlength   Max length of label
     *      @param	string	$morecss	More CSS
     *      @return void
     */
    public function selectSeveritiesTickets($selected = '', $htmlname = 'ticketseverity', $filtertype = '', $format = 0, $empty = 0, $noadmininfo = 0, $maxlength = 0, $morecss = '')
    {
        global $langs, $user;

        $ticketstat = new Ticket($this->db);

        dol_syslog(get_class($this) . "::selectSeveritiesTickets " . $selected . ", " . $htmlname . ", " . $filtertype . ", " . $format, LOG_DEBUG);

        $filterarray = array();

        if ($filtertype != '' && $filtertype != '-1') {
            $filterarray = explode(',', $filtertype);
        }

        $ticketstat->loadCacheSeveritiesTickets();

        print '<select id="select' . $htmlname . '" class="flat minwidth100'.($morecss?' '.$morecss:'').'" name="' . $htmlname . '">';
        if ($empty) {
            print '<option value="">&nbsp;</option>';
        }

        if (is_array($ticketstat->cache_severity_tickets) && count($ticketstat->cache_severity_tickets)) {
            foreach ($ticketstat->cache_severity_tickets as $id => $arrayseverities) {
                // On passe si on a demande de filtrer sur des modes de paiments particuliers
                if (count($filterarray) && !in_array($arrayseverities['type'], $filterarray)) {
                    continue;
                }

                // We discard empty line if showempty is on because an empty line has already been output.
                if ($empty && empty($arrayseverities['code'])) {
                    continue;
                }

                if ($format == 0) {
                    print '<option value="' . $id . '"';
                }

                if ($format == 1) {
                    print '<option value="' . $arrayseverities['code'] . '"';
                }

                if ($format == 2) {
                    print '<option value="' . $arrayseverities['code'] . '"';
                }

                if ($format == 3) {
                    print '<option value="' . $id . '"';
                }

                // Si selected est text, on compare avec code, sinon avec id
                if (preg_match('/[a-z]/i', $selected) && $selected == $arrayseverities['code']) {
                    print ' selected="selected"';
                } elseif ($selected == $id) {
                    print ' selected="selected"';
                } elseif ($arrayseverities['use_default'] == "1" && !$empty) {
                    print ' selected="selected"';
                }

                print '>';
                if ($format == 0) {
                    $value = ($maxlength ? dol_trunc($arrayseverities['label'], $maxlength) : $arrayseverities['label']);
                }

                if ($format == 1) {
                    $value = $arrayseverities['code'];
                }

                if ($format == 2) {
                    $value = ($maxlength ? dol_trunc($arrayseverities['label'], $maxlength) : $arrayseverities['label']);
                }

                if ($format == 3) {
                    $value = $arrayseverities['code'];
                }

                print $value ? $value : '&nbsp;';
                print '</option>';
            }
        }
        print '</select>';
        if ($user->admin && !$noadmininfo) {
            print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
        }

        print ajax_combobox('select'.$htmlname);
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     * Clear list of attached files in send mail form (also stored in session)
     *
     * @return	void
     */
    public function clear_attached_files()
    {
    	// phpcs:enable
    	global $conf,$user;
    	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

    	// Set tmp user directory
    	$vardir=$conf->user->dir_output."/".$user->id;
    	$upload_dir = $vardir.'/temp/';                     // TODO Add $keytoavoidconflict in upload_dir path
    	if (is_dir($upload_dir)) dol_delete_dir_recursive($upload_dir);

    	$keytoavoidconflict = empty($this->trackid)?'':'-'.$this->trackid;   // this->trackid must be defined
    	unset($_SESSION["listofpaths".$keytoavoidconflict]);
    	unset($_SESSION["listofnames".$keytoavoidconflict]);
    	unset($_SESSION["listofmimes".$keytoavoidconflict]);
    }

    /**
     * Show the form to add message on ticket
     *
     * @param  	string  $width      	Width of form
     * @return 	void
     */
    public function showMessageForm($width = '40%')
    {
        global $conf, $langs, $user, $hookmanager, $form, $mysoc;

        $formmail = new FormMail($this->db);
        $addfileaction = 'addfile';

        if (! is_object($form)) $form=new Form($this->db);

        // Load translation files required by the page
        $langs->loadLangs(array('other', 'mails'));

        // Clear temp files. Must be done at beginning, before call of triggers
        if (GETPOST('mode', 'alpha') == 'init' || (GETPOST('modelmailselected', 'alpha') && GETPOST('modelmailselected', 'alpha') != '-1'))
        {
        	$this->clear_attached_files();
        }

        // Define output language
        $outputlangs = $langs;
        $newlang = '';
        if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $this->param['langsmodels'];
        if (! empty($newlang))
        {
        	$outputlangs = new Translate("", $conf);
        	$outputlangs->setDefaultLang($newlang);
        	$outputlangs->load('other');
        }

        // Get message template for $this->param["models"] into c_email_templates
        $arraydefaultmessage = -1;
        if ($this->param['models'] != 'none')
        {
        	$model_id=0;
        	if (array_key_exists('models_id', $this->param))
        	{
        		$model_id=$this->param["models_id"];
        	}

        	$arraydefaultmessage=$formmail->getEMailTemplate($this->db, $this->param["models"], $user, $outputlangs, $model_id);		// If $model_id is empty, preselect the first one
        }

        // Define list of attached files
        $listofpaths = array();
        $listofnames = array();
        $listofmimes = array();
        $keytoavoidconflict = empty($this->trackid)?'':'-'.$this->trackid;   // this->trackid must be defined

        if (GETPOST('mode', 'alpha') == 'init' || (GETPOST('modelmailselected', 'alpha') && GETPOST('modelmailselected', 'alpha') != '-1'))
        {
        	if (! empty($arraydefaultmessage->joinfiles) && is_array($this->param['fileinit']))
        	{
        		foreach($this->param['fileinit'] as $file)
        		{
        			$this->add_attached_files($file, basename($file), dol_mimetype($file));
        		}
        	}
        }

        if (! empty($_SESSION["listofpaths".$keytoavoidconflict])) $listofpaths=explode(';', $_SESSION["listofpaths".$keytoavoidconflict]);
        if (! empty($_SESSION["listofnames".$keytoavoidconflict])) $listofnames=explode(';', $_SESSION["listofnames".$keytoavoidconflict]);
        if (! empty($_SESSION["listofmimes".$keytoavoidconflict])) $listofmimes=explode(';', $_SESSION["listofmimes".$keytoavoidconflict]);

        // Define output language
        $outputlangs = $langs;
        $newlang = '';
        if ($conf->global->MAIN_MULTILANGS && empty($newlang)) {
            $newlang = $this->param['langsmodels'];
        }
        if (! empty($newlang)) {
            $outputlangs = new Translate("", $conf);
            $outputlangs->setDefaultLang($newlang);
            $outputlangs->load('other');
        }

        print "\n<!-- Begin message_form TICKETSUP -->\n";

        $send_email = GETPOST('send_email', 'int') ? GETPOST('send_email', 'int') : 0;

        // Example 1 : Adding jquery code
        print '<script type="text/javascript" language="javascript">
		jQuery(document).ready(function() {
			send_email=' . $send_email . ';
			if (send_email) {
				jQuery(".email_line").show();
			} else {
				jQuery(".email_line").hide();
			}

			jQuery("#send_msg_email").click(function() {
				if(jQuery(this).is(":checked")) {
					jQuery(".email_line").show();
				}
				else {
					jQuery(".email_line").hide();
				}
            });';
        print '});
		</script>';

        print '<form method="post" name="ticket" enctype="multipart/form-data" action="' . $this->param["returnurl"] . '">';
        print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
        print '<input type="hidden" name="action" value="' . $this->action . '">';
        print '<input type="hidden" name="actionbis" value="add_message">';
        foreach ($this->param as $key => $value) {
            print '<input type="hidden" name="' . $key . '" value="' . $value . '">';
        }

        // Get message template
        $model_id=0;
        if (array_key_exists('models_id', $this->param)) {
            $model_id=$this->param["models_id"];
            $arraydefaultmessage=$formmail->getEMailTemplate($this->db, $this->param["models"], $user, $outputlangs, $model_id);
        }

        $result = $formmail->fetchAllEMailTemplate($this->param["models"], $user, $outputlangs);
        if ($result<0) {
            setEventMessages($this->error, $this->errors, 'errors');
        }
        $modelmail_array=array();
        foreach ($formmail->lines_model as $line) {
            $modelmail_array[$line->id]=$line->label;
        }

        print '<table class="border"  width="' . $width . '">';

        // External users can't send message email
        if ($user->rights->ticket->write && !$user->socid)
        {
            print '<tr><td width="30%"></td><td colspan="2">';
            $checkbox_selected = (GETPOST('send_email') == "1" ? ' checked' : '');
            print '<input type="checkbox" name="send_email" value="1" id="send_msg_email" '.$checkbox_selected.'/> ';
            print '<label for="send_msg_email">' . $langs->trans('SendMessageByEmail') . '</label>';
            print '</td></tr>';

            // Zone to select its email template
            if (count($modelmail_array)>0) {
                print '<tr class="email_line"><td></td><td colspan="2"><div style="padding: 3px 0 3px 0">'."\n";
                print $langs->trans('SelectMailModel').': '.$formmail->selectarray('modelmailselected', $modelmail_array, $this->param['models_id'], 1);
                if ($user->admin) {
                    print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
                }
                print ' &nbsp; ';
                print '<input class="button" type="submit" value="'.$langs->trans('Use').'" name="modelselected" id="modelselected">';
                print ' &nbsp; ';
                print '</div></td>';
            }

            // Private message (not visible by customer/external user)
            if (! $user->socid) {
                print '<tr><td width="30%"></td><td>';
                $checkbox_selected = (GETPOST('private_message', 'alpha') == "1" ? ' checked' : '');
                print '<input type="checkbox" name="private_message" value="1" id="private_message" '.$checkbox_selected.'/> ';
                print '<label for="private_message">' . $langs->trans('MarkMessageAsPrivate') . '</label>';
                print '</td><td align="center">';
                print $form->textwithpicto('', $langs->trans("TicketMessagePrivateHelp"), 1, 'help');
                print '</td></tr>';
            }

            print '<tr class="email_line"><td class="titlefieldcreate">' . $langs->trans('Subject') . '</td>';
            $label_title = empty($conf->global->MAIN_APPLICATION_TITLE) ? $mysoc->name : $conf->global->MAIN_APPLICATION_TITLE;
            print '<td colspan="2"><input type="text" class="text" size="80" name="subject" value="[' . $label_title . ' - ticket #' . $this->track_id . '] ' . $langs->trans('TicketNewMessage') . '" />';
            print '</td></tr>';

            // Destinataires
            print '<tr class="email_line"><td>' . $langs->trans('MailRecipients') . '</td><td colspan="2">';
            $ticketstat = new Ticket($this->db);
            $res = $ticketstat->fetch('', '', $this->track_id);
            if ($res) {
                // Retrieve email of all contacts (internal and external)
                $contacts = $ticketstat->getInfosTicketInternalContact();
                $contacts = array_merge($contacts, $ticketstat->getInfosTicketExternalContact());

                // Build array to display recipient list
                if (is_array($contacts) && count($contacts) > 0) {
                    foreach ($contacts as $key => $info_sendto) {
                        if ($info_sendto['email'] != '') {
                            $sendto[] = dol_escape_htmltag(trim($info_sendto['firstname'] . " " . $info_sendto['lastname']) . " <" . $info_sendto['email'] . "> (" . $info_sendto['libelle'] . ")");
                        }
                    }
                }

                if ($ticketstat->origin_email && !in_array($this->dao->origin_email, $sendto)) {
                    $sendto[] = $ticketstat->origin_email . "(origin)";
                }

                if ($ticketstat->fk_soc > 0) {
                    $ticketstat->socid = $ticketstat->fk_soc;
                    $ticketstat->fetch_thirdparty();

                    if (is_array($ticketstat->thirdparty->email) && !in_array($ticketstat->thirdparty->email, $sendto)) {
                        $sendto[] = $ticketstat->thirdparty->email . '(' . $langs->trans('Customer') . ')';
                    }
                }

                if ($conf->global->TICKET_NOTIFICATION_ALSO_MAIN_ADDRESS) {
                    $sendto[] = $conf->global->TICKET_NOTIFICATION_EMAIL_TO . '(generic email)';
                }

                // Print recipient list
                if (is_array($sendto) && count($sendto) > 0) {
                    print implode(', ', $sendto);
                } else {
                    print '<div class="warning">' . $langs->trans('WarningNoEMailsAdded') . ' ' . $langs->trans('TicketGoIntoContactTab') . '</div>';
                }
            }
            print '</td></tr>';
        }

        // Intro
        // External users can't send message email
        if ($user->rights->ticket->write && !$user->socid) {
            $mail_intro = GETPOST('mail_intro') ? GETPOST('mail_intro') : $conf->global->TICKET_MESSAGE_MAIL_INTRO;
            print '<tr class="email_line"><td><label for="mail_intro">' . $langs->trans("TicketMessageMailIntro") . '</label>';

            print '</td><td>';
            include_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
            $uselocalbrowser = true;

            $doleditor = new DolEditor('mail_intro', $mail_intro, '100%', 140, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_SOCIETE, ROWS_2, 70);

            $doleditor->Create();
            print '</td><td align="center">';
            print $form->textwithpicto('', $langs->trans("TicketMessageMailIntroHelp"), 1, 'help');
            print '</td></tr>';
        }

        // MESSAGE
        $defaultmessage="";
        if (is_array($arraydefaultmessage) && count($arraydefaultmessage) > 0 && $arraydefaultmessage->content) {
            $defaultmessage=$arraydefaultmessage->content;
        }
        $defaultmessage=str_replace('\n', "\n", $defaultmessage);

        // Deal with format differences between message and signature (text / HTML)
        if (dol_textishtml($defaultmessage) && !dol_textishtml($this->substit['__SIGNATURE__'])) {
            $this->substit['__SIGNATURE__'] = dol_nl2br($this->substit['__SIGNATURE__']);
        } elseif (!dol_textishtml($defaultmessage) && dol_textishtml($this->substit['__SIGNATURE__'])) {
            $defaultmessage = dol_nl2br($defaultmessage);
        }
        if (isset($_POST["message"]) &&  ! $_POST['modelselected']) {
            $defaultmessage=GETPOST('message');
        } else {
            $defaultmessage=make_substitutions($defaultmessage, $this->substit);
            // Clean first \n and br (to avoid empty line when CONTACTCIVNAME is empty)
            $defaultmessage=preg_replace("/^(<br>)+/", "", $defaultmessage);
            $defaultmessage=preg_replace("/^\n+/", "", $defaultmessage);
        }

        print '<tr><td class="tdtop"><label for="message"><span class="fieldrequired">' . $langs->trans("Message") . '</span></label></td><td>';
        //$toolbarname = 'dolibarr_details';
        $toolbarname = 'dolibarr_notes';
        include_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
        $doleditor = new DolEditor('message', $defaultmessage, '100%', 200, $toolbarname, '', false, true, $conf->global->FCKEDITOR_ENABLE_SOCIETE, ROWS_5, 70);
        $doleditor->Create();
        print '</td><td align="center">';
        if ($user->rights->ticket->write && !$user->socid) {
            print $form->textwithpicto('', $langs->trans("TicketMessageHelp"), 1, 'help');
        }

        print '</td></tr>';

        // Signature
        // External users can't send message email
        if ($user->rights->ticket->write && !$user->socid) {
            $mail_signature = GETPOST('mail_signature') ? GETPOST('mail_signature') : $conf->global->TICKET_MESSAGE_MAIL_SIGNATURE;
            print '<tr class="email_line"><td><label for="mail_intro">' . $langs->trans("TicketMessageMailSignature") . '</label>';

            print '</td><td>';
            include_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
            $doleditor = new DolEditor('mail_signature', $mail_signature, '100%', 150, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_SOCIETE, ROWS_2, 70);
            $doleditor->Create();
            print '</td><td align="center">';
            print $form->textwithpicto('', $langs->trans("TicketMessageMailSignatureHelp"), 1, 'help');
            print '</td></tr>';
        }

        // Attached files
        if (!empty($this->withfile)) {
            $out .= '<tr>';
            $out .= '<td width="180">' . $langs->trans("MailFile") . '</td>';
            $out .= '<td colspan="2">';
            // TODO Trick to have param removedfile containing nb of image to delete. But this does not works without javascript
            $out .= '<input type="hidden" class="removedfilehidden" name="removedfile" value="">' . "\n";
            $out .= '<script type="text/javascript" language="javascript">';
            $out .= 'jQuery(document).ready(function () {';
            $out .= '    jQuery(".removedfile").click(function() {';
            $out .= '        jQuery(".removedfilehidden").val(jQuery(this).val());';
            $out .= '    });';
            $out .= '})';
            $out .= '</script>' . "\n";
            if (count($listofpaths)) {
                foreach ($listofpaths as $key => $val) {
                    $out .= '<div id="attachfile_' . $key . '">';
                    $out .= img_mime($listofnames[$key]) . ' ' . $listofnames[$key];
                    if (!$this->withfilereadonly) {
                        $out .= ' <input type="image" style="border: 0px;" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/delete.png" value="' . ($key + 1) . '" class="removedfile reposition" id="removedfile_' . $key . '" name="removedfile_' . $key . '" />';
                    }
                    $out .= '<br></div>';
                }
            } else {
                $out .= $langs->trans("NoAttachedFiles") . '<br>';
            }
            if ($this->withfile == 2) { // Can add other files
                $out .= '<input type="file" class="flat" id="addedfile" name="addedfile" value="' . $langs->trans("Upload") . '" />';
                $out .= ' ';
                $out .= '<input type="submit" class="button" id="' . $addfileaction . '" name="' . $addfileaction . '" value="' . $langs->trans("MailingAddFile") . '" />';
            }
            $out .= "</td></tr>\n";

            print $out;
        }

        print '</table>';

        print '<center><br>';
        print '<input class="button" type="submit" name="btn_add_message" value="' . $langs->trans("AddMessage") . '" />';
        if ($this->withcancel) {
            print " &nbsp; &nbsp; ";
            print "<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"" . $langs->trans("Cancel") . "\">";
        }
        print "</center>\n";

        print "</form>\n";
        print "<!-- End form TICKET -->\n";
    }
    //VASA funcio selectdate
    public function selectDate($set_time = '', $prefix = 're', $h = 0, $m = 0, $empty = 0, $form_name = "", $d = 1, $addnowlink = 0, $disabled = 0, $fullday = '', $addplusone = '', $adddateof = '')
	{
		global $conf,$langs;

		$retstring='';

		if ($prefix=='') $prefix='re';
		if ($h == '') $h=0;
		if ($m == '') $m=0;
		$emptydate=0;
		$emptyhours=0;
		if ($empty == 1) { $emptydate=1; $emptyhours=1; }
		if ($empty == 2) { $emptydate=0; $emptyhours=1; }
		$orig_set_time=$set_time;

		if ($set_time === '' && $emptydate == 0)
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
			$set_time = dol_now('tzuser')-(getServerTimeZoneInt('now')*3600); // set_time must be relative to PHP server timezone
		}

		// Analysis of the pre-selection date
		if (preg_match('/^([0-9]+)\-([0-9]+)\-([0-9]+)\s?([0-9]+)?:?([0-9]+)?/', $set_time, $reg))	// deprecated usage
		{
			// Date format 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'
			$syear	= (! empty($reg[1])?$reg[1]:'');
			$smonth	= (! empty($reg[2])?$reg[2]:'');
			$sday	= (! empty($reg[3])?$reg[3]:'');
			$shour	= (! empty($reg[4])?$reg[4]:'');
			$smin	= (! empty($reg[5])?$reg[5]:'');
		}
		elseif (strval($set_time) != '' && $set_time != -1)
		{
			// set_time est un timestamps (0 possible)
			$syear = dol_print_date($set_time, "%Y");
			$smonth = dol_print_date($set_time, "%m");
			$sday = dol_print_date($set_time, "%d");
			if ($orig_set_time != '')
			{
				$shour = dol_print_date($set_time, "%H");
				$smin = dol_print_date($set_time, "%M");
				$ssec = dol_print_date($set_time, "%S");
			}
			else
			{
				$shour = '';
				$smin = '';
				$ssec = '';
			}
		}
		else
		{
			// Date est '' ou vaut -1
			$syear = '';
			$smonth = '';
			$sday = '';
			$shour = !isset($conf->global->MAIN_DEFAULT_DATE_HOUR) ? ($h == -1 ? '23' : '') : $conf->global->MAIN_DEFAULT_DATE_HOUR;
			$smin = !isset($conf->global->MAIN_DEFAULT_DATE_MIN) ? ($h == -1 ? '59' : '') : $conf->global->MAIN_DEFAULT_DATE_MIN;
			$ssec = !isset($conf->global->MAIN_DEFAULT_DATE_SEC) ? ($h == -1 ? '59' : '') : $conf->global->MAIN_DEFAULT_DATE_SEC;
		}
		if ($h == 3) $shour = '';
		if ($m == 3) $smin = '';

		// You can set MAIN_POPUP_CALENDAR to 'eldy' or 'jquery'
		$usecalendar='combo';
		if (! empty($conf->use_javascript_ajax) && (empty($conf->global->MAIN_POPUP_CALENDAR) || $conf->global->MAIN_POPUP_CALENDAR != "none")) {
			$usecalendar = ((empty($conf->global->MAIN_POPUP_CALENDAR) || $conf->global->MAIN_POPUP_CALENDAR == 'eldy')?'jquery':$conf->global->MAIN_POPUP_CALENDAR);
		}

		if ($d)
		{
			// Show date with popup
			if ($usecalendar != 'combo')
			{
				$formated_date='';
				//print "e".$set_time." t ".$conf->format_date_short;
				if (strval($set_time) != '' && $set_time != -1)
				{
					//$formated_date=dol_print_date($set_time,$conf->format_date_short);
					$formated_date=dol_print_date($set_time, $langs->trans("FormatDateShortInput"));  // FormatDateShortInput for dol_print_date / FormatDateShortJavaInput that is same for javascript
				}

				// Calendrier popup version eldy
				if ($usecalendar == "eldy")
				{
					// Zone de saisie manuelle de la date
					$retstring.='<input id="'.$prefix.'" name="'.$prefix.'" type="text" class="maxwidth100" maxlength="11" value="'.$formated_date.'"';
					$retstring.=($disabled?' disabled':'');
					$retstring.=' onChange="dpChangeDay(\''.$prefix.'\',\''.$langs->trans("FormatDateShortJavaInput").'\'); "';  // FormatDateShortInput for dol_print_date / FormatDateShortJavaInput that is same for javascript
					$retstring.='>';

					// Icone calendrier
					if (! $disabled)
					{
						$retstring.='<button id="'.$prefix.'Button" type="button" class="dpInvisibleButtons"';
						$base=DOL_URL_ROOT.'/core/';
						$retstring.=' onClick="showDP(\''.$base.'\',\''.$prefix.'\',\''.$langs->trans("FormatDateShortJavaInput").'\',\''.$langs->defaultlang.'\');"';
						$retstring.='>'.img_object($langs->trans("SelectDate"), 'calendarday', 'class="datecallink"').'</button>';
					}
					else $retstring.='<button id="'.$prefix.'Button" type="button" class="dpInvisibleButtons">'.img_object($langs->trans("Disabled"), 'calendarday', 'class="datecallink"').'</button>';

					$retstring.='<input type="hidden" id="'.$prefix.'day"   name="'.$prefix.'day"   value="'.$sday.'">'."\n";
					$retstring.='<input type="hidden" id="'.$prefix.'month" name="'.$prefix.'month" value="'.$smonth.'">'."\n";
					$retstring.='<input type="hidden" id="'.$prefix.'year"  name="'.$prefix.'year"  value="'.$syear.'">'."\n";
				}
				elseif ($usecalendar == 'jquery')
				{
					if (! $disabled)
					{
						// Output javascript for datepicker
						$retstring.="<script type='text/javascript'>";
						$retstring.="$(function(){ $('#".$prefix."').datepicker({
							dateFormat: '".$langs->trans("FormatDateShortJQueryInput")."',
							autoclose: true,
							todayHighlight: true,";
							if (! empty($conf->dol_use_jmobile))
							{
								$retstring.="
								beforeShow: function (input, datePicker) {
									input.disabled = true;
								},
								onClose: function (dateText, datePicker) {
									this.disabled = false;
								},
								";
							}
							// Note: We don't need monthNames, monthNamesShort, dayNames, dayNamesShort, dayNamesMin, they are set globally on datepicker component in lib_head.js.php
							if (empty($conf->global->MAIN_POPUP_CALENDAR_ON_FOCUS))
							{
							$retstring.="
								showOn: 'button',
								buttonImage: '".DOL_URL_ROOT."/theme/".$conf->theme."/img/object_calendarday.png',
								buttonImageOnly: true";
							}
							$retstring.="
							}) });";
						$retstring.="</script>";
					}

					// Zone de saisie manuelle de la date
					$retstring.='<div class="nowrap inline-block">';
					$retstring.='<input id="'.$prefix.'" name="'.$prefix.'" type="text" class="maxwidth100" maxlength="11" value="'.$formated_date.'"';
					$retstring.=($disabled?' disabled':'');
					$retstring.=' onChange="dpChangeDay(\''.$prefix.'\',\''.$langs->trans("FormatDateShortJavaInput").'\'); "';  // FormatDateShortInput for dol_print_date / FormatDateShortJavaInput that is same for javascript
					$retstring.='>';

					// Icone calendrier
					if (! $disabled)
					{
						/* Not required. Managed by option buttonImage of jquery
                		$retstring.=img_object($langs->trans("SelectDate"),'calendarday','id="'.$prefix.'id" class="datecallink"');
                		$retstring.="<script type='text/javascript'>";
                		$retstring.="jQuery(document).ready(function() {";
                		$retstring.='	jQuery("#'.$prefix.'id").click(function() {';
                		$retstring.="    	jQuery('#".$prefix."').focus();";
                		$retstring.='    });';
                		$retstring.='});';
                		$retstring.="</script>";*/
					}
					else
					{
						$retstring.='<button id="'.$prefix.'Button" type="button" class="dpInvisibleButtons">'.img_object($langs->trans("Disabled"), 'calendarday', 'class="datecallink"').'</button>';
					}

					$retstring.='</div>';
					$retstring.='<input type="hidden" id="'.$prefix.'day"   name="'.$prefix.'day"   value="'.$sday.'">'."\n";
					$retstring.='<input type="hidden" id="'.$prefix.'month" name="'.$prefix.'month" value="'.$smonth.'">'."\n";
					$retstring.='<input type="hidden" id="'.$prefix.'year"  name="'.$prefix.'year"  value="'.$syear.'">'."\n";
				}
				else
				{
					$retstring.="Bad value of MAIN_POPUP_CALENDAR";
				}
			}
			// Show date with combo selects
			else
			{
				//$retstring.='<div class="inline-block">';
				// Day
				$retstring.='<select'.($disabled?' disabled':'').' class="flat valignmiddle maxwidth50imp" id="'.$prefix.'day" name="'.$prefix.'day">';

				if ($emptydate || $set_time == -1)
				{
					$retstring.='<option value="0" selected>&nbsp;</option>';
				}

				for ($day = 1 ; $day <= 31; $day++)
				{
					$retstring.='<option value="'.$day.'"'.($day == $sday ? ' selected':'').'>'.$day.'</option>';
				}

				$retstring.="</select>";

				$retstring.='<select'.($disabled?' disabled':'').' class="flat valignmiddle maxwidth75imp" id="'.$prefix.'month" name="'.$prefix.'month">';
				if ($emptydate || $set_time == -1)
				{
					$retstring.='<option value="0" selected>&nbsp;</option>';
				}

				// Month
				for ($month = 1 ; $month <= 12 ; $month++)
				{
					$retstring.='<option value="'.$month.'"'.($month == $smonth?' selected':'').'>';
					$retstring.=dol_print_date(mktime(12, 0, 0, $month, 1, 2000), "%b");
					$retstring.="</option>";
				}
				$retstring.="</select>";

				// Year
				if ($emptydate || $set_time == -1)
				{
					$retstring.='<input'.($disabled?' disabled':'').' placeholder="'.dol_escape_htmltag($langs->trans("Year")).'" class="flat maxwidth50imp valignmiddle" type="number" min="0" max="3000" maxlength="4" id="'.$prefix.'year" name="'.$prefix.'year" value="'.$syear.'">';
				}
				else
				{
					$retstring.='<select'.($disabled?' disabled':'').' class="flat valignmiddle maxwidth75imp" id="'.$prefix.'year" name="'.$prefix.'year">';

					for ($year = $syear - 10; $year < $syear + 10 ; $year++)
					{
						$retstring.='<option value="'.$year.'"'.($year == $syear ? ' selected':'').'>'.$year.'</option>';
					}
					$retstring.="</select>\n";
				}
				//$retstring.='</div>';
			}
		}

		if ($d && $h) $retstring.=($h==2?'<br>':' ');

		if ($h)
		{
			// Show hour
			$retstring.='<select'.($disabled?' disabled':'').' class="flat valignmiddle maxwidth50 '.($fullday?$fullday.'hour':'').'" id="'.$prefix.'hour" name="'.$prefix.'hour">';
			if ($emptyhours) $retstring.='<option value="-1">&nbsp;</option>';
			for ($hour = 0; $hour < 24; $hour++)
			{
				if (strlen($hour) < 2) $hour = "0" . $hour;
				$retstring.='<option value="'.$hour.'"'.(($hour == $shour)?' selected':'').'>'.$hour.(empty($conf->dol_optimize_smallscreen)?'':'H').'</option>';
			}
			$retstring.='</select>';
			if ($m && empty($conf->dol_optimize_smallscreen)) $retstring.=":";
		}

		if ($m)
		{
			// Show minutes
			$retstring.='<select'.($disabled?' disabled':'').' class="flat valignmiddle maxwidth50 '.($fullday?$fullday.'min':'').'" id="'.$prefix.'min" name="'.$prefix.'min">';
			if ($emptyhours) $retstring.='<option value="-1">&nbsp;</option>';
			for ($min = 0; $min < 60 ; $min++)
			{
				if (strlen($min) < 2) $min = "0" . $min;
				$retstring.='<option value="'.$min.'"'.(($min == $smin)?' selected':'').'>'.$min.(empty($conf->dol_optimize_smallscreen)?'':'').'</option>';
			}
			$retstring.='</select>';

			$retstring.='<input type="hidden" name="'.$prefix.'sec" value="'.$ssec.'">';
		}

		// Add a "Now" link
		if ($conf->use_javascript_ajax && $addnowlink)
		{
			// Script which will be inserted in the onClick of the "Now" link
			$reset_scripts = "";

			// Generate the date part, depending on the use or not of the javascript calendar
			$reset_scripts .= 'jQuery(\'#'.$prefix.'\').val(\''.dol_print_date(dol_now(), 'day').'\');';
			$reset_scripts .= 'jQuery(\'#'.$prefix.'day\').val(\''.dol_print_date(dol_now(), '%d').'\');';
			$reset_scripts .= 'jQuery(\'#'.$prefix.'month\').val(\''.dol_print_date(dol_now(), '%m').'\');';
			$reset_scripts .= 'jQuery(\'#'.$prefix.'year\').val(\''.dol_print_date(dol_now(), '%Y').'\');';
			/*if ($usecalendar == "eldy")
            {
                $base=DOL_URL_ROOT.'/core/';
                $reset_scripts .= 'resetDP(\''.$base.'\',\''.$prefix.'\',\''.$langs->trans("FormatDateShortJavaInput").'\',\''.$langs->defaultlang.'\');';
            }
            else
            {
                $reset_scripts .= 'this.form.elements[\''.$prefix.'day\'].value=formatDate(new Date(), \'d\'); ';
                $reset_scripts .= 'this.form.elements[\''.$prefix.'month\'].value=formatDate(new Date(), \'M\'); ';
                $reset_scripts .= 'this.form.elements[\''.$prefix.'year\'].value=formatDate(new Date(), \'yyyy\'); ';
            }*/
			// Update the hour part
			if ($h)
			{
				if ($fullday) $reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
				//$reset_scripts .= 'this.form.elements[\''.$prefix.'hour\'].value=formatDate(new Date(), \'HH\'); ';
				$reset_scripts .= 'jQuery(\'#'.$prefix.'hour\').val(\''.dol_print_date(dol_now(), '%H').'\');';
				if ($fullday) $reset_scripts .= ' } ';
			}
			// Update the minute part
			if ($m)
			{
				if ($fullday) $reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
				//$reset_scripts .= 'this.form.elements[\''.$prefix.'min\'].value=formatDate(new Date(), \'mm\'); ';
				$reset_scripts .= 'jQuery(\'#'.$prefix.'min\').val(\''.dol_print_date(dol_now(), '%M').'\');';
				if ($fullday) $reset_scripts .= ' } ';
			}
			// If reset_scripts is not empty, print the link with the reset_scripts in the onClick
			if ($reset_scripts && empty($conf->dol_optimize_smallscreen))
			{
				$retstring.=' <button class="dpInvisibleButtons datenowlink" id="'.$prefix.'ButtonNow" type="button" name="_useless" value="now" onClick="'.$reset_scripts.'">';
				$retstring.=$langs->trans("Now");
				$retstring.='</button> ';
			}
		}

		// Add a "Plus one hour" link
		if ($conf->use_javascript_ajax && $addplusone)
		{
			// Script which will be inserted in the onClick of the "Add plusone" link
			$reset_scripts = "";

			// Generate the date part, depending on the use or not of the javascript calendar
			$reset_scripts .= 'jQuery(\'#'.$prefix.'\').val(\''.dol_print_date(dol_now(), 'day').'\');';
			$reset_scripts .= 'jQuery(\'#'.$prefix.'day\').val(\''.dol_print_date(dol_now(), '%d').'\');';
			$reset_scripts .= 'jQuery(\'#'.$prefix.'month\').val(\''.dol_print_date(dol_now(), '%m').'\');';
			$reset_scripts .= 'jQuery(\'#'.$prefix.'year\').val(\''.dol_print_date(dol_now(), '%Y').'\');';
			// Update the hour part
			if ($h)
			{
				if ($fullday) $reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
				$reset_scripts .= 'jQuery(\'#'.$prefix.'hour\').val(\''.dol_print_date(dol_now(), '%H').'\');';
				if ($fullday) $reset_scripts .= ' } ';
			}
			// Update the minute part
			if ($m)
			{
				if ($fullday) $reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
				$reset_scripts .= 'jQuery(\'#'.$prefix.'min\').val(\''.dol_print_date(dol_now(), '%M').'\');';
				if ($fullday) $reset_scripts .= ' } ';
			}
			// If reset_scripts is not empty, print the link with the reset_scripts in the onClick
			if ($reset_scripts && empty($conf->dol_optimize_smallscreen))
			{
				$retstring.=' <button class="dpInvisibleButtons datenowlink" id="'.$prefix.'ButtonPlusOne" type="button" name="_useless2" value="plusone" onClick="'.$reset_scripts.'">';
				$retstring.=$langs->trans("DateStartPlusOne");
				$retstring.='</button> ';
			}
		}

		// Add a "Plus one hour" link
		if ($conf->use_javascript_ajax && $adddateof)
		{
			$tmparray=dol_getdate($adddateof);
			$retstring.=' - <button class="dpInvisibleButtons datenowlink" id="dateofinvoice" type="button" name="_dateofinvoice" value="now" onclick="jQuery(\'#re\').val(\''.dol_print_date($adddateof, 'day').'\');jQuery(\'#reday\').val(\''.$tmparray['mday'].'\');jQuery(\'#remonth\').val(\''.$tmparray['mon'].'\');jQuery(\'#reyear\').val(\''.$tmparray['year'].'\');">'.$langs->trans("DateInvoice").'</a>';
		}

		return $retstring;
	}

}
