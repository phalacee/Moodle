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
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot.'/message/output/lib.php');
// Need an app token defined by the user, and the user's id
// Access should be limited to Teachers+

class message_output_pushover extends message_output {

    /**
     * Sends a message by taking what it receives from the Moodle
     * system and throwing sending it to pushover.
     * @param object $message Message being sent
     */
    public function send_message($message) {
        global $DB, $USER, $CFG;

        // Skip users that allowed access around or aren't configured.
        if ($message->userto->auth === 'nologin' or $message->userto->suspended or $message->userto->deleted or !$this->is_user_configured($message->userto)) {
            return true;
        }

        $apptok  = $CFG->apptoken;
        $usertok = get_user_preferences( 'message_processor_pushover_usertoken', '', $message->userto);
        $title   = $message->subject;
        $messtxt = $message->fullmessage;

        curl_setopt_array($ch = curl_init(), array(
            CURLOPT_URL => "https://api.pushover.net/1/messages.json",
            CURLOPT_POSTFIELDS => array(
                "token"   => $apptok,
                "user"    => $usertok,
                "title"   => $title,
                "message" => $messtxt,
            )
        ));
        curl_exec($ch);
        curl_close($ch);

        return $message->savedmessageid;
    }

    /**
     * Defines the config form fragment used on user
     * messaging preferences interface (message/edit.php)
     * @param object $preferences Preferences form to modify
     * @return string Preference fields to add
     */
    public function config_form($preferences) {
       if (!$this->is_system_configured()) {
            return get_string('notconfigured','message_pushover');
        } else {
            return get_string('usertoken', 'message_pushover').': <input size="30" name="pushover_usertoken" value="'.s($preferences->pushover_usertoken).'" />';
        }
    }

    /**
     * Processes the data from the config form fragment
     * (used in message/edit.php)
     * @param object $form Form object
     * @param array $preferences Preference set
     */
    public function process_form($form, &$preferences) {
       if (isset($form->pushover_usertoken) && !empty($form->pushover_usertoken)) {
            $preferences['message_processor_pushover_usertoken'] = $form->pushover_usertoken;
        }
    }

    /**
     * Loads initial config data from the database to
     * populate the form with
     * @param array $preferences Preference set
     * @param int $userid ID of user to get preferences for
     */
    public function load_data(&$preferences, $userid) {
       $preferences->pushover_usertoken = get_user_preferences( 'message_processor_pushover_usertoken', '', $userid);
    }

    /**
     * Returns whether all the necessary config settings
     * have been set to allow this plugin to be used
     * @return bool True if system is ready
     */
    public function is_system_configured() {
        global $CFG;
        return (!empty($CFG->apptoken));
    }

    /**
     * Returns whether the user has completed all the necessary settings
     * in their profile to allow this plugin to be used
     * @param object $user The user, defaults to $USER.
     * @return bool True if user is configured
     */
    public function is_user_configured($user = null) {
        global $USER;

        if (is_null($user)) {
            $user = $USER;
        }
        return (bool)get_user_preferences('message_processor_pushover_usertoken', null, $user->id);
    }

    /**
     * Default message output settings for this output, for
     * message providers that do not specify what the settings should be for
     * this output in the messages.php file
     * @return int Message settings mask
     */
    public function get_default_messaging_settings() {
        return MESSAGE_PERMITTED;
    }
}
// u58zXrAvwYb5DHCjsVV8MFd9i9f1cM
