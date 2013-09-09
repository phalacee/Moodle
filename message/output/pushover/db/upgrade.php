<?php

/**
 *
 * Pushover for Moodle is distributed as GPLv3 software, and is provided free of charge without warranty.
 * A full copy of this licence can be found @ http://www.gnu.org/licenses/gpl.html
 *
 * @package message_pushover
 * @author Jason Fowler <phalacee@gmail.com>
 * @copyright Copyright &copy; 2013 Jason Fowler. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013090601
 * @since 2013090601
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

function xmldb_message_pushover_upgrade($oldversion) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();
    return true;
}

?>
