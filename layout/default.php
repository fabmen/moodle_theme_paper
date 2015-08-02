<?php
// This file is part of The Bootstrap 3 Moodle theme
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


$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);

$knownregionpre = $PAGE->blocks->is_known_region('side-pre');
$knownregionpost = $PAGE->blocks->is_known_region('side-post');

$regions = theme_paper_bootstrap_grid($hassidepre, $hassidepost);
$PAGE->set_popup_notification_allowed(false);
$haslogo = (!empty($PAGE->theme->settings->logo));
$html = theme_paper_get_html_for_settings($OUTPUT, $PAGE);

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<nav role="navigation" class="navbar navbar-default <?php echo $html->navbarclass ?>">
    <div class="container-fluid">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle toggle-left hidden-lg" data-toggle="sidebar" data-target=".sidebar">
            <span class="sr-only">Toggle menu sidebar</span>
          <span class="fa fa-bars"></span>
         
        </button>
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#moodle-navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="fa fa-ellipsis-v"></span>
        </button>
        <a href="<?php echo $CFG->wwwroot;?>"><?php if ($haslogo) {
 echo html_writer::empty_tag('img', array('src'=>$PAGE->theme->settings->logo, 'class'=>'logo')); }

 else { ?><a class="navbar-brand" href="<?php echo $CFG->wwwroot;?>"><?php echo $SITE->shortname; }?></a>
         
    </div>

    <div id="moodle-navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav ">
               <?php echo $OUTPUT->custom_menu(); ?>
        </ul>
        <ul class="nav navbar-nav navbar-right">
             <?php echo $OUTPUT->user_menu(); ?>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
        </ul>
    </div>
    </div>
</nav>


<div id="page" class="container-fluid">
    
    
    <div id="page-content" class="row">
        <div id="region-main" class="sidebar-content-animate <?php echo $regions['content']; ?>">
        <header id="page-header" class="clearfix">
            <div class="container-fluid">
                <a href="<?php echo $CFG->wwwroot ?>" class="logo"></a>
                <?php echo $OUTPUT->page_heading(); ?>
            </div>
            <div id="page-navbar" class="clearfix">
                <nav class="breadcrumb-nav" role="navigation" aria-label="breadcrumb"><?php echo $OUTPUT->navbar(); ?></nav>
                <div class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); ?></div>
                
            </div>

        <div id="course-header">
            <?php echo $OUTPUT->course_header(); ?>
        </div>
        </header>
            <?php
            echo $OUTPUT->course_content_header();

            echo $OUTPUT->main_content();
            echo $OUTPUT->course_content_footer();
            ?>
        </div>

        <?php
        if ($knownregionpre) {
            echo $OUTPUT->blocks('side-pre', $regions['pre'] . ' sidebar sidebar sidebar-left sidebar-open sidebar-lg-show sidebar-animate' . $html->sidebarclass);
        }?>
        <?php
        if ($knownregionpost) {
            echo $OUTPUT->blocks('side-post', 'sidebar-other-animate ' .$regions['post']);
        }?>
    </div>

    <footer id="page-footer">
        <div id="course-footer"><?php echo $OUTPUT->course_footer(); ?></div>
        <p class="helplink"><?php echo $OUTPUT->page_doc_link(); ?></p>
        <?php
        echo $OUTPUT->login_info();
        echo $OUTPUT->home_link();
        echo $OUTPUT->standard_footer_html();
        ?>
    </footer>

    <?php echo $OUTPUT->standard_end_of_body_html() ?>

</div>
</body>
</html>
