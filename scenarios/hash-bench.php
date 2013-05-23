<?php

// Set dynmic output
flush(); @ob_flush();  ## make sure that all output is sent in real-time

// Set time limit
set_time_limit ( 0 );

$string = file_get_contents('/etc/passwd');

foreach (hash_algos() as $algo) {
  $i = 10000;
  $time = microtime(true);

  while($i--) {
    hash($algo, $string);
  }
  $result[$algo] = microtime(true) - $time;
}
asort($result);

printf ("<table border='1'>");
foreach ($result as $algo => $time) {
  printf("<tr><td>%-11s</td><td>%.4f</td><tr>", $algo, $time);
}
printf("</table>");
