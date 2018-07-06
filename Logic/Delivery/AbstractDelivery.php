<?php
namespace BatchProcess\Logic\Delivery;

abstract class AbstractDelivery
{
    /**
     * SFTP object sets up when extended
     *
     * @var [type]
     */
    protected $Sftp;
    protected $basePath = 'batch_files/';
    protected $deliveredPath = 'delivered/';
    protected $errorDirectory = 'error/';
    protected $localBasePath;

    /**
     * Sets the local path. Ex: IssuerA/batch_files/
     *
     * @return void
     */
    abstract protected function setLocalBase();

    /**
     * Set remote path for delivery
     *
     * @param [string] $filePath
     * @return void
     */
    abstract protected function setRemotePath();

    /**
     * SFTP object for use in delivery
     *
     * @return void
     */
    abstract public function setSFTP();

    /**
     * Directory containing the files to upload
     *
     * @var string
     */
    protected $newDirectory = 'new/';

    /**
     * Destination of files to upload
     *
     * @var [string]
     */
    protected $remotePath;


    public function __construct()
    {
        $this->setSFTP();
        $this->setLocalBase();
    }

    /**
     * Deliver batch file
     *
     * @param [string] $filePath: Full path to file to be delivered
     * @return bool: Weather or not file was delivered
     */
    public function deliverFile($filePath)
    {
        $this->setRemotePath($filePath);
        $fileName = basename($filePath);
    }

    /**
     * Return a list of all the files in the Local Base directory
     *
     * @return An array of paths of files to be delivered
     * @throws \DomainException: If localBasePath dosen't exist and can't be created
     */
    public function getFilesToDeliver()
    {
        $toScan = $this->basePath . $this->localBasePath . $this->newDirectory;
        $files = [];

        if (!is_dir($toScan)) {
            if (!mkdir($toScan, 0755, true)) {
                throw new \DomainException("Could not make directory: {$toScan}");
            }
        }

        // Get the files in the directory
        $tmpFiles = $this->getFiles($toScan);

        // Some files might be a directory. Looping through files and directories
        // If it is a directory ? Scan it : add it to the list of files to return
        foreach ($tmpFiles as $possibleDirectory) {
            $directoryScan = $toScan . $possibleDirectory;
            if ($is_dir($directoryScan)) {
                $readFiles = $this->getFiles($directoryScan . '/');

                foreach ($realFiles as $file) {
                    $files[] = $directoryScan . '/' . $file;
                }
            }
            else {
                $files[] = $directoryScan;
            }
        }

        return $files;
    }

    /**
     * Helper function for getFilesToDeliver
     * Filters out the . and .. and returns the list of files in teh scanned directory
     *
     * @param [string] $fullPath
     * @return array: Files in the directory
     */
    protected function getFiles($fullPath)
    {
        $scannedFiles = scandir($fullPath);
        $files = [];

        foreach ($scannedFiles as $file) {
            if (!in_array(trim($file), ['.', '..'])) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * Move the file specified by $filePath to the delivered directory
     *
     * @param [string] $filePath
     * @return bool: Success or Failure
     */
    public function moveToDeliveredDirectory($filePath)
    {
        $toPath = $this->basePath . $this->localBasePath . $this->deliveredPath;
        $fileName = basename($filePath);

        // Move File: If directory does not exist, make a directory. If fails to make directoy throw an exception
        if (!is_dir($toPath)) {
            if (!mkdir($toPath, 0755, true)) {
                throw new \DomainException("Could make directory: {$filePath} {$toPath}");
            }
        }

        // Rename the file so it is in it's new directory
        $fileResults = rename($filePath, $toPath . $fileName);

        if (!$fileResults) {
            throw new \DomainException("Could not move file: {$filePath} to {$toPath}");
        }

        return $results;
    }

    /**
     * Move the file specified by $filePath to the errors directory
     *
     * @param [string] $filePath
     * @return bool: Success or Failure
     * @throws \DomainException: If the file wasn't moved to the new directory
     */
    public function moveToUndeliveredDirectory($filePath)
    {
        $toPath = $this->basePath . $this->localBasePath . $this->errorDirectory;
        $fileName = basename($filePath);

        if (!is_dir($toPath)) {
            if (!mkdir($toPath, 0755, true)) {
                throw new \DomainException("Could not make directory: {$toPath}");
            }
        }

        // Rename the file for it's new directory
        $fileResults = rename($filePath, $toPath . $fileName);

        if (!$fileResults) {
            throw new \DomainException("Could not move file {$filePath} to {$toPath}");
        }

        return $fileResults;
    }
}