<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\Request;
use app\models\User;

class UserController extends Controller
{
    /*public function behaviors()
    {
        return [
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'actions' => ['get-users'],
                        'allow' => true,
                        'roles' => ['admin'], // Примерная роль для доступа
                    ],
                    // Другие правила...
                ],
            ],
        ];
    }*/

    public function actionLogin()
    {
        $request = Yii::$app->getRequest()->getRawBody();
        $postData = json_decode($request, true);

        $username = $postData['username'];
        $user = User::findOne(['username' => $username]);
        if (!$user) {
            return ['success' => false, 'message' => 'Пользователь не найден'];
        }
        $password = $postData['password'];
        // Сравниваем пароль пользователя с переданным паролем
        if (Yii::$app->security->validatePassword($password, $user->password)) {
            $token = Yii::$app->security->generateRandomString(64);
            $user->api_token = $token;
            $user->save();
            return ['success' => true, 'api_token' => $token, 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Неверный пароль'];
        }
    }

    public function actionSignup()
    {
        $request = Yii::$app->getRequest()->getRawBody();
        $postData = json_decode($request, true);

        $username = $postData['username'];
        if (User::findOne(['username' => $username])) {
            return ['success' => false, 'message' => 'Пользователь уже существует'];
        }
        $password = $postData['password'];

        $user = new User();
        $user->username = $username;
        $user->password = Yii::$app->security->generatePasswordHash($password);
        // Генерация $secretKey
        $secretKey = Yii::$app->security->generateRandomString(64);
        $user->api_token = $secretKey;
        if ($user->save()) {
            // Создайте полезную нагрузку для JWT токена
            $payload = [
                'sub' => $user->id,
                'exp' => time() + 3600, // Время истечения токена (1 час)
            ];
            $api_token = Yii::$app->getSecurity()->hashData(json_encode($payload), $secretKey);
            return ['success' => true, 'message' => 'Регистрация прошла успешно!', 'api_token' => $api_token, 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Ошибка при создании пользователя'];
        }
    }

    public function actionAuth()
    {
        $token = Yii::$app->getRequest()->getHeaders()->get('Authorization');
        if (!$token) {
            return ['success' => false, 'message' => 'Отсутствует заголовок Authorization с токеном'];
        }

        $token = str_replace('Bearer ', '', $token);

        // Проверка подписи токена с использованием секретного ключа
        $secretKey = 'ваш_секретный_ключ_здесь'; // Получите секретный ключ из вашей базы данных или конфигурационного файла

        try {
            $payload = JWT::decode($token, $secretKey, ['HS256']);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Неверный токен. Пользователь не аутентифицирован'];
        }

        // В этой точке, если исключения не было, токен верный и вы можете продолжить работу с пользователем
        // $payload содержит информацию о пользователе

        return ['success' => true, 'user' => $payload];
    }
}