# Laravel Car Booking Calendar

Цей проєкт реалізує відображення завантаженості автопарку по днях за обраний місяць. Підраховується кількість **вільних**, **зайнятих** та **сервісних** днів для кожного авто. Також реалізовано інтерактивний календар бронювань.

---

## Основна логіка

Головний контролер: `App\Http\Controllers\CarController`

### Метод: `index(Request $request)`

#### Призначення

Повертає статистичну зведену таблицю по кожному авто за місяць.

#### Алгоритм

1. Отримує місяць і рік з запиту (`$month`, `$year`).
2. Вираховує:
    - Початок місяця (`$from`)
    - Кінець місяця (`$to`)
    - Кількість днів у місяці (`$totalDays`)
3. Виконує SQL-запит:
    - Витягує авто разом із брендом та кольором.
    - Використовує підзапит `b` для підрахунку днів:
        - `service` — якщо `other` містить `service`
        - `busy` — якщо авто зайняте 9+ годин між 9:00 і 21:00
    - Підраховує:
        - `busy` дні
        - `service` дні
        - `free = totalDays - busy - service`
4. Повертає результат у view `index.blade.php`.

---

### Метод: `calendar(Request $request)`

#### Призначення

Готує масив бронювань для відображення у календарі (наприклад, через DayPilot.js).

#### Алгоритм

1. Отримує місяць і рік з запиту.
2. Отримує список авто:
    - `rc_cars` → `rc_cars_models` → `rc_cars_brands`
    - Фільтрація за company_id, status, is_deleted
3. Отримує бронювання з `rc_bookings`, які:
    - Починаються або закінчуються в межах місяця
    - Або повністю охоплюють місяць
4. Формує масив `groupedBookings`:
    - Групує за `car_id`
    - Кожен елемент має:
        ```php
        [
          'start' => дата початку,
          'end' => дата завершення,
          'type' => rent або service
        ]
        ```
5. Повертає у view `calendar.blade.php`.

---
