<?php
class Booki_DateTime extends DateTime {
    private $_date_time;
    /*solves session serialization in php 5.2*/
    public function __toString() {
        return $this->format('c'); // format as ISO 8601
    }
    
    public function __sleep() {
        $this->_date_time = $this->format('c');
        return array('_date_time');
    }
    
    public function __wakeup() {
        $this->__construct($this->_date_time);
    }
}
?>