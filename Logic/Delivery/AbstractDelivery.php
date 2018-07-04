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
    protected $basePath = '/batch_files/';
    protected $deliveredPath = 'delivered/';
    protected $errorDirectory = 'error/';
    protected $localBasePath;

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
     * SFTP object for use in delivery
     *
     * @return void
     */
    abstract protected function setSFTP();

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
    abstract protected function setRemotePath($filePath);

    /**
     * Deliver batch file
     *
     * @param [string] $filePath: Full path to file to be delivered
     * @return bool: Weather or not file was delivered
     */
    public function deliverFile($filePath)
    {
        $this->setRemotePath($filePath);
        $filename = basename($filePath);

        
    }
}