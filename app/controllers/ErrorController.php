<?php

/**
 * A controller used for handling standard errors
 */
class ErrorController extends Controller
{
    protected $_exception = null;

    public function setException(Exception $exception)
    {
        $this->_exception = $exception;
    }

    public function errorAction()
    {
        header("HTTP/1.0 404 Not Found");

        $this->view->error = $this->_exception->getMessage();

        error_log($this->view->error);
        error_log($this->_exception->getTraceAsString());
    }
}
