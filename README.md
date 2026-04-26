# ChatApp — Deploy to Railway

## Prerequisites
- Railway account (railway.app)
- GitHub repository with this code
- SMTP credentials (recommended: Resend.com free tier — 3000 emails/month)

## Deploy Steps

1. Push this repo to GitHub

2. Go to railway.app → New Project → Deploy from GitHub Repo → select your repo

3. Add PostgreSQL:
   Railway Dashboard → your project → "+ New" → Database → Add PostgreSQL
   Railway auto-sets `DATABASE_URL` in your app service

4. Set Environment Variables in Railway (your app service → Variables tab):

   | Variable        | Value                              |
   |-----------------|------------------------------------|
   | APP_URL         | https://your-app.up.railway.app    |
   | APP_ENV         | production                         |
   | SESSION_SECRET  | (generate: `openssl rand -hex 16`) |
   | SMTP_HOST       | smtp.resend.com                    |
   | SMTP_PORT       | 587                                |
   | SMTP_USER       | resend                             |
   | SMTP_PASS       | re_xxxxxxxxxxxx                    |
   | SMTP_FROM       | noreply@yourdomain.com             |
   | SMTP_FROM_NAME  | ChatApp                            |

5. Railway will build the Dockerfile and deploy automatically.
   Database tables are created automatically on first request.

## Local Development

```bash
cp .env.example .env          # fill in your values
docker-compose up --build     # starts app on :8080 + postgres
```

## Email Testing Locally

Use Mailtrap.io (free) — create an inbox, copy SMTP credentials into `.env`.

## Project Structure

```
chat-app/
├── Dockerfile
├── docker-compose.yml
├── railway.toml
├── composer.json
├── .env.example
├── public/
│   ├── index.php          # front controller / router
│   ├── .htaccess
│   └── assets/
│       ├── css/style.css
│       └── js/chat.js
├── src/
│   ├── config.php
│   ├── db.php
│   ├── mailer.php
│   ├── auth.php
│   ├── csrf.php
│   └── handlers/
│       ├── register.php
│       ├── verify.php
│       ├── login.php
│       ├── logout.php
│       ├── chat.php
│       └── api_messages.php
├── views/
│   ├── layout.php
│   ├── register.php
│   ├── verify.php
│   ├── login.php
│   ├── chat.php
│   └── 404.php
└── migrations/
    └── 001_schema.sql
```
