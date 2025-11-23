<?php

function fnt_buildMultipartData_v001(array $postprocess_payload): array
{
  $boundary = uniqid();
  $delimiter = '-------------' . $boundary;
  $data = '';
  foreach ($postprocess_payload as $key => $value) {
    $data .= "--" . $delimiter . "\r\n";
    $data .= 'Content-Disposition: form-data; name="' . $key . '"' . "\r\n\r\n";
    $data .= $value . "\r\n";
  }
  $data .= "--" . $delimiter . "--\r\n";
  return [
    'body' => $data,
    'content-type' => 'multipart/form-data; boundary=' . $delimiter,
  ];
}
