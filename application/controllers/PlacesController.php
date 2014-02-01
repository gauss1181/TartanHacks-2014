<?php

class PlacesController extends Zend_Controller_Action {

    /**
     * Returns a JSON object containing the suggested places
     * based on the user-typed string
     */
    public function getsuggestionsAction() {
        $this->_helper->layout()->disableLayout(); /* Disable the layout file */
        $this->_helper->viewRenderer->setNoRender(true); /* Set to not using the template file */

        $string = $this->getRequest()->getParam('string');

        $dbPlaces = new Application_Model_DbTable_Places();

        $ret = $dbPlaces->getPlacesContainingStr($string)->toArray();

        array_walk($ret, function (&$n) {
            unset($n['type']);
            unset($n['contains']);

        });

        echo Zend_Json::encode($ret);
    }

    /**
     * Get all categories
     */
    public function getcategoriesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $dbPlaces = new Application_Model_DbTable_Places();

        $ret = $dbPlaces->getCategories()->toArray();

        array_walk($ret, function(&$n) { 
            unset($n['avg_cost']);
            unset($n['avg_rating']);
            unset($n['close_time']);
            unset($n['open_time']);
            unset($n['address']);
            unset($n['type']);
            unset($n['avg_stay_time']);
        });

        echo Zend_Json::encode($ret);
    }

    public function getItinAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $arrival = $this->getRequest()->getParam('arrival');
        $leaving = $this->getRequest()->getParam('leaving');

        $budget = $this->getRequest()->getParam('budget');
        $tags = $this->getRequest()->getParam('tags'); /* IDs of categories separated by , */
        
    }
}