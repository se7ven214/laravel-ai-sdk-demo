# Laravel AI SDK Demo

Laravel 13 demo project running with Docker, Nginx, MySQL, and the official Laravel AI SDK.

## Setup

1. Copy the environment file.

```bash
cp .env.example .env
```

2. Start and build the containers.

```bash
docker compose up -d --build
```

3. Install PHP dependencies inside the app container.

```bash
docker compose exec app composer install
```

4. Generate the application key.

```bash
docker compose exec app php artisan key:generate
```

5. Run database migrations.

```bash
docker compose exec app php artisan migrate
```

6. Configure your AI provider key in `.env`.

Examples:

```env
OPENAI_API_KEY=your_openai_key
GEMINI_API_KEY=your_gemini_key
```

The Docker database settings are already prepared in `.env.example`:

```env
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_ai_demo
DB_USERNAME=laravel
DB_PASSWORD=secret
```

## Run

- App: [http://localhost:8080](http://localhost:8080)
- AI demo page: [http://localhost:8080/ai-demo](http://localhost:8080/ai-demo)
- MySQL from host tools: `127.0.0.1:3308`

To start the project again later:

```bash
docker compose up -d
```
