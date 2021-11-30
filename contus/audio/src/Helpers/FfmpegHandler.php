<?php

namespace Contus\Audio\Helpers;

class FfmpegHandler
{
    private $filePath;
    private $folderPath;
    public $destinationFolder;
    private $hlsQualities = ['426x240', '640x360', '842x480', '1280x720', '1920x1080'];
    private $oldName;
    private $newName;
    private $uuid;

    public function __construct($oldName, $uuid)
    {
        $this->oldName = $oldName;
        $this->uuid = $uuid;
        $this->filePath = base_path('public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $this->uuid . DIRECTORY_SEPARATOR . $this->oldName);
        $this->folderPath = base_path('public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $this->uuid);
        $this->checkAndCreateDir(base_path('public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'convert'));
    }

    public function clearFolders($destinationFolder = null, $folderPath = null)
    {
        if ($destinationFolder) {
            $this->destinationFolder = $destinationFolder;
        }
        if ($folderPath) {
            $this->folderPath = $folderPath;
        }
        if (is_dir($this->destinationFolder)) {
            shell_exec("rm -rf " . $this->destinationFolder);
        }
        if (is_dir($this->folderPath)) {
            shell_exec("rm -rf " . $this->folderPath);
        }
    }

    public function generateNewName()
    {
        $pathinfo = pathinfo($this->oldName);
        $extension = $pathinfo['extension'];
        $this->newName = $pathinfo['filename'];
        $current_time = \Carbon\Carbon::now()->timestamp;
        $this->newName = str_replace(' ', '_', $this->newName);
        $this->newName = preg_replace('/[^A-Za-z0-9\-]/', '', $this->newName) . $current_time . '.' . $extension;
        return $this->newName;
    }

    public function prepareTranscode()
    {
        rename($this->filePath, $this->folderPath . DIRECTORY_SEPARATOR . $this->newName);
        $this->filePath = base_path('public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $this->uuid . DIRECTORY_SEPARATOR . $this->newName);
        $this->destinationFolder = base_path('public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'convert' . DIRECTORY_SEPARATOR . $this->uuid . DIRECTORY_SEPARATOR);
        $this->checkAndCreateDir($this->destinationFolder);
    }

    public function transcode()
    {
        $this->validateFilesFolders();
        $comment = 'bash '. base_path('bash'.DIRECTORY_SEPARATOR.'vod.sh') .' ' . $this->filePath . ' '. implode($this->hlsQualities,',') .' '.$this->destinationFolder ;
        $output = shell_exec($comment . "  2>&1; echo $?");
        $output = explode(PHP_EOL, $output);
        return $output[count($output) - 2] === '0';
    }

    private function validateFilesFolders()
    {
        if (count($this->hlsQualities) === 0) {
            abort(500, 'ffmpeg quality list not found');
        }
        if (!is_dir($this->destinationFolder)) {
            abort(500, 'Destination folder not found');
        }
        if (!file_exists($this->filePath)) {
            abort(500, 'File not found');
        }
    }

    private function checkAndCreateDir($folder)
    {
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
            chmod($folder, 0777);
        }
    }

}
