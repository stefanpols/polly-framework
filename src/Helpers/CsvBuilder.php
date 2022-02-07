<?php

namespace Polly\Helpers;

class CsvBuilder
{
    private $header = null;
    private $rules  = array();
    
    
    public function addRule($rule)
    {
        $this->rules[] = $rule;
    }
    
    public function isEmpty()
    {
        return empty($this->rules);
    }
    
    public function setHeader($header)
    {
        $this->header = $header;
    }
    
    public function addHeader($header)
    {
        if($this->header == null)
        {
            $this->header = array();
        }
        $this->header[] = $header;
    }
    
    public function setHeaders($headers)
    {
        $this->header = $headers;
    }
    
    public function addRules($rules)
    {
        $this->rules = array_merge($this->rules, $rules);
    }

    public function stream($fileName)
    {
        $fileName = $fileName . ".csv";
        ob_end_clean();
        
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename={$fileName}");
        header("Expires: 0");
        header("Pragma: public");
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        $fh = @fopen('php://output', 'w');
        
        if(!empty($this->header))
        {
            fputcsv($fh, $this->header, ";");
        }

        foreach($this->rules as &$data)
        {
			foreach($data as &$col)
				mb_convert_encoding($col, 'UTF-16LE', 'UTF-8');
				
			fputcsv($fh, $data, ";");
        }
        fclose($fh);
        exit();
    }
    
    public function save($filePath)
    {
        $fp = fopen($filePath, 'w');
        if(!empty($this->header))
        {
            fputcsv($fp, $this->header, ";");
        }
        
        foreach($this->rules as $data)
        {
			foreach($data as &$col)
				mb_convert_encoding($col, 'UTF-16LE', 'UTF-8');
			
            fputcsv($fp, $data, ";");
        }   
        fclose($fp);
    }
}
