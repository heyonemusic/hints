<?php

if (!empty($_POST['text']) && is_string($_POST['text'])) {
    header('content-type: application/json');
    $url = 'http://ahunter.ru/site/suggest/address?addresslim=3;output=json|pretty;query=' . urlencode($_POST['text']);
    $ch = curl_init($url);

    try {

        $strlen = mb_strlen($_POST['text']);

        // Проверка на количество полученных символов
        if ($strlen < 3 || $strlen > 100) {
            throw new \RuntimeException('Кол-во символов должно быть не менее 3-ёх');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        $response = curl_exec($ch);

        // Если запрос завершится ошибкой, то выведем текст ошибки
        if (!is_string($response)) {
            $msg = curl_errno($ch) !== 0
                ? curl_error($ch)
                : 'Неизвестная ошибка';

            throw new \RuntimeException('Ошибка обращения к API AHunter: ' . $msg);
        }

        // Проверка массива JSON
        try {
            $data = json_decode($response, true, 512, \JSON_THROW_ON_ERROR);

            // Если результат не массив, то выведем ошибку
            if (!is_array($data)) {
                throw new \RuntimeException('JSON не является массивом');
            } elseif (!isset($data['suggestions'])) {
                throw new \RuntimeException('в JSON отсутствует массив suggestions');
            }

            echo json_encode(['status' => true, 'data' => $data['suggestions']]);
            //  если JSON содержит синтаксические ошибки или вовсе не является JSON, то выведем сообщение об ошибке
        } catch (\JsonException | \RuntimeException $e) {
            $msg = $e instanceof \JsonException
                ? 'не удалось распарсить JSON'
                : $e->getMessage();

            throw new \RuntimeException('Ошибка обращения к API AHunter: ' . $msg);
        }
    } catch (\RuntimeException $e) {
        echo json_encode(['status' => false, 'error' => $e->getMessage()]);
    } finally {
        curl_close($ch);
    }

    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="main.css" rel="stylesheet">
    <title>Подсказки</title>
</head>
<body>
    <form method="post">
        <input size="70" id="input" value="" type="text" placeholder="Введите город:" />
        <a href="#" class="button">Очистить</a>
    </form>
    <div class="city1"></div>
    <div class="city2"></div>
    <div class="city3"></div>
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</body>
</html>