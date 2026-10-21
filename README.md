# LLH Ticketing System

A Laravel 12-based ticketing system built on a Tailwind CSS v4 admin dashboard template, customized for hotel ticket management.

## Features

- Ticket creation, assignment, status tracking, and comments
- User management (admin)
- Dashboard metrics with period filters (daily / weekly / monthly)
- Calendar, profile, and authentication pages
- RTL layout support (English / Arabic)
- Dark mode

## Tech Stack

- Laravel 12 (PHP >= 8.2)
- Blade components
- Tailwind CSS v4
- Alpine.js v3
- Vite 7

## Getting Started

    composer install
    npm install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate
    npm run build
    php artisan serve

For development with hot reload:

    composer run dev

## License

MIT License. See LICENSE for details.