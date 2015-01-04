<?php

/**
 * This is simple laravel application test
 */
class EloquentTestCase extends TestCase {

    protected $entityNamespace = 'Gzero\Entity';

    public function setUp()
    {
        parent::setUp();
        $this->restartEloquentEvents();
    }

    protected function restartEloquentEvents()
    {
        // Get all models in the Model directory
        $models_dir = dirname(__DIR__) . '/src/' . str_replace('\\', '/', $this->entityNamespace);
        $files      = \File::files($models_dir);

        // Exclude non *.php files
        foreach ($files as $i => $file) {
            if (!strpos($file, '.php')) {
                unset($files[$i]);
            }
        }
        // Remove the directory name and the .php from the filename
        $files = str_replace($models_dir . '/', '', $files);
        $files = str_replace('.php', '', $files);

        // Remove "Base*" and "Abstract*" classes as we don't want to boot that models
        foreach ($files as $i => $file) {
            if (str_is('Base*', $file) || str_is('Abstract*', $file)) {
                unset($files[$i]);
            }
        }

        // Reset each model event listeners
        foreach ($files as $model) {
            if (!method_exists($this->entityNamespace . '\\' . $model, 'flushEventListeners')) {
                continue;
            }

            // Flush any existing listeners
            call_user_func([$this->entityNamespace . '\\' . $model, 'flushEventListeners']);

            // Re-register them
            call_user_func([$this->entityNamespace . '\\' . $model, 'boot']);
        }
    }
}
