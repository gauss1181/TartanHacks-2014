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

        $dbPlaces = new Application_Model_DbTable_Places();

        $arrival = $this->getRequest()->getParam('arrival');
        $leaving = $this->getRequest()->getParam('leaving');
        $city = $this->getRequest()->getParam('place');

        $budget = $this->getRequest()->getParam('budget');
        $tags = explode(",", $this->getRequest()->getParam('tags')); /* IDs of categories separated by , */

        $arrival_obj = new DateTime($arrival);
        $leaving_obj = new DateTime($leaving);
        $total_time_avail = (intval($arrival_obj->diff($leaving_obj)->format("%R%")) / 3600 / 24 - 1) * 24 / 2 * 60; /* Just assume half of the day will be used to explore */
        $total_time_avail = abs($total_time_avail); /* Total time available, in minutes */

        $remaining_time_avail = $total_time_avail;

        /* For each tag, check if it is a place or category. Add these two to two different variables */
        $places_to_visit = array();
        $categories = array();
        foreach ($tags as $tag) {
            $entry = $dbPlaces->fetchRow("id = '$tag'");
            if ($entry == null) {
                break;
            }

            $entry = $entry->toArray();
            if ($entry['type'] == 'place') {

                $client = new Zend_Http_Client();
                $place_name = str_replace(" ", "%20", $entry['name']);
                $locality = str_replace(" ", "%20", $city);
                $address = $entry['address'];
                $last_comma = strrpos($address, ",");
                $second_last = strrpos($address, ",", $last_comma - strlen($address) - 1);
                $street_address = str_replace(" ", "%20", substr($address, 0, $second_last));
                /* Another heuristic: strip off the state name and city name in the end to get the street address */

                $client->setUri("https://api.locu.com/v1_0/venue/search/?name=$place_name&locality=$locality&street_address=$street_address&api_key=e286f4f7cfc1589c0ba2d454429e5d00adae5201");

                $arr_response = json_decode($client->request()->getBody());
                $arr_response = $arr_response->objects;

                if (count($arr_response) == 0) {
                    /* If can't find one location, ignore it */
                    continue;
                }

                $arr_response = $arr_response[0]; /* Heuristic: take the first object in the array */
                $remaining_time_avail -= intval($entry['avg_stay_time']);
                array_push($places_to_visit, $arr_response);
            }
            else {
                array_push($categories, $entry);
            }
        }

        /* Find the rectangular region formed by these places */
        $leftmost = null;
        $rightmost = null;
        $topmost = null;
        $bottommost = null;

        foreach ($places_to_visit as $place) {
            $long = $place->long;
            $lat = $place->lat;

            if ($leftmost == null) {
                $leftmost = $long;
                $rightmost = $leftmost;
                $topmost = $lat;
                $bottommost = $topmost;
            }
            else {
                /* Not strictly correct login */
                if ($long < $leftmost) {
                    $leftmost = $long;
                }
                if ($long > $rightmost) {
                    $rightmost = $long;
                }
                if ($lat > $topmost) {
                    $topmost = $lat;
                }
                if ($lat < $bottommost) {
                    $bottommost = $lat;
                }
            }
        }


        /**
         * Do some heuristics: determine how many restaurants we need to go to, based on the given
         * arrival and leaving dates.
         * Based on the locations that "must go", find places of the categories specified which are
         * also in the same region
         * Then try to find the optimal path to connect them
         */
        
        /**
         * Loop through every group and try to use places within the rectangular region to
         * fill the remaining time
         */

        $avg_time_per_cat = round($remaining_time_avail / count($categories));
        foreach ($categories as $category) {
            $remaining_time_this_cat = $avg_time_per_cat;
            $places = $dbPlaces->getPlacesByCategory($category['id']);


            foreach ($places as $place) {
                /* If there are no more remaining time for this category, stop */
                if ($remaining_time_this_cat <= 0) {
                    break;
                }

                $entry = $dbPlaces->fetchRow("id = $place")->toArray();
                
                /* Determine whether this place is within the lat long bounds */
                $client = new Zend_Http_Client();
                $place_name = str_replace(" ", "%20", $entry['name']);
                $locality = str_replace(" ", "%20", $city);
                $address = $entry['address'];
                $last_comma = strrpos($address, ",");
                $second_last = strrpos($address, ",", $last_comma - strlen($address) - 1);
                $street_address = str_replace(" ", "%20", substr($address, 0, $second_last));
                $client->setUri("https://api.locu.com/v1_0/venue/search/?name=$place_name&locality=$locality&street_address=$street_address&api_key=e286f4f7cfc1589c0ba2d454429e5d00adae5201");

                $arr_response = json_decode($client->request()->getBody());
                $arr_response = $arr_response->objects;

                if (count($arr_response) == 0) {
                    continue;
                }

                $arr_response = $arr_response[0];
                $lat = $arr_response->lat;
                $long = $arr_response->long;

                if ($lat <= $topmost && $lat >= $bottommost &&
                    $long <= $leftmost && $long >= $rightmost) {
                    /* If within bounds, add it */
                    $arr_response->avg_stay_time = $entry['avg_stay_time'];
                    array_push($places_to_visit, $arr_response);
                    $remaining_time_this_cat -= $entry['avg_stay_time'];

                }
            }
        }
        /* TODO There may be duplicate entries */

        echo Zend_Json::encode($places_to_visit);

        $ret = array(
            array(
                'id' => 1,
                'name' => "Metropolitan Museum of Art",
                'start_time' => "01/02/2014 07:00:00",
                'end_time' => "01/02/2014 12:00:00",
                'latitude' => 40.779144,
                'longitude' => -73.96277
            ),

            array(
                'id' => 2,
                'name' => "Peking Duck House",
                'start_time' => "01/02/2014 12:30:00",
                'end_time' => "01/02/2014 13:30:00",
                'latitude' => 40.714658,
                'longitude' => -73.998733
            )
        );

        $this->view->data = Zend_Json::encode($ret);

    }
}