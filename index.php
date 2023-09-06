<?php
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
// Подключаем автозагрузчик Composer
require(__DIR__ . '/vendor/autoload.php');

// Подключаем файл с настройками Yii2 приложения
$config = require(__DIR__ . '/config/web.php');

// Проверяем, что приложение успешно создано
if (class_exists('Yii')) {
    echo "Класс Yii определен.";
    // Класс Yii определен
    // Ваш код для работы с Yii
} else {
    // Класс Yii не определен
    echo "Класс Yii не определен.";
    // Возможно, у вас есть проблема с настройкой Yii или автозагрузкой классов.
}
// Создаем и запускаем экземпляр Yii2 приложения
$application = new yii\web\Application($config);
$application->run();
//(new yii\web\Application($config))->run();