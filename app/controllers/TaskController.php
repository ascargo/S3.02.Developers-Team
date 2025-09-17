<?php

class TaskController extends ApplicationController
{
    private function loadTasks(): array
    {
        $filePath = ROOT_PATH . '/data/tasks.json';

        if (!file_exists($filePath)) {
            error_log("loadTasks: file does not exist: $filePath");
            return [];
        }

        $json = @file_get_contents($filePath);
        if ($json === false) {
            $err = error_get_last()['message'] ?? 'unknown read error';
            error_log("loadTasks: failed to read $filePath: $err");
            return [];
        }

        $tasks = json_decode($json);
        if (!is_array($tasks)) {
            $jsonErr = json_last_error_msg();
            error_log("loadTasks: json_decode did not return array. json_last_error: $jsonErr. Raw length=" . strlen($json));
            return [];
        }

        return $tasks;
    }


    private function saveTasks(array $tasks): bool
    {
        $filePath = ROOT_PATH . '/data/tasks.json';
        $dir = dirname($filePath);

        // Ensure directory exists
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0775, true)) {
                error_log("saveTasks: failed to mkdir $dir");
                return false;
            }
        }

        // Ensure file exists (first run)
        if (!file_exists($filePath)) {
            if (@file_put_contents($filePath, "[]", LOCK_EX) === false) {
                $err = error_get_last()['message'] ?? 'unknown write error';
                error_log("saveTasks: could not create file $filePath: $err");
                return false;
            }
            @chmod($filePath, 0664);
        }

        // If file exists but is not writable, bail
        if (!is_writable($filePath)) {
            error_log("saveTasks: file not writable: $filePath");
            return false;
        }

        // Encode JSON
        // If your PHP supports it, JSON_THROW_ON_ERROR gives better diagnostics:
        // try { $json = json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); }
        // catch (Throwable $e) { error_log("saveTasks: json_encode failed: ".$e->getMessage()); return false; }
        $json = json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $jsonErr = json_last_error_msg();
            error_log("saveTasks: json_encode failed: $jsonErr");
            return false;
        }

        // Write with lock
        $bytes = @file_put_contents($filePath, $json, LOCK_EX);
        if ($bytes === false) {
            $err = error_get_last()['message'] ?? 'unknown write error';
            error_log("saveTasks: write failed at $filePath: $err");
            return false;
        }

        // Friendly perms for local dev
        @chmod($filePath, 0664);
        return true;
    }



    private function findTaskById(array $tasks, int $id)
    {
        foreach ($tasks as $t) {
            if ((int)($t->id ?? 0) === $id) {
                return $t;
            }
        }
        return null;
    }

    private function normalizeStatus(?string $s): string
    {
        // Map legacy statuses into our three canonical ones
        $s = trim((string)$s);
        $map = [
            'pending' => 'To do',
            'todo' => 'To do',
            'to do' => 'To do',
            'in progress' => 'In progress',
            'doing' => 'In progress',
            'done' => 'Done',
            'completed' => 'Done',
        ];
        $key = strtolower($s);
        return $map[$key] ?? ($s ?: 'To do');
    }

    public function indexAction()
    {
        $tasks = $this->loadTasks();

        // Normalize legacy statuses and group for the 3 columns
        $groups = ['To do' => [], 'In progress' => [], 'Done' => []];
        $map    = [
            'pending'     => 'To do',
            'todo'        => 'To do',
            'to do'       => 'To do',
            'doing'       => 'In progress',
            'in progress' => 'In progress',
            'completed'   => 'Done',
            'done'        => 'Done',
        ];

        foreach ($tasks as $t) {
            $raw  = isset($t->status) ? strtolower(trim((string)$t->status)) : 'to do';
            $norm = $map[$raw] ?? ($t->status ?? 'To do');
            $t->status = $norm;

            if (!isset($groups[$norm])) {
                $groups[$norm] = [];
            }
            $groups[$norm][] = $t;
        }

        $this->view->tasks  = $tasks;
        $this->view->groups = $groups;
    }

    public function addAction()
    {
        if (!empty($_SESSION['error'])) {
            $this->view->error = $_SESSION['error'];
            unset($_SESSION['error']);
        }
        if (!empty($_SESSION['formData'])) {
            $this->view->formData = $_SESSION['formData'];
            unset($_SESSION['formData']);
        }

        // allow ?partial=1 for modal rendering
        if ($this->_getParam('partial')) {
            $this->view->disableLayout();
        }
    }

    public function createAction()
    {
        $isAjax = ($this->_getParam('ajax') === '1');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                $this->view->renderJson(['ok' => false, 'error' => 'Invalid method']);
                return;
            }
            header('Location: ' . $this->_baseUrl() . '/task/add');
            exit;
        }

        $title       = htmlspecialchars($this->_getParam('title'));
        $status      = $this->normalizeStatus($this->_getParam('status', 'To do'));
        $description = htmlspecialchars($this->_getParam('description'));
        $owner       = htmlspecialchars($this->_getParam('created_by'));

        $formData = [
            'title'       => $title,
            'description' => $description,
            'created_by'  => $owner,
            'status'      => $status,
        ];

        $fail = function ($msg) use ($isAjax, $formData) {
            if ($isAjax) {
                $this->view->renderJson(['ok' => false, 'error' => $msg]);
                return true;
            }
            $_SESSION['error'] = $msg;
            $_SESSION['formData'] = $formData;
            header('Location: ' . $this->_baseUrl() . '/task/add');
            exit;
        };

        if (empty($title)) {
            if ($fail("Title is required.")) {
                return;
            }
        }
        if (!preg_match('/^[\p{L}0-9\s.,!?¿¡\'"()-]{3,50}$/u', $title)) {
            if ($fail("Title must be 3-50 characters. Letters, numbers, and basic punctuation allowed.")) {
                return;
            }
        }
        if (!preg_match('/^[\p{L}0-9\s.,!?¿¡\'"()-]{0,200}$/u', $description)) {
            if ($fail("Only letters, numbers, and basic punctuation allowed.")) {
                return;
            }
        }
        if (!preg_match('/^[\p{L}0-9\s.,!?¿¡\'"()-]{0,25}$/u', $owner)) {
            if ($fail("Only letters, numbers, and basic punctuation allowed.")) {
                return;
            }
        }

        $tasks = $this->loadTasks();

        $maxId = 0;
        foreach ($tasks as $t) {
            if (isset($t->id) && is_numeric($t->id)) {
                $maxId = max($maxId, (int)$t->id);
            }
        }
        $newId = $maxId + 1;

        $newTask = (object)[
            'id'          => $newId,
            'title'       => $title,
            'description' => $description,
            'created_by'  => $owner,
            'status'      => $status,
            'created_at'  => date('d-m-Y H:i:s'),
        ];

        $tasks[] = $newTask;

        if (!$this->saveTasks($tasks)) {
            $msg = "Could not write tasks file. Check permissions for: " . ROOT_PATH . "/data/tasks.json";
            error_log("createAction: $msg");
            if ($isAjax) {
                $this->view->renderJson(['ok' => false, 'error' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
            $_SESSION['formData'] = $formData;
            header('Location: ' . $this->_baseUrl() . '/task/add');
            exit;
        }

        if ($isAjax) {
            $this->view->renderJson(['ok' => true, 'task' => $newTask]);
            return;
        }

        header('Location: ' . $this->_baseUrl() . '/task');
        exit;
    }

    public function deleteAction()
    {
        $isAjax = ($this->_getParam('ajax') === '1');
        $id = (int) $this->_getParam('id');

        $tasks = $this->loadTasks();

        $updatedTasks = [];
        $found = false;
        foreach ($tasks as $task) {
            if ((int)$task->id === $id) {
                $found = true;
                continue;
            }
            $updatedTasks[] = $task;
        }

        if (!$this->saveTasks($updatedTasks)) {
            $msg = "Could not write tasks file. Check permissions for: " . ROOT_PATH . "/data/tasks.json";
            error_log("deleteAction: $msg");
            if ($isAjax) {
                $this->view->renderJson(['ok' => false, 'error' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . $this->_baseUrl() . '/task');
            exit;
        }

        if ($isAjax) {
            $this->view->renderJson(['ok' => true, 'removed' => $found]);
            return;
        }

        header('Location: ' . $this->_baseUrl() . '/task');
        exit;
    }

    public function editAction()
    {
        $id = (int) $this->_getParam('id');
        if ($id < 1) {
            $this->view->error = "Invalid task ID.";
            // Render edit view with the error so the modal shows a clear message
            return;
        }

        $tasks = $this->loadTasks();
        $task  = $this->findTaskById($tasks, $id);

        if (!$task) {
            $this->view->error = "Task not found.";
            // Render edit view with the error (don’t call indexAction here)
            return;
        }

        $this->view->task = $task;

        // allow ?partial=1 for modal rendering
        if ($this->_getParam('partial')) {
            $this->view->disableLayout();
        }
    }


    public function showAction()
    {
        $id = (int) $this->_getParam('id');
        if ($id < 1) {
            $this->view->error = "Invalid task ID.";
            $this->indexAction();
            return;
        }

        $tasks = $this->loadTasks();
        $task  = $this->findTaskById($tasks, $id);

        if (!$task) {
            $this->view->error = "Task not found.";
            $this->indexAction();
            return;
        }

        $this->view->task = $task;

        // allow ?partial=1 for modal rendering
        if ($this->_getParam('partial')) {
            $this->view->disableLayout();
        }
    }

    public function updateAction()
    {
        $isAjax = ($this->_getParam('ajax') === '1');
        $id = (int) $this->_getParam('id');

        $tasks = $this->loadTasks();

        $title = htmlspecialchars($this->_getParam('title'));
        $description = htmlspecialchars($this->_getParam('description'));
        $owner = htmlspecialchars($this->_getParam('created_by'));
        $status = $this->normalizeStatus($this->_getParam('status', 'To do'));

        $updated = false;
        foreach ($tasks as $task) {
            if ((int)$task->id === $id) {
                $task->title = $title;
                $task->description = $description;
                $task->created_by = $owner;
                $task->status = $status;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            if ($isAjax) {
                $this->view->renderJson(['ok' => false, 'error' => 'Task not found']);
                return;
            }
            $_SESSION['error'] = "Task not found.";
            header('Location: ' . $this->_baseUrl() . '/task');
            exit;
        }

        if (!$this->saveTasks($tasks)) {
            $msg = "Could not write tasks file. Check permissions for: " . ROOT_PATH . "/data/tasks.json";
            error_log("updateAction: $msg");
            if ($isAjax) {
                $this->view->renderJson(['ok' => false, 'error' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . $this->_baseUrl() . '/task');
            exit;
        }

        if ($isAjax) {
            $this->view->renderJson(['ok' => true]);
            return;
        }

        header('Location: ' . $this->_baseUrl() . '/task');
        exit;
    }
}
