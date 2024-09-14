<?php
  $appid = "system";
  $secret_key = "p5UPbL5x";
  $base_link = "https://v16077.vr.mirapolis.ru/mira/service/v2";

  $measures_link = "$base_link/measures";
  $measures_link_with_params = "$measures_link?appid=$appid&secretkey=$secret_key";

  $sign=strtoupper(md5("$measures_link_with_params"));

  $url="$measures_link?appid=$appid&sign=$sign";
  echo $url;
  $get = file_get_contents("$url");
  echo $get;
?>
