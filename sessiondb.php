<?php

/*
=====================================================
* Get EE and its modules to use DB-based PHP sessions, not the filesystem
* Andy Hebrank, 2015
* https://insidenewcity.com
* 
* Based on http://shiflett.org/articles/storing-sessions-in-a-database
*
* on some distributions, you'll want to check for reasonable garbage collection settings
* or your session table may keep growing.  here are some pretty sensible values for short
* lived sessions:
* 
*   session.gc_probability = 1
*   session.gc_divisor = 1000
*   session.gc_maxlifetime = 1440

=====================================================

*/

/**
 * Helper function: use the old-style EE instance for older EE versions
 */

Class Sessiondb {

  var $table_name = "php_sessions";


  function hook_session() {
    // set up the hooks
    session_set_save_handler(array($this, '_open'),
                             array($this, '_close'),
                             array($this, '_read'),
                             array($this, '_write'),
                             array($this, '_destroy'),
                             array($this, '_clean'));
  }

  /**
   * Check if the database table for sessions exists
   */
  private function check_db() {
    if (ee()->db->table_exists($this->table_name)) {
      return true;
    }
    return $this->make_db();
  }

  /**
   * Build the session table if it's not there already
   */
  private function make_db() {
    ee()->load->dbforge();
    $fields = array(
      'id' => array('type' => 'varchar', 'constraint' => '32'),
      'access' => array('type' => 'integer', 'constraint' => '10', 'unsigned' => true),
      'data' => array('type' => 'text')
      );
    ee()->dbforge->add_field($fields);
    ee()->dbforge->add_key('id', true);
    ee()->dbforge->create_table($this->table_name);

    return true;
  }

  /**
   * session_start()
   */
  function _open() {
    $this->check_db();
  }

  function _close() {
    // no need to do anything
  }

  /**
   * Read session data from the db
   */
  function _read($id) {
    $result = ee()->db->select('data')
      ->where('id', $id)
      ->get($this->table_name);

    if (!$result->num_rows()) {
      return '';
    }

    return $result->row()->data;
  }

  /**
   * Write a session variable
   */
  function _write($id, $data) {
    $access = time();

    $this->_destroy($id);
    return ee()->db->insert($this->table_name, array(
      'id' => $id,
      'data' => $data,
      'access' => $access
      ));
  }

  /**
   * Delete a session variable
   */
  function _destroy($id) {
    return ee()->db->delete($this->table_name, 
      array('id' => $id));
  }

  /**
   * Clean out old session records
   */
  function _clean($max) {
    $old = time() - $max;
    
    return ee()->db->where('access <', $old)
      ->delete($this->table_name);
  }

}