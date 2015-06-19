<?php

namespace Flagship\Components\Helpers\Database\Migrations;

class MigrationCreator
{
    protected $file;
    protected $class;
    protected $options;

    /**
     * Create a new migration creator instance.
     *
     * @param string $name
     */
    public function __construct($name, $options)
    {
        $arr = explode('_', $name);
        $arr = array_map(function ($item) {
            return ucfirst($item);
        }, $arr);

        $this->class = implode('', $arr);
        $this->db = $options['db'];

        $this->file = date('Y_m_d_His_').$name;
        $this->options = $options;
    }

    /**
     * Create a new migration at the given path.
     *
     * @return string
     */
    public function create()
    {
        $fullPath = $this->options['path'].'/'.$this->file.'.php';

        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file.
        $stub = $this->getStub();

        file_put_contents($fullPath, $this->populateStub($stub));

        return $fullPath;
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function getStubPath()
    {
        return __DIR__.'/migration.stub';
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param string $stub
     *
     * @return string
     */
    protected function populateStub($stub)
    {
        $stub = str_replace('{{class}}', $this->class, $stub);
        $stub = str_replace('{{db}}', $this->db, $stub);

        return $stub;
    }

    /**
     * Get the migration stub file.
     *
     * @return string
     */
    protected function getStub()
    {
        return file_get_contents($this->getStubPath());
    }
}
