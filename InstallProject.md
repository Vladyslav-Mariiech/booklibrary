# Проєкт BookLibrary

---

# Інструкція для розгортання проєкту

## 1. Клонуємо репозиторій

git clone https://github.com/Vladyslav-Mariiech/booklibrary.git
cd booklibrary

## 2. Встановлюємо PHP залежності
composer install
## 3. Налаштовуємо файл .env
Скопіюйте шаблон .env.example у .env:
#### для Linux / Mac
cp .env.example .env
#### для Windows PowerShell
copy .env.example .env

#### Налаштуйте підключення до бази даних у файлі .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booklibrary
DB_USERNAME=root
DB_PASSWORD=

## 4. Генеруємо ключ додатку
php artisan key:generate

## 5. Накочуємо міграції
php artisan migrate

## 6. Встановлюємо JavaScript залежності
npm install

## 7. Запускаємо Vite для розробки
npm run dev

## 8. Запуск локального серверу Laravel
php artisan serve

## 9. Створюємо символьну ссылку для storage

php artisan storage:link (для підключення зоображень обкладинок книг)
