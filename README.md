# Salon Control (salon-soft)

Plataforma para control de stock, servicios, ventas, comisiones y reportes para salones de belleza.

## Stack
- Backend: Laravel + PostgreSQL
- Frontend: Vue 3 + Vite

## Estructura
```
salon-backend/   # API Laravel
salon-frontend/  # App Vue
```

## Requisitos
- PHP 8.2+
- Composer
- Node 18+ (recomendado)
- PostgreSQL

## Backend (Laravel)
```
cd salon-backend
cp .env.example .env
# Edita .env con tus credenciales de Postgres
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

API por defecto: `http://localhost:8000`

Usuario demo (seeder):
- email: `admin@salon.test`
- password: `secret123`

## Frontend (Vue)
```
cd salon-frontend
npm install
# Opcional: configura VITE_API_URL en .env
npm run dev
```

Frontend por defecto: `http://localhost:5173`

## Subir cambios (Git)
```
git status
git add .
git commit -m "chore: actualiza algo"
git push origin <tu-rama>
```

Si trabajas en una rama nueva:
```
git checkout -b codex/mi-cambio
git push -u origin codex/mi-cambio
```

