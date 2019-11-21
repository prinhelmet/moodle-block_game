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
 * Block Game configuration form definition
 *
 * @package   block_blockgame
 * @copyright 2019 Jose Wilson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../../config.php');
require_login();

/**
 *  Block Game config form definition class
 *
 * @package    block_blockgame
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_blockgame_edit_form extends block_edit_form {

    /**
     * Block Game form definition
     *
     * @param mixed $mform
     * @return void
     */
    protected function specific_definition($mform) {
        global $SESSION;

        // Start block specific section in config form.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        if ($SESSION->game->courseid > 1) {
            // Game block instance alternate title.
            $mform->addElement('text', 'config_game_title', get_string('config_title', 'block_blockgame'));
            $mform->setDefault('config_game_title', '');
            $mform->setType('config_game_title', PARAM_MULTILANG);
            $mform->addHelpButton('config_game_title', 'config_title', 'block_blockgame');

            // Control visibility name course.
            $mform->addElement('selectyesno', 'config_show_name_course', get_string('config_name_course', 'block_blockgame'));
            $mform->setDefault('config_show_name_course', 1);
            $mform->addHelpButton('config_show_name_course', 'config_name_course', 'block_blockgame');

            // Control visibility of link info user game.
            $mform->addElement('selectyesno', 'config_show_info', get_string('config_info', 'block_blockgame'));
            $mform->setDefault('config_show_info', 1);
            $mform->addHelpButton('config_show_info', 'config_info', 'block_blockgame');

            // Control score activities.
            $mform->addElement('selectyesno', 'config_score_activities', get_string('config_score_activities', 'block_blockgame'));
            $mform->setDefault('config_score_activities', 1);
            $mform->addHelpButton('config_score_activities', 'config_score_activities', 'block_blockgame');

            // Control bonus of day.
            $bonusdayoptions = array(0 => 0, 5 => 5, 10 => 10, 15 => 15, 20 => 20, 50 => 50, 100 => 100);
            $mform->addElement('select', 'config_bonus_day', get_string('config_bonus_day', 'block_blockgame'), $bonusdayoptions);
            $mform->addHelpButton('config_bonus_day', 'config_bonus_day', 'block_blockgame');

            // Control visibility of rank.
            $mform->addElement('selectyesno', 'config_show_rank', get_string('config_rank', 'block_blockgame'));
            $mform->setDefault('config_show_rank', 1);
            $mform->addHelpButton('config_show_rank', 'config_rank', 'block_blockgame');

            // Control limit rank.
            $limit = array(0 => 0, 5 => 5, 10 => 10, 20 => 20, 50 => 50, 100 => 100);
            $mform->addElement('select', 'config_limit_rank', get_string('config_limit_rank', 'block_blockgame'), $limit);
            $mform->addHelpButton('config_limit_rank', 'config_limit_rank', 'block_blockgame');

            // Preserve user identity.
            $mform->addElement('selectyesno', 'config_show_identity', get_string('config_identity', 'block_blockgame'));
            $mform->setDefault('config_show_identity', 0);
            $mform->disabledIf('config_show_identity', 'config_show_rank', 'eq', 0);
            $mform->addHelpButton('config_show_identity', 'config_identity', 'block_blockgame');

            // Control visibility of score.
            $mform->addElement('selectyesno', 'config_show_score', get_string('config_score', 'block_blockgame'));
            $mform->setDefault('config_show_score', 1);
            $mform->addHelpButton('config_show_score', 'config_score', 'block_blockgame');

            // Control visibility of level.
            $mform->addElement('selectyesno', 'config_show_level', get_string('config_level', 'block_blockgame'));
            $mform->setDefault('config_show_level', 1);
            $mform->addHelpButton('config_show_level', 'config_level', 'block_blockgame');

            // Options controlling level up.
            $levels = array(4 => 4, 6 => 6, 8 => 8, 10 => 10, 12 => 12, 15 => 15);
            $mform->addElement('select', 'config_level_number', get_string('config_level_number', 'block_blockgame'), $levels);
            $mform->setDefault('config_level_number', 4);
            $mform->disabledIf('config_level_number', 'config_show_level', 'eq', 0);
            $mform->addHelpButton('config_level_number', 'config_level_number', 'block_blockgame');

            $leveluppoints = array(1 => 300, 2 => 500, 3 => 1000, 4 => 2000,
                5 => 4000, 6 => 6000, 7 => 10000, 8 => 20000,
                9 => 30000, 10 => 50000, 11 => 70000, 12 => 100000,
                13 => 150000, 14 => 300000, 15 => 500000);
            for ($i = 1; $i <= count($leveluppoints); $i++) {
                $mform->addElement('text', 'config_level_up' . $i, get_string('config_level_up' . $i, 'block_blockgame'));
                $mform->setDefault('config_level_up' . $i, $leveluppoints[$i]);
                $mform->disabledIf('config_level_up' . $i, 'config_show_level', 'eq', 0);
                foreach ($levelupoptions as $level) {
                    if ($level < $i) {
                        $mform->disabledIf('config_level_up' . $i, 'config_level_number', 'eq', $level);
                    }
                }
                $mform->setType('config_level_up' . $i, PARAM_INT);
                $mform->addHelpButton('config_level_up' . $i, 'config_level_up' . $i, 'block_blockgame');
            }
        }
    }

}
