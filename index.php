<?php
header('Content-Type: text/html; charset=utf-8');

// установка токена бота
define('BOT_TOKEN', '6250517922:AAERH-A1Bz9hyTOT2aiNhtIbccrjGkx3AE0');
// установка URL API Телеграмм
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

// установка параметров подключения к базе данных
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'r8246_hto-to');
define('DB_PASSWORD', 'XTy9nUe@E;!m');
define('DB_NAME', 'r8246_telegram_photos');

// подключение к базе данных
$conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
mysqli_set_charset($conn, "utf8mb4");

// получение данных, отправленных пользователем
$update = json_decode(file_get_contents('php://input'), true);
// извлечение ID чата и текста сообщения
$message_text = $update['message']['text'];
$chat_id = $update['message']['chat']['id'];
$user_name = $update['message']['from']['username'];

// получаем данные о фотографии
$file_id = $update["message"]["photo"][0]["file_id"];
$file_info = getFile($file_id);
$file_path = $file_info["file_path"];

// скачиваем фотографию
$file_url = "https://api.telegram.org/file/bot" . BOT_TOKEN . "/" . $file_path;
$file = file_get_contents($file_url);

// проверка ответа пользователя
if ($message_text != "/start") {
    function generateUniqueFileName($extension) {
      $microtime = str_replace('.', '', microtime(true));
      return $microtime . '.' . $extension;
    }

    // сохраняем фотографию на сервере с уникальным именем
    $photo_path = "/home/r8246/public_html/reto-to.online/photos/";//Путь к файлу стоит изменить 
    $extension = pathinfo($file_path, PATHINFO_EXTENSION);
    $file_name = generateUniqueFileName($extension);
    file_put_contents($photo_path . $file_name, $file);
    
    // сохранение ответа в базе данных с путем к файлу
    $sql = "INSERT INTO photos (path, chat_id, user_name) VALUES ('{$photo_path}{$file_name}', '$chat_id', '$user_name')";
    mysqli_query($conn, $sql);

    // отправляем сообщение пользователю
    sendMessage($chat_id, "Фотография успешно сохранена на сервере.");
} else {
    // отправка вопроса пользователю
    sendMessage($chat_id, "Please send us your photo!");
}

// функция отправки сообщения пользователю
function sendMessage($chat_id, $message) {
    $url = API_URL . "sendMessage?chat_id=" . $chat_id . "&text=" . urlencode($message);
    file_get_contents($url);
}

// функция получения информации о файле
function getFile($file_id) {
    $url = API_URL . "getFile?file_id=" . $file_id;
    $result = json_decode(file_get_contents($url), true);
    return $result['result'];
}
?>
