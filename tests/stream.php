<?php
function getMime($path)
{
   $mime = '';
   if (function_exists("mime_content_type"))
   {
      return mime_content_type($filename);
   }
   else if (function_exists("finfo_open"))
   {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $filename);
      $finfo_close($finfo);
      return $mime;
   }
   return 'application/octet-stream';
}

function is_range_valid($start,$end,$size)
{
   
   //cf : http://tools.ietf.org/html/draft-ietf-http-range-retrieval-00
   return (is_numeric($start) && is_numeric($end) && $start < $end && $end < $size) 
      ||  (is_numeric($start) && null === $end)
      ||  (null === $start && is_numeric($end) && $end < $size);
}

function multipart_header($filetype,$boundary,$range)
{
   $buf = "\r\n".'--'.$boundary."\r\n";
   $buf .= 'Content-Type: '.$filetype."\r\n"; 
   $buf .= 'Content-Range: bytes '.$range['start'].'-'.$range['end'].'/'.$range['total']."\r\n\r\n";

   return $buf;
}

$file = "08 - Ce Matin La.mp3";
$path = "/var/www/ify/tests/zik/Air/Moon Safari/";

//echo $file.$path;

if ( file_exists($path.$file))
{
   $filename = $path.$file;
   $filetype = 'application/zip';
   $filesize = filesize($filename);
   $content_length = 0;
   if ( file_exists($filename))
   {
      if(ini_get('zlib.output_compression'))
         ini_set('zlib.output_compression', 'Off');


      header('Content-Disposition: attachment; filename='.basename($filename));
      header('Content-Transfer-Encoding: binary');
      header('Cache-control: private, no-cache, no-store, must-revalidate');

      header('Pragma: no-cache');
      header('Expires: 0');

      set_time_limit(0);

      $ranges = array();

      if (isset($_SERVER['HTTP_RANGE']))
      {
         //Warning: Only support Unconditional Range Retrieval

         //Send Partial header
         header('HTTP/1.1 206 Partial Content');

         $range_values = preg_match('/^bytes=((\d*-\d*,? ?)+)$/', @$_SERVER['HTTP_RANGE'], $matches) ? explode(',',$matches[1]) : array();

         $is_multipart = false;      

         if ( 1 < count($range_values) )
         {
            //Case of multiples ranges
            $is_multipart = true;
            $boundary = md5(date('r', time()));
            foreach( $range_values as $range)
            {
               $el = array();
               list($start, $end) = explode('-', $range);
               $start = is_numeric($start) ? intval($start) : null;
               $end = is_numeric($end) ? intval($end) : null;

               $el['valid'] = is_range_valid($start,$end,$filesize)?true:false;

               if ($el['valid'])
               {
                  if (null === $start) 
                  {
                     $start = $filesize - $end;
                     $end = $filesize-1;
                  }
                  if (null === $end)   
                  {
                     $end = $filesize-1;
                  }

                  $el['start'] = $start;
                  $el['end'] = $end;
                  $el['header'] = multipart_header($filetype, 
                     $boundary, 
                     array('start' => $start,
                     'end' => $end,
                     'total' => $filesize));
                  $content_length += strlen($el['header']);
                  $content_length += $end-$start+1;
                  $ranges[] = $el;
               } 
            }
            $content_length += strlen("\r\n".'--'.$boundary.'--'."\r\n");
            header('Content-Length: '.$content_length);
            header('Content-Type: is_multipart/byteranges; boundary='.$boundary); 

         }
         else
         {  //Case of a single range

            list($start, $end) = explode("-", $ranges[0]);

            if (is_range_valid($start,$end,$size))
            {
               if (null === $start) $start = $size - $end;
               if (null === $end) $end = $size-1;
            }
            else
            {
               $start = 0;
               $end = $filesize-1;
            }

            $range['start'] = $start;
            $range['end'] = $end;
            $content_length = $end-$start+1;
            header('Content-Length: ' . $content_length );
         }

         $file = fopen($filename, 'r'); 
         foreach ($ranges as $range)
         {

            if ($range['valid'])
            {

               if (true===$is_multipart)
               {
                  print($range['header']);
               }

               if ($file)
               {
                  fseek($file, $range['start']);
                  $buffer = fread($file, $range['end'] - $range['start']+1);
                  print($buffer);
                  flush();
               }         
            }
         }

         fclose($file);

         if (true===$is_multipart)
         {
            print("\r\n".'--'.$boundary.'--'."\r\n");
         }

      }
      else 
      {
         //No range download;
         header('Content-Length: '.$filesize);
         header('Content-Type: '.$filetype);
         $file = fopen($filename, 'r'); 
         if ($file)
         {
            fseek($file, 0);
            $buffer = fread($file, floor($filesize/2));
            print($buffer);
            flush();
            sleep(10);
            fseek($file, floor($filesize/2)+1);
            $buffer = fread($file,floor($filesize/2));
            print($buffer);
            flush();
            fclose($file);
         }
      }

      exit;

   }
}
//else // file doesn't exist
//{
//   //header('Location: oups.php');
//   exit;
//}
