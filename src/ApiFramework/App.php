<?php

namespace ApiFramework;

/**
 * App
 *
 * Main application class
 * @version 1.0
 * @package App
*/
class App extends Core
{

    /**
     * Run the application
     * 
     * @return view
     */
    static public function run ()
    {
        // Get Url
        $url = Request::url();

        // Check if the user is logged in
        $user = new User();
        if (($url !== '/login/') && (!$user->info(Request::token()))) {
            Response::error(401, 'Invalid token');
            return Response::output();
        }

        // Get route action
        list($action, $urlParams) = Router::getAction($url);

        // Check if the class exists
        if (!class_exists($action['class'])) {
            Response::error(404, 'Not Found');
            return Response::output();
        }

        // Create the required object
        $obj = new $action['class'];

        // Apply limit
        $obj->limit(Request::limit());

        // Apply offset
        $obj->offset(Request::offset());

        // Apply order
        $obj->order(Request::order());

        // Apply where
        $obj->where(Request::input());

        // Set data
        $someData = Request::input();
        $obj->data($someData);

        // Execute the required method
        $res = call_user_func_array(array($obj, $action['method']), $urlParams?: []);

        // Check if the response was successfull
        if ($res !== false) {
            Response::data($res);
        }

        // Return the response in the right format
        return Response::output($res);
    }

}