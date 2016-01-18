<?php
// This file is part of The Bootstrap Moodle theme
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_paper
 * @copyright  2012
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_paper_core_renderer extends core_renderer {

    public function notification($message, $classes = 'notifyproblem') {
        $message = clean_text($message);

        if ($classes == 'notifyproblem') {
            return html_writer::div($message, 'alert alert-danger');
        }
        if ($classes == 'notifywarning') {
            return html_writer::div($message, 'alert alert-warning');
        }
        if ($classes == 'notifysuccess') {
            return html_writer::div($message, 'alert alert-success');
        }
        if ($classes == 'notifymessage') {
            return html_writer::div($message, 'alert alert-info');
        }
        if ($classes == 'redirectmessage') {
            return html_writer::div($message, 'alert alert-block alert-info');
        }
        if ($classes == 'notifytiny') {
            // Not an appropriate semantic alert class!
            return $this->debug_listing($message);
        }
        return html_writer::div($message, $classes);
    }

    private function debug_listing($message) {
        $message = str_replace('<ul style', '<ul class="list-unstyled" style', $message);
        return html_writer::tag('pre', $message, array('class' => 'alert alert-info'));
    }

    public function navbar() {
        $items = $this->page->navbar->get_items();
        if (empty($items)) { // MDL-46107.
            return '';
        }
        $breadcrumbs = '';
        foreach ($items as $item) {
            $item->hideicon = true;
            $breadcrumbs .= '<li>'.$this->render($item).'</li>';
        }
        return "<ol class=breadcrumb>$breadcrumbs</ol>";
    }

    public function custom_menu($custommenuitems = '') {
        // The custom menu is always shown, even if no menu items
        // are configured in the global theme settings page.
        global $CFG;

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) { // MDL-45507.
            $custommenuitems = $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render_custom_menu($custommenu);
    }

    protected function render_custom_menu(custom_menu $menu) {

        // TODO: eliminate this duplicated logic, it belongs in core, not
        // here. See MDL-39565.
        $firstmenu = new custom_menu('', '');
        if (isloggedin() && !isguestuser() ) {
            $branchtitle = get_string('mycourses');
            $branchlabel = '<i class="fa fa-briefcase"></i> '.$branchtitle;
            $branchurl   = new moodle_url('/my/index.php');
            $branchsort  = -10;

            $branch = $firstmenu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
            if ($courses = enrol_get_my_courses(NULL, 'fullname ASC')) {
                foreach ($courses as $course) {
                    if ($course->visible){
                        $branch->add(format_string($course->fullname), new moodle_url('/course/view.php?id='.$course->id), format_string($course->shortname));
                    }
                }
        } }
        $branchtitle = get_string('showallcourses');
        $branchlabel = '<i class="fa fa-sitemap"></i> '.$branchtitle;
        $branchurl   = new moodle_url('/course/index.php');
        $branchsort  = -20;
        $firstmenu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
        $content = '';
        foreach ($firstmenu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }
        $content .= '</ul><ul class="nav navbar-nav">';
        $addlangmenu = true;
        $langs = get_string_manager()->get_list_of_translations();
        if (count($langs) < 2
            or empty($CFG->langmenu)
            or ($this->page->course != SITEID and !empty($this->page->course->lang))) {
            $addlangmenu = false;
        }

        if (!$menu->has_children() && $addlangmenu === false) {
            return '';
        }

        if ($addlangmenu) {
            $strlang =  get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $menu->add('<i class="fa fa-flag"></i>&nbsp;'.$currentlang, new moodle_url('#'), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }
        
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }

        return $content;
    }

    public function user_menu($user = null, $withlinks = null) {
        $usermenu = new custom_menu('', '');
        
         global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = 'usermenu';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin() || $loginpage) {
            $branchtitle = get_string('loggedinnot', 'moodle');
            $branchlabel = get_string('loggedinnot', 'moodle');
            if (!$loginpage) {
                $returnstr = $loginurl;
                
            }
           

        } else if (isguestuser()) {
                 // If logged in as a guest user, show a string to that effect.
            $branchtitle = get_string('loggedinasguest');
            $branchlabel = get_string('loggedinasguest');
            if (!$loginpage && $withlinks) {
                $returnstr =$loginurl;
            }

            
        }else{

            // Get some navigation opts.
            $opts = user_get_user_navigation_info($user, $this->page);
    
            $avatarcontents =$opts->metadata['useravatar'];
            $usertextcontents = $opts->metadata['userfullname'];
    
            // Other user.
            if (!empty($opts->metadata['asotheruser'])) {
                $avatarcontents .= $opts->metadata['realuseravatar'];
                $usertextcontents = $opts->metadata['realuserfullname'];
                $usertextcontents .=get_string(
                        'loggedinas',
                        'moodle',
                        $opts->metadata['userfullname']
                    );
            }
    
            // Role.
            if (!empty($opts->metadata['asotherrole'])) {
                $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
                $usertextcontents .= $opts->metadata['rolename'];
            }
    
            // User login failures.
            if (!empty($opts->metadata['userloginfail'])) {
                $usertextcontents .= $opts->metadata['userloginfail'];
            }
    
            // MNet.
            if (!empty($opts->metadata['asmnetuser'])) {
                $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
                $usertextcontents .=$opts->metadata['mnetidprovidername'];
            }
    
            $branchtitle = $usertextcontents;
            $branchlabel = $avatarcontents.$usertextcontents;
        }
        $branchurl   = new moodle_url('#');
        $branchsort  = 1000;
        $branch = $usermenu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
        if ($returnstr!=''){
            $branch->add('<i class="fa fa-sign-in"></i>&nbsp;' .get_string('login'), new moodle_url($returnstr), get_string('login'));
        }else if (!$loginpage) {
            if ($withlinks) {
                $navitemcount = count($opts->navitems);
                $idx = 0;
                foreach ($opts->navitems as $key => $value) {
    
                    switch ($value->itemtype) {
                        case 'divider':
                            // If the nav item is a divider, add one and skip link processing.
                           $branch->add('####');
                            break;
    
                        case 'invalid':
                            // Silently skip invalid entries (should we post a notification?).
                            break;
    
                        case 'link':
                            // Process this as a link item.
                            $pix = null;
                            if (isset($value->pix) && !empty($value->pix)) {
                                switch (substr($value->pix,2)) {
                                    case 'course':
                                        $faicon = 'dashboard';
                                        break;
                                    case 'grades':
                                        $faicon = 'calculator';
                                        break;
                                    case 'message':
                                        $faicon = 'envelope';
                                        break;
                                    case 'preferences':
                                        $faicon = 'cogs';
                                        break;
                                     case 'logout':
                                        $faicon = 'sign-out';
                                        break;
                                    case 'login':
                                        $faicon = 'sign-in';
                                        break;
                                    default :
                                        $faicon = substr($value->pix,2);
                                }
                                $label = '<i class="fa fa-'.$faicon.'"></i>&nbsp;' . $value->title;
                            } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                                $label = html_writer::img(
                                    $value->imgsrc,
                                    $value->title,
                                    array('class' => 'iconsmall')
                                ) . $value->title;
                            }
                           
                            $branch->add($label, new moodle_url($value->url), $value->title);
                            break;
                    }
    
                    $idx++;
    
                    // Add dividers after the first item and before the last item.
                    if ($idx == 1 || $idx == $navitemcount - 1) {
                        $branch->add('####');
                    }
                }
            }
        }            
        $content = '';
        foreach ($usermenu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }

        return $content;

    }

    

    protected function render_custom_menu_item(custom_menu_item $menunode, $level = 0 ) {
        static $submenucount = 0;

        if ($menunode->has_children()) {

            if ($level == 1) {
                $dropdowntype = 'dropdown';
            } else {
                $dropdowntype = 'dropdown-submenu';
            }

            $content = html_writer::start_tag('li', array('class' => $dropdowntype));
            // If the child has menus render it as a sub menu.
            $submenucount++;
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }
            $linkattributes = array(
                'href' => $url,
                'class' => 'dropdown-toggle',
                'data-toggle' => 'dropdown',
                'title' => $menunode->get_title(),
            );
            $content .= html_writer::start_tag('a', $linkattributes);
            $content .= $menunode->get_text();
            if ($level == 1) {
                $content .= '<b class="caret"></b>';
            }
            $content .= '</a>';
            $content .= '<ul class="dropdown-menu">';
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode, 0);
            }
            $content .= '</ul>';
        } else {
            // The node doesn't have children so produce a final menuitem.
            // Also, if the node's text matches '####', add a class so we can treat it as a divider.
            if (preg_match("/^#+$/", $menunode->get_text())) {
                // This is a divider.
                $content = '<li class="divider">&nbsp;</li>';
            } else {
                $content = '<li>';
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('title' => $menunode->get_title()));
                $content .= '</li>';
            }
        }
        return $content;
    }

    protected function render_tabtree(tabtree $tabtree) {
        if (empty($tabtree->subtree)) {
            return '';
        }
        $firstrow = $secondrow = '';
        foreach ($tabtree->subtree as $tab) {
            $firstrow .= $this->render($tab);
            if (($tab->selected || $tab->activated) && !empty($tab->subtree) && $tab->subtree !== array()) {
                $secondrow = $this->tabtree($tab->subtree);
            }
        }
        return html_writer::tag('ul', $firstrow, array('class' => 'nav nav-tabs nav-justified')) . $secondrow;
    }

    protected function render_tabobject(tabobject $tab) {
        if ($tab->selected or $tab->activated) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'active'));
        } else if ($tab->inactive) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'disabled'));
        } else {
            if (!($tab->link instanceof moodle_url)) {
                // Backward compatibility when link was passed as quoted string.
                $link = "<a href=\"$tab->link\" title=\"$tab->title\">$tab->text</a>";
            } else {
                $link = html_writer::link($tab->link, $tab->text, array('title' => $tab->title));
            }
            return html_writer::tag('li', $link);
        }
    }

    public function box($contents, $classes = 'generalbox', $id = null, $attributes = array()) {
        if (isset($attributes['data-rel']) && $attributes['data-rel'] === 'fatalerror') {
            return html_writer::div($contents, 'alert alert-danger', $attributes);
        }
        return parent::box($contents, $classes, $id, $attributes);
    }


}
