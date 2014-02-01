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

        $ret = $dbPlaces->getPlacesContainingStr($string);

        echo Zend_Json::encode($ret->toArray());
    }
}