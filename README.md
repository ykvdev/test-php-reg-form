# PHP Signup/in Form Test Task

## Install

1. `git clone https://bitbucket.org/atoumus/php_test_reg_form.git && cd ./php_test_reg_form`
1. `composer install`
1. `cp ./reg_form.clear.sqlite3 ./data/reg_form.sqlite3`
1. Run as dev env: `php -S 0.0.0.0:8000 -t public ./public/index.php` - errors will showing
1. Or run as prod env: `APP_ENV=prod php -S 0.0.0.0:8000 -t public ./public/index.php` - errors will not showing
1. Go to: http://{your-ip-address}:8000 in browser.

## Task description

Создать без использования фреймворков:

- регистрация
- активация
- авторизация
- восстановление пароля
- отображение информации в ЛК
- редактирование данных в ЛК
- изменение пароля в ЛК