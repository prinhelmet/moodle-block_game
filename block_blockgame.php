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
 * Game block definition
 *
 * @package    block_blockgame
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/blockgame/libgame.php');

require_login();

/**
 *  Block Game config form definition class
 *
 * @package    block_blockgame
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_blockgame extends block_base {

    /**
     * Sets the block title
     *
     * @return none
     */
    public function init() {
        $this->title = get_string('game_title_default', 'block_blockgame');
    }

    /**
     * Controls the block title based on instance configuration
     *
     * @return bool
     */
    public function specialization() {
        global $course;

        // Need the bigger course object.
        $this->course = $course;

        // Override the block title if an alternative is set.
        if (isset($this->config->game_title) && trim($this->config->game_title) != '') {
            $this->title = format_string($this->config->game_title);
        }
    }

    /**
     * Defines where the block can be added
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'course-view' => true,
            'site-index' => true,
            'mod' => true,
            'my' => true
        );
    }

    /**
     * Controls global configurability of block
     *
     * @return bool
     */
    public function instance_allow_config() {
        return false;
    }

    /**
     * Controls global configurability of block
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Controls if a block header is shown based on instance configuration
     *
     * @return bool
     */
    public function hide_header() {
        return isset($this->config->show_header) && $this->config->show_header == 0;
    }

    /**
     * Creates the block's main content
     *
     * @return string
     */
    public function get_content() {

        global $USER, $SESSION, $COURSE, $OUTPUT, $CFG;

        // Load Game of user.
        $game = new stdClass();
        $game->courseid = $COURSE->id;
        $game->userid = $USER->id;

        $game = load_game($game);
        $game->config = $this->config;

        if ($COURSE->id == 1) {
            $game->config = get_config('blockgame');
        }
        $SESSION->game = $game;

        // Get block ranking configuration.
        $cfggame = get_config('blockgame');

        if (!file_exists($CFG->dirroot . '/blocks/blockgame/game.js')) {
            $context = stream_context_create(array('http' => array('method' => 'POST', 'content' => '')));
            $contents = file_get_contents($CFG->wwwroot . '/blocks/blockgame/create_game_js.php', null, $context);
        }
        if (isset($this->content)) {
            return $this->content;
        }

        // Start the content, which is primarily a table.
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        $showavatar = !isset($cfggame->use_avatar) || $cfggame->use_avatar == 1;
        $changeavatar = !isset($cfggame->change_avatar_course) || $cfggame->change_avatar_course == 1;
        $shownamecourse = !isset($game->config->show_name_course) || $game->config->show_name_course == 1;
        $showidentity = !isset($game->config->show_identity) || $game->config->show_identity == 1;
        $showrank = !isset($game->config->show_rank) || $game->config->show_rank == 1;
        $showinfo = !isset($game->config->show_info) || $game->config->show_info == 1;
        $showscore = !isset($game->config->show_score) || $game->config->show_score == 1;
        $showlevel = !isset($game->config->show_level) || $game->config->show_level == 1;
        $scoreactivities = !isset($game->config->score_activities) || $game->config->score_activities == 1;

        $levelnumber = 0;
        // Config level up.
        if ($showlevel) {
            $levelnumber = (int) $game->config->level_number;
            $levelup[0] = (int) $game->config->level_up1;
            $levelup[1] = (int) $game->config->level_up2;
            $levelup[2] = (int) $game->config->level_up3;
            $levelup[3] = (int) $game->config->level_up4;
            $levelup[4] = (int) $game->config->level_up5;
            $levelup[5] = (int) $game->config->level_up6;
            $levelup[6] = (int) $game->config->level_up7;
            $levelup[7] = (int) $game->config->level_up8;
            $levelup[8] = (int) $game->config->level_up9;
            $levelup[9] = (int) $game->config->level_up10;
            $levelup[10] = (int) $game->config->level_up11;
            $levelup[11] = (int) $game->config->level_up12;
            $levelup[12] = (int) $game->config->level_up13;
            $levelup[13] = (int) $game->config->level_up14;
            $levelup[14] = (int) $game->config->level_up15;
        }

        // Bonus of day.
        if (isset($game->config->bonus_day)) {
            $addbonusday = $game->config->bonus_day;
        } else {
            $addbonusday = 0;
        }
        if ($addbonusday > 0) {
            bonus_of_day($game, $addbonusday);
        }

        // Bonus of badge.

        if (isset($cfggame->bonus_badge)) {
            $bonusbadge = $cfggame->bonus_badge;
            $game = score_badge($game, $bonusbadge);
        }

        if ($scoreactivities) {
            score_activities($game);
            $game = ranking($game);
            if ($showlevel) {
                $game = set_level($game, $levelup, $levelnumber);
            }
        } else {
            no_score_activities($game);
            $game = ranking($game);
            if ($showlevel) {
                $game = set_level($game, $levelup, $levelnumber);
            }
        }

        $table = new html_table();
        $table->attributes = array('class' => 'gameTable', 'style' => 'width: 100%;');

        if ($USER->id != 0) {
            $row = array();
            $userpictureparams = array('size' => 20, 'link' => false, 'alt' => 'User');
            $userpicture = $OUTPUT->user_picture($USER, $userpictureparams);
            if ($showavatar) {
                if ($COURSE->id == 1 || $changeavatar) {
                    $userpicture = '<a href="' . $CFG->wwwroot
                            . '/blocks/blockgame/set_avatar_form.php?id=' . $COURSE->id . '&avatar='
                            . $game->avatar . '">' . '<img hspace="5" src="' . $CFG->wwwroot . '/blocks/blockgame/pix/a'
                            . $game->avatar . '.png" height="40" width="40"/></a>';
                } else {
                    $userpicture = '<img hspace="5" src="' . $CFG->wwwroot . '/blocks/blockgame/pix/a'
                            . $game->avatar . '.png" height="40" width="40"/>';
                }
            }
            $linkinfo = '';
            if ($showinfo) {
                $linkinfo = '<a href="' . $CFG->wwwroot . '/blocks/blockgame/perfil_gamer.php?id='
                        . $COURSE->id . '">' . '<img hspace="12" src="'
                        . $CFG->wwwroot . '/blocks/blockgame/pix/info.png"/></a>';
            }
            $row[] = $userpicture . get_string('label_you', 'block_blockgame') . $linkinfo;
            $table->data[] = $row;
            $row = array();
            $icontxt = $OUTPUT->pix_icon('logo', '', 'theme');
            if ($COURSE->id != 1 && $shownamecourse) {
                $coursetxt = '(' . $COURSE->shortname . ')';
                $row[] = $coursetxt;
                $table->data[] = $row;
            }
            if ($showrank) {
                $row = array();
                $icontxt = '<img src="' . $CFG->wwwroot . '/blocks/blockgame/pix/rank.png" height="20" width="20"/>';
                $row[] = $icontxt . ' ' . get_string('label_rank', 'block_blockgame')
                        . ': ' . $game->rank . '&ordm; / ' . get_players($game->courseid);
                $table->data[] = $row;
            }
            if ($showscore) {
                $row = array();
                $icontxt = '<img src="' . $CFG->wwwroot . '/blocks/blockgame/pix/score.png" height="20" width="20"/>';
                $row[] = $icontxt . ' ' . get_string('label_score', 'block_blockgame') . ': '
                        . (int) ($game->score + $game->score_activities + $game->score_badges) . '';
                $table->data[] = $row;
            }
            if ($showlevel) {
                $row = array();
                $icontxt = '<img src="' . $CFG->wwwroot . '/blocks/blockgame/pix/level.png" height="20" width="20"/>';
                $row[] = $icontxt . ' ' . get_string('label_level', 'block_blockgame') . ': ' . $game->level . '';
                $table->data[] = $row;

                $percent = 0;
                $nextlevel = $game->level + 1;
                if ($nextlevel <= $levelnumber) {
                    $total = (int) ($game->score + $game->score_activities + $game->score_badges);
                    $percent = ($total * 100) / $levelup[$game->level];
                }
                $row = array();
                $progressbar = '<div style="height:12px; padding:2px; background-color:#ccc; text-align:right; font-size:12px;">';
                $progressbar .= '<div style="height: 8px; width:' . $percent;
                $progressbar .= '%; padding: 0px; background-color: #356ebc;"></div>';
                $progressbar .= get_string('next_level', 'block_blockgame') . ' =>' . $levelup[$game->level] . '</div>';
                $row[] = $progressbar;
                $table->data[] = $row;
            }
            $row = array();
            $icontxtrank = '<hr/><table border="0" width="100%"><tr>';
            if ($showrank) {
                $icontxtrank .= '<td align="left" width="50%"><a href="'
                        . $CFG->wwwroot . '/blocks/blockgame/rank_game.php?id=' . $COURSE->id . '"><img alt="'
                        . get_string('label_rank', 'block_blockgame') . '" title="'
                        . get_string('label_rank', 'block_blockgame') . '" src="'
                        . $CFG->wwwroot . '/blocks/blockgame/pix/rank_list.png" height="25" width="25"/></a></td>';
            }
            $icontxtrank .= '<td align="right" width="50%"><a href="' . $CFG->wwwroot . '/blocks/blockgame/help_game.php?id='
                    . $COURSE->id . '"><img alt="' . get_string('help', 'block_blockgame') . '" title="'
                    . get_string('help', 'block_blockgame') . '" src="'
                    . $CFG->wwwroot . '/blocks/blockgame/pix/help.png"  height="25" width="25"/></a></td>';
            $icontxtrank .= '</tr></table>';
            $row[] = $icontxtrank;
            $table->data[] = $row;
        } else {
            $row[] = '';
            $table->data[] = $row;
        }
        $this->content->text .= HTML_WRITER::table($table);

        $this->content->footer = '';
        return $this->content;
    }

}
