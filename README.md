

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
