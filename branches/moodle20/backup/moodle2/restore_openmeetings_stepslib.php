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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_openmeetings_activity_task
 */

/**
 * Structure step to restore one openmeetings activity
 */
class restore_openmeetings_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('openmeetings', '/activity/openmeetings');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_openmeetings($data) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/openmeetings/openmeetings_gateway.php');

        $data = (object)$data;
        $data->course = $this->get_courseid();

        $openmeetings_gateway = new openmeetings_gateway();
        if ($openmeetings_gateway->openmeetings_loginuser()) {
            //Roomtype 0 means its and recording, we don't need to create a room for that
            if ($data->type != 0) {
                $data->room_id = $openmeetings_gateway->openmeetings_createRoomWithModAndType($data);
            }
        }
        // insert the openmeetings record
        $newitemid = $DB->insert_record('openmeetings', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
        // Add openmeetings related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_openmeetings', 'intro', null);
    }
}