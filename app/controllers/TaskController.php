<?php

class TaskController extends ApplicationController
{
    public function indexAction()
    {
        $filePath = ROOT_PATH . '/data/tasks.json';

        if (!file_exists($filePath)) {
            $tasks = [];
        } else {
            $json = file_get_contents($filePath);
            $tasks = json_decode($json);
        }
        $this->view->tasks = $tasks;
    }

    public function addAction()
    {

    }

    public function createAction()
    {
        $title = htmlspecialchars($this->_getParam('title'));
        $status = $this->_getParam('status', 'To do');
        $description = htmlspecialchars($this->_getParam('description'));
        $owner = htmlspecialchars($this->_getParam('created_by'));

        if (empty($title)) {
            $this->view->error = "Title is required.";
            $this->addAction();
            return;
        }

        if (!preg_match('/^[\p{L}0-9\s.,!?¿¡\'"()-]{3,50}$/u', $title)) {
            $this->view->error = "Title must be 3-50 characters. Letters, numbers, and basic punctuation allowed.";
            $this->addAction();
            return;
        }

        if (!preg_match('/^[\p{L}0-9\s.,!?¿¡\'"()-]{0,200}$/u', $description)) {
            $this->view->error = "Only letters, numbers, and basic punctuation allowed.";
            $this->addAction();
            return;
        }

        if (!preg_match('/^[\p{L}0-9\s.,!?¿¡\'"()-]{0,25}$/u', $owner)) {
            $this->view->error = "Only letters, numbers, and basic punctuation allowed.";
            $this->addAction();
            return;
        }

        $filePath = ROOT_PATH . '/data/tasks.json';
        $tasks = [];

        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $tasks = json_decode($json);
        }

        $newId = count($tasks) > 0 ? end($tasks)->id + 1 : 1;

        $newTask = new stdClass();
        $newTask->id = $newId;
        $newTask->title = $title;
        $newTask->description = $description;
        $newTask->created_by = $owner;
        $newTask->status = $status;
        $newTask->created_at = date('d-m-Y H:i:s');

        $tasks[] = $newTask;

        file_put_contents($filePath, json_encode($tasks, JSON_PRETTY_PRINT));

        header('Location: ' . $this->_baseUrl() . '/task');
        exit;
    }

    public function deleteAction()
    {
        $id = $this->_getParam('id');

        $filePath = ROOT_PATH . '/data/tasks.json';
        $tasks = [];

        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $tasks = json_decode($json);
        }

        $updatedTasks = [];

        foreach ($tasks as $task) {
            if ($task->id != $id) {
                $updatedTasks[] = $task;
            }
        }

        file_put_contents($filePath, json_encode($updatedTasks, JSON_PRETTY_PRINT));

        header('Location: ' . $this->_baseUrl() . '/task');
        exit;
    }

    public function editAction()
    {
        $id = (int) $this->_getParam('id');

        if ($id < 1) {
            $this->view->error = "Invalid task ID.";
            $this->indexAction();
            return;
        }

        $filePath = ROOT_PATH . '/data/tasks.json';
        $tasks = [];

        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $tasks = json_decode($json);
        }

        $taskToEdit = null;

        foreach ($tasks as $task) {
            if ($task->id == $id) {
                $taskToEdit = $task;
                break;
            }
        }

        if (!$taskToEdit) {
            $this->view->error = "Task not found.";
            $this->indexAction();
            return;
        }

        $this->view->task = $taskToEdit;
    }

    public function updateAction()
    {
        $id = (int) $this->_getParam('id');

        $filePath = ROOT_PATH . '/data/tasks.json';
        $tasks = [];

        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $tasks = json_decode($json);
        }

        $title = htmlspecialchars($this->_getParam('title'));
        $description = htmlspecialchars($this->_getParam('description'));
        $owner = htmlspecialchars($this->_getParam('created_by'));
        $status = $this->_getParam('status', 'To do');

        foreach ($tasks as $task) {
            if ($task->id == $id) {
                $task->title = $title;
                $task->description = $description;
                $task->created_by = $owner;
                $task->status = $status;
                break;
            }
        }

        file_put_contents($filePath, json_encode($tasks, JSON_PRETTY_PRINT));

        header('Location: ' . $this->_baseUrl() . '/task');
        exit;
    }
}
