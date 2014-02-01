<?php

class Application_Model_DbTable_Places extends Zend_Db_Table_Abstract {

    protected $_name = 'places';

    public function addPlace($andrewId) {
        $data = array(
            'student_andrew_id' => $andrewId
        );

        $this->insert($data);
    }
    
    public function getPlacesContainingStr($str) {
        $rows = $this->fetchAll("name LIKE '%$str%'");
        return $rows;
    }

    public function updateCourse($status) {
        $data = array(
            'status' => $status
        );
        $this->update($data, "id = $status");
    }

}