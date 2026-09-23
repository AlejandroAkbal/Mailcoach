<div align="center">

<img width="500" alt="Mailcoach" src="https://github.com/spatie/Mailcoach/assets/3626559/be10e73d-e1f5-42ea-870f-38c40176939e">

# Self-Hosted Mailcoach for Coolify 4

**Turnkey, production-ready Docker Compose deployment for Spatie Mailcoach Self-Hosted with Laravel Horizon, Redis, MySQL & Amazon SES.**

[![Coolify](https://img.shields.io/badge/Coolify-v4-purple.svg?style=flat-square&logo=docker)](https://coolify.io)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4.svg?style=flat-square&logo=php)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20.svg?style=flat-square&logo=laravel)](https://laravel.com)
[![Horizon](https://img.shields.io/badge/Laravel-Horizon-4054B2.svg?style=flat-square&logo=laravel)](https://laravel.com/docs/horizon)
[![Spatie Mailcoach](https://img.shields.io/badge/Mailcoach-v10.5-00D984.svg?style=flat-square)](https://mailcoach.app/self-hosted)
[![License](https://img.shields.io/badge/License-Proprietary%20%2F%20MIT-blue.svg?style=flat-square)](#-license)

[Features](#-key-features) • [Architecture](#%EF%B8%8F-architecture) • [Coolify Quickstart](#-quickstart-for-coolify-4) • [Configuration](#%EF%B8%8F-configuration) • [Maintenance & Updates](#-maintenance--updating) • [Operational Tips](#%EF%B8%8F-operational-tips--best-practices)

</div>

---

## 📖 Overview

[Mailcoach](https://mailcoach.app/self-hosted) by Spatie is a premier self-hosted email marketing and newsletter platform. It offers list management, drip campaigns, split testing, and transactional email tracking at a fraction of the cost of SaaS alternatives by integrating directly with high-volume senders like Amazon SES, Postmark, and Mailgun.

Because Spatie distributes Mailcoach exclusively as a proprietary Composer package via their private Satis repository and **does not publish official Docker images**, self-hosting in containerized environments typically requires rolling a custom build.

This repository provides an **automated, battle-tested Docker Compose template designed specifically for [Coolify 4](https://coolify.io)**. It packages the web application, queue workers, database, and Redis cache into a zero-drift, self-healing stack.

---

## ✨ Key Features

- 🚀 **Turnkey Coolify 4 Support:** Pre-configured `docker-compose.prod.yml` that works seamlessly with Coolify's Docker Compose build pack.
- ⚡ **Full Laravel Horizon Worker:** Dedicated queue processing container with configured queues (`mailcoach-general`, `mailcoach-heavy`, `mailcoach-feedback`) and health checks.
- 🔒 **Secure Satis Authentication:** License credentials are passed securely via Docker build arguments (`COMPOSER_AUTH`), ensuring zero credential leakage into git history or public layers.
- 🔄 **Friction-Free Updates:** Streamlined dependency update workflow without messy migration collisions or manual schema diffing.
- 🛡️ **Zero Downtime & Isolated Resources:** Portable image tagging (`${COOLIFY_RESOURCE_UUID:-mailcoach}-laravel:latest`) that prevents image collisions across multi-tenant servers.
- 📧 **Enterprise Email Ready:** Built-in support for Amazon SES (with SNS webhook feedback for bounces/complaints), Postmark, Mailgun, and SendGrid.

---

## 🏛️ Architecture

```
                                  ┌───────────────────────────────┐
                                  │ Coolify / Reverse Proxy (SSL) │
                                  └───────────────┬───────────────┘
                                                  │ HTTP :80
                                                  ▼
┌─────────────────────────┐             ┌───────────────────┐             ┌─────────────────────────┐
│     Redis (Alpine)      │◄────────────┤   laravel (Web)   ├────────────►│         MySQL 8         │
│  Queues, Cache, Session │             │ Nginx + PHP-8.3   │             │   Persistent Storage    │
└────────────▲────────────┘             └───────────────────┘             └────────────▲────────────┘
             │                                                                         │
             ├─────────────────────────────────────────┐                               │
             │                                         │                               │
┌────────────┴────────────┐             ┌──────────────┴────────────┐                  │
│ laravel-horizon (Worker)│             │      Coolify Cron Task    │                  │
│  Background Jobs & SES  │             │   artisan schedule:run    ├──────────────────┘
└─────────────────────────┘             └───────────────────────────┘
```

---

## 🚀 Quickstart for Coolify 4

### Step 1: Create Resource in Coolify
1. In your Coolify dashboard, navigate to **Projects** → Select your Environment → **New Resource**.
2. Select **Public / Private Git Repository**.
3. Repository URL: `https://github.com/AlejandroAkbal/Mailcoach` (or your private fork).
4. Set the **Build Pack** to **Docker Compose**.
5. Set **Docker Compose Location** to `/docker-compose.prod.yml`.

### Step 2: Configure Build Secrets
Under your Application settings in Coolify, go to **Environment Variables** and add your build-time Composer license credentials:

* Mark as **Build-time variable** (`COMPOSER_AUTH`):
```json
{"http-basic":{"satis.spatie.be":{"username":"YOUR_SPATIE_EMAIL","password":"YOUR_MAILCOACH_LICENSE_KEY"}}}
```

### Step 3: Configure Runtime Environment Variables
Add the standard runtime secrets under **Environment Variables**:

| Variable | Recommended / Default | Description |
| :--- | :--- | :--- |
| `APP_NAME` | `Mailcoach` | Application display name |
| `APP_ENV` | `production` | Environment mode |
| `APP_KEY` | *(Generate via `php artisan key:generate`)* | 32-character AES encryption key |
| `APP_URL` | `https://mailcoach.yourdomain.com` | Public HTTPS URL for your deployment |
| `DB_PASSWORD` | *(Generate a secure password)* | MySQL root & application database password |
| `DB_DATABASE` | `mailcoach_db` | MySQL database name |
| `DB_USERNAME` | `mailcoach_user` | MySQL database username |

*(Database and Redis connection variables are automatically linked via Docker Compose service discovery).*

### Step 4: Configure the Scheduler
Mailcoach requires the Laravel scheduler to run every minute for automations, scheduled campaigns, and feedback processing.

In Coolify, go to your Application → **Scheduled Tasks** → **Add Task**:
* **Name:** `Mailcoach Scheduler`
* **Command:** `php /var/www/html/artisan schedule:run`
* **Frequency:** `* * * * *` (Every minute)
* **Container:** `laravel`

### Step 5: Deploy & Create Admin User
1. Click **Deploy** in Coolify.
2. Once healthy, open the terminal in the `laravel` container (or run via Coolify's Execute Command tool):
```bash
php artisan mailcoach:make-user
```
3. Follow the interactive prompts to create your primary administrator email and password.
4. Log in at `https://mailcoach.yourdomain.com`!

---

## 🔄 Maintenance & Updating

Spatie maintains Mailcoach through private Satis package releases (`spatie/laravel-mailcoach`), rather than frequent commits to the starter template.

To update your Mailcoach deployment to the latest upstream release:

```shell
# 1. Update composer dependencies to latest Spatie release
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php83-composer:latest \
  composer update spatie/laravel-mailcoach -W

# 2. Bump composer constraints
composer bump

# 3. Commit and push to trigger Coolify automatic deployment
git add composer.json composer.lock
git commit -m "chore: upgrade Mailcoach to latest release"
git push origin main
```

During deployment, Coolify automatically runs:
```bash
php artisan optimize
php artisan migrate --force --isolated
```
*Note: You do **not** need to manually diff or republish migrations for routine package updates. Spatie tracks applied schema migrations in the `migrations` table, and Laravel executes any newly added migrations idempotently.*

---

## 🛡️ Operational Tips & Best Practices

- **Amazon SES Rate Limits:** If sending large campaigns on new SES accounts, set reasonable throttling in Mailcoach (**Settings** → **Mailers**) to match your AWS SES sending quota.
- **Horizon Monitoring:** Horizon dashboard is accessible at `/horizon` to authenticated admin users. Check queue draining and throughput in real-time.
- **Storage for Media & Uploads:** By default, attachments and templates use the `mailcoach-storage` Docker volume. For multi-node or stateless scaling, configure an S3/R2 bucket in `.env` (`MEDIA_DISK=s3`).

---

## 📄 License

- This Docker & Coolify configuration template is open-source under the [MIT License](LICENSE).
- **Mailcoach Self-Hosted** is proprietary commercial software developed by [Spatie](https://spatie.be). You must purchase a valid license from [spatie.be/products/mailcoach](https://spatie.be/products/mailcoach) to use it.
