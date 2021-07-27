<?php

namespace App\Services\Storage;

interface FileSystem
{
    /**
     * Get driver client.
     *
     * @return mixed
     */
    public function getClient();

    /**
     * Store or put file into storage.
     *
     * @param $content
     * @param null $path
     * @param array $options
     * @return mixed
     */
    public function store($content, $path = null, $options = []);

    /**
     * Get file from storage.
     *
     * @param null $path
     * @param array $options
     * @return mixed
     */
    public function get($path = null, $options = []);

    /**
     * Get url of path (depends on url)
     *
     * @param $filePath
     * @return mixed
     */
    public function url($filePath);

    /**
     * Create a directory.
     *
     * @param $path
     * @param array $options
     * @return bool
     */
    public function makeDirectory($path, $options = []);

    /**
     * Copy data inside disk.
     *
     * @param $from
     * @param $to
     * @param array $options
     * @return mixed
     */
    public function copy($from, $to, $options = []);

    /**
     * Move data inside disk.
     *
     * @param $from
     * @param $to
     * @param array $options
     * @return mixed
     */
    public function move($from, $to, $options = []);

    /**
     * Delete data inside disk.
     *
     * @param $path
     * @return mixed
     */
    public function delete($path);

}