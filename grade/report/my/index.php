<?php
// This file is part of Moodle - http://moodle.org/
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

/**
 * The gradebook overview report
 *
 * @package    gradereport_my
 * @copyright  2014 Jason Fowler - http://phalacee.com/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/my/lib.php';

$courseid = 1;
$userid   = $USER->id;
$title = get_string('mygrades', 'gradereport_my');
$navcontext = context_user::instance($USER->id);

/// basic access checks
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);

$PAGE->set_url(new moodle_url('/grade/report/my/index.php'));
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_context($navcontext);

$context = context_course::instance($course->id);

$systemcontext = context_system::instance();
require_capability('gradereport/overview:view', $context);

if (empty($userid)) {
    require_capability('moodle/grade:viewall', $systemcontext);

} else {
    if (!$DB->get_record('user', array('id'=>$userid, 'deleted'=>0)) or isguestuser($userid)) {
        print_error('invaliduserid');
    }
}

$access = false;
if (has_capability('moodle/grade:viewall', $systemcontext)) {
    //ok - can view all course grades
    $access = true;

} else if ($userid == $USER->id and has_capability('moodle/grade:viewall', $context)) {
    //ok - can view any own grades
    $access = true;

} else if ($userid == $USER->id and has_capability('moodle/grade:view', $context) and $course->showgrades) {
    //ok - can view own course grades
    $access = true;

} else if (has_capability('moodle/grade:viewall', context_user::instance($userid)) and $course->showgrades) {
    // ok - can view grades of this user- parent most probably
    $access = true;
}

if (!$access) {
    // no access to grades!
    print_error('nopermissiontoviewgrades', 'error',  $CFG->wwwroot.'/course/view.php?id='.$courseid);
}

/// return tracking object
$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'my', 'courseid'=>$course->id, 'userid'=>$userid));

/// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = array();
}
$USER->grade_last_report[$course->id] = 'my';

//first make sure we have proper final grades - this must be done before constructing of the grade tree
grade_regrade_final_grades($courseid);

// Create a report instance
$report = new grade_report_my($userid, $gpr, $context);

// print the page
//print_grade_page_head($courseid, 'report', 'overview', get_string('pluginname', 'gradereport_overview'). ' - '.fullname($report->user));
echo $OUTPUT->header();
if ($report->fill_table()) {
    echo '<br />'.$report->print_table(true);
}
echo $OUTPUT->footer();


