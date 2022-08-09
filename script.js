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