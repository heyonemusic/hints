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
    <title>Подсказки</title>
</head>
<style>
    input {
        padding: 10px;
    }

    .city1 {
        display: none;
        margin: 5px;
        padding: 10px;
        width: 465px;
    }

    .city2 {
        display: none;
        margin: 5px;
        padding: 10px;
        width: 465px;
    }

    .city3 {
        display: none;
        margin: 5px;
        padding: 10px;
        width: 465px;
    }

    .city1:hover {
        cursor: pointer;
        background-color: #d9d9d9;
    }

    .city2:hover {
        cursor: pointer;
        background-color: #d9d9d9;
    }

    .city3:hover {
        cursor: pointer;
        background-color: #d9d9d9;
    }

    .button {
        text-decoration: none;
        padding: 10px;
        border: 1px solid black;
        color: #000;
        transition: 0.7s;
    }

    .button:hover {
        background-color: #d2d2d2;
        transition: 0.7s;
    }
</style>

<body>
    <form method="post">
        <input size="70" id="input" value="" type="text" placeholder="Введите город:" />
        <a href="#" class="button">Очистить</a>
    </form>
    <div class="city1"></div>
    <div class="city2"></div>
    <div class="city3"></div>
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script>
        // Таймаут для запросов на сервер
        function debounce(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this,
                    args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }

        window.addEventListener('DOMContentLoaded', function() {
            var input = document.querySelector('input#input');
            input.addEventListener('input', debounce(function(e) {
                // Избавляемся от пустых запросов к API, а также делаем показ только при вводе от 3-ёх символов
                if (e.target.value === null || e.target.value.length < 3 || e.target.value.length > 100) {
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: '',
                    dataType: 'JSON', // говорим Jquery что ждём JSON
                    data: {
                        text: e.target.value
                    },
                    beforeSend: function() {
                        e.target.disabled = true; // Выключаем поле на время запроса, чтобы не кидались новые event(input)
                    },
                    complete: function() {
                        e.target.disabled = false; // Включаем обратно
                    },
                    success: function(r) {
                        if (r.status) {
                            // Присвоение городов из полученных результатов
                            var id1 = r.data[0].value;
                            var id2 = r.data[1].value;
                            var id3 = r.data[2].value;
                            // Запись городов в блоки
                            $(".city1").text(id1);
                            $(".city2").text(id2);
                            $(".city3").text(id3);
                            // Показ скрытых блоков с городами
                            $(".city1").show();
                            $(".city2").show();
                            $(".city3").show();
                            // Замена значения инпута при клике на результат выпадающего списка
                            $(".city1").click(function() {
                                $("#input").val(id1);
                                // Скрытие городов при выборе результата
                                $(".city1").hide();
                                $(".city2").hide();
                                $(".city3").hide();
                            });
                            $(".city2").click(function() {
                                $("#input").val(id2);
                                $(".city1").hide();
                                $(".city2").hide();
                                $(".city3").hide();
                            });
                            $(".city3").click(function() {
                                $("#input").val(id3);
                                $(".city1").hide();
                                $(".city2").hide();
                                $(".city3").hide();
                            });
                        } else {
                            alert(r.error);
                        }
                    }
                });
            }, 1000));
        });
        $(".button").click(function() {
            $("#input").val("");
            $(".city1").hide();
            $(".city2").hide();
            $(".city3").hide();
        });
    </script>
</body>

</html>