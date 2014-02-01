<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initRoutes() {

    }
    
    protected function _initSession() {
            Zend_Session::start();
    }
}
