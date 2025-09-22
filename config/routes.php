<?php


/**
 * Used to define the routes in the system.
 *
 * A route should be defined with a key matching the URL and an
 * controller#action-to-call method. E.g.:
 *
 * '/' => 'index#index',
 * '/calendar' => 'calendar#index'
 */
$routes = array(
    '/' => 'task#index',
    '/index' => 'task#index',
    '/test' => 'test#index',
    '/task' => 'task#index',
    '/task/add' => 'task#add',
    '/task/create' => 'task#create',
    '/task/edit/:id' => 'task#edit',
    '/task/update/:id' => 'task#update',
    '/task/delete/:id' => 'task#delete',
    '/task/show/:id' => 'task#show',
);
