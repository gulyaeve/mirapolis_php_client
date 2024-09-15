<?php
function signit($url, $params)
{  // url REST-запроса и массив параметров
    $appid = 'system';  // идентификатор приложения
    $secretkey = 'p5UPbL5x';  // ключ системы
    $ret_params = $params;  // массив передаваемых параметров
    ksort($ret_params);  // сортировка параметров по названию
    $ret_params['appid'] = $appid;  // помещение в конец массива параметра appid
    $signstring = "$url?";  // формирование строки для подписи начиная с url
    foreach ($ret_params as $key => $val) {
        if (($val != '') || (gettype($val) != 'string')) {
            $signstring .= "$key=$val&";  // добавление в строку для подписи очередного параметра
        }
    }
    $signstring .= "secretkey=$secretkey";  // дополнение строки для подписи параметром secretkey
    $ret_params['sign'] = strtoupper(md5($signstring));  // формирование ключа и добавление его в
    // массив параметров
    return $ret_params;
}

function sendrequest($url, $parameters, $method, $ret_crange)
{
    // дополнение массива параметров значениями appid и sign (используется выше описанная функция signit)
    $curl_data = signit($url, $parameters);
    $ch = curl_init();  // инициализация дескриптора запроса
    curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');  // задание кодировки запроса
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // возврат результата
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  // делает возможным переход на страницу ошибки
    curl_setopt($ch, CURLOPT_HEADER, $ret_crange);  // делает возможным возвращение заголовка Content-Range
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);  // задание метода запроса
    $query = http_build_query($curl_data);  // построение строки параметров
    switch ($method) {
        case 'PUT':  // для PUT необходимо передавать длину строки параметров
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($query)));
        case 'POST':  // параметры PUT и POST передаются в теле запроса
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            break;
        case 'GET':  // для GET и DELETE параметры указываются в заголовке
        case 'DELETE':
            $url .= "?$query";
    }
    curl_setopt($ch, CURLOPT_URL, $url);  // задание url запроса
    $curl_response = curl_exec($ch);  // выполнение запроса
    $response = json_decode($curl_response, true);  // парсинг результатов
    if (!$response)
        $response = $curl_response;  // если результат не json
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // получение кода результата
    curl_close($ch);
    echo $code;
    // анализ ответа
    if ($code != 200) {
        throw new Exception('Неправильный HTTP-код: ' . $code);
    } else if (is_array($response) && isset($response['errorMessage'])) {
        throw new Exception('Возвращена ошибка: ' . $response['errorMessage']);
    } else {
        return $response;
    }
}

$member_email = 'egulyaev@academy.ru';
$measure_id = '200';
$service_url = "https://v16077.vr.mirapolis.ru/mira/service/v2/measures/$measure_id/members/regbyemail/$member_email";
$parameters = array(
    // 'sendNotifications' => 'true',
    // 'addRolesByDefault' => 'true',
    // 'enableSearchByEmail' => 'true'
);
$res = sendrequest($service_url, $parameters, 'POST', 0);
echo "Добавлен слушатель $res";

?>
