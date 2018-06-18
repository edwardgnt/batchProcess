<?php
namespace BatchProces\Format;

abstract class AbstractCard
{
        protected $basePath = '/assets/batch_files';
        protected $fileName;
        protected $filePrefix;
        protected $tmpDir;
        protected $tmpFileHandle;
        protected $tmpFileName;

        public function __construct()
        {
            $this->tmpFileName = tempnam($this->tmpDir, 'Batch_tmp_');
            $this->tmpFileHandle = fopen($this->tmpFileName, 'wb') or die('Could not open temp file');
        }

        abstract public function addCard($batchRow);
        abstract public function addCards(array $cards);
        abstract public function format($row);
        abstract protected function setFileName();
        abstract protected function setFilePrefix();
        
        /**
         * Cleans up the file handle
         */
        public function __destruct()
        {
            if(isset($this->tmpFileHandle) && is_resource($this->tmpFileHandle)) {
                fclose($this->tmpFileHandle);
            }
        }

        /**
         * Create the file
         *
         * @return void
         */
        public function createFile()
        {
            fclose($this->tmpFileHandle);
            return rename($this->tmpFileName, $this->basePath . $this->fileName);
        }

        /**
         * Be sure a two letter state code is passed. Otherwise throw an exception
         *
         * @param [string] $state
         * @return void
         */
        protected function cleanState($state)
        {
            $clean = trim($state);

            if(strlen($clean) > 2) {
                throw new UnexpectedValueException('Invalid state value being used to create batch');
            }

            return strtoupper($clean);
        }

        /**
         * Get the name of the file
         *
         * @return string
         */
        public function getFileName()
        {
            return $this->fileName;
        }
}