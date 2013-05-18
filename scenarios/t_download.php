<?php

/*
 *  If you want to download a file in the server give the filepath and fileName as in the example2
	$fileDown = new PHPFileDownload('myFileName','mypath/myfile.txt');
	$fileDown->exportData();
 *
 */




class PHPFileDownload {

	var $fileName;
	var $fileType;
	var $filePath;

	function __construct($fileName = null,$filePath = null) {

		if($fileName == null) {
			$this->setFileName(date("Y-m-d_H:i:s")); // default file name
		}else {
			$this->setFileName($fileName);
		}

		if($filePath != null) {
			$this->setFilePath($filePath); 
			$this->setFileType("file");

		}
	}
	
	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}

	public function getFileName() {

		$fileType = $this->getFileType();
		$this->fileName = basename($this->getFilePath());
		return $this->fileName;
	}

	public function setFileType($fileType) {
		$this->fileType = filetype($this->getFilePath());
	}

	public function getFileType() {
		return $this->fileType;
	}

	public function setFilePath($path) {
		$this->filePath = $path;
	}

	public function getFilePath() {
		return $this->filePath;
	}

	public function exportData(){
			$this->executeHeaders();
			set_time_limit(0);
			readfile($this->getFilePath());
	}

	public function executeHeaders() {
	
	var is_attachment;
	is_attachment = true;

	header("Pragma: public");
	header("Expires: -1");
	header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
	if ($is_attachment) {
		// Attachement
		header("Content-Disposition: attachment; filename=\"$file_name\"");
	} else {
		// Streamed file
		header('Content-Disposition: inline;  filename=\"$file_name\"');
	}
		header("Content-Type: " . $this->getFileType());
	header("Accept-Ranges: bytes");
	

// Error management
//	header("HTTP/1.0 500 Internal Server Error");
//	header("HTTP/1.0 404 Not Found");


// Old config
//	header("Pragma: no-cache");
//	header("Expires: 0");
//	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//	header("Cache-Control: private", false);
//	header("Content-Type: " . $this->getFileType());
//	header("Content-type: application/octet-stream");
//	header('Content-Disposition: attachment; filename="' . $this->getFileName() . '";');
//	header("Content-Transfer-Encoding: binary");
	}


}
