<?php 

namespace PHPExpress\FS;

use Exception;

/**
 * File which you can write and read to
 */
class File {
    private $path;

    /**
     * File constructor
     * 
     * @param mixed $path Path to file.
     * @param bool $create If true file will be created if it does not exist
     */
    function __construct($path, bool $create = false)
    {
        $this->path = $path;

        if(file_exists($path) == false) 
        { 
            if($create == true) {
                file_put_contents($path, "");
            } else {
                throw new Exception("File $path does not exist!", 500);
            }
        }
    }
    /**
     * Writes data to file.
     * @param mixed $mixin Data that will be written to the file
     *  
     * @return void
     */
    public function WriteAll($mixin) {
        file_put_contents($this->path, $mixin);
    }
    /**
     * Reads all contents of file as string
     * 
     * @return string
     */
    public function ReadAllText(): string {
        return file_get_contents($this->path);
    }
    /**
     * Reads all contents of file as json
     * 
     * @return array
     */
    public function ReadAllJson(): array {
        $jsonString = file_get_contents($this->path);
        return json_decode($jsonString, true);
    }
}