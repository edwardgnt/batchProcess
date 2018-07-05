<?php

namespace BatchProcess\Logic\Delivery;

class IssuerA extends AbstractDelivery
{
    /**
     * Set the local base path to the directory that contains the files to deliver to IssuerA
     *
     * @return void
     */
    protected function setLocalBase() 
    {
        $this->localBasePath = "IssuerA/card_batches/";
    }

    /**
     * Sets the remote path we want to deliver file to on the Issuer's server
     *
     * @param [type] $filePath
     * @return void
     */
    protected function setRemotePath($filePath) 
    {
        $this->remotePath = 'Incoming/';
    }

    /**
     * SFTP object to be used for file delivery
     *
     * @return void
     */
    public function setSFTP()
    {
        // Moq SFTP object and libaries to deliver files
        $this->Sftp = new \Sftp\IssuerA();
    }

    public function deliverFiles()
    {
        $files = $this->getFilesToDeliver;
    }
}