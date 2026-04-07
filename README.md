<img width="450" alt="Mailcoach" src="https://github.com/spatie/Mailcoach/assets/3626559/be10e73d-e1f5-42ea-870f-38c40176939e">

# Mailcoach Self-Hosted

Powerful email marketing, automations and transactional emails, seamlessly integrated into your Laravel application.

Mailcoach Self-Hosted lets you manage your contact lists and send marketing, automated and transactional emails from within Laravel.

- Send marketing emails with all the features you need—including segmentation, split testing, and helpful analytics.
- Automate your email marketing and create powerful workflows for onboarding or generating leads.
- Edit, send and track transactional emails directly in Mailcoach

Read our documentation on [how to get started](https://mailcoach.app/self-hosted).

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/mailcoach.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/Mailcoach)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation & updating

```shell
git rebase upstream/main

docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php83-composer:latest \
  composer install --ignore-platform-reqs
  
  ./vendor/bin/sail up --remove-orphans
  
  ./vendor/bin/sail composer outdated --direct
  
 ./vendor/bin/sail composer update  && ./vendor/bin/sail composer bump
```

To update the migrations: 

```shell
php artisan vendor:publish --tag=mailcoach-migrations

And then ask an AI: 
`Hey, I want you to compare the old 2024 mailcoach migrations to the new ones, and then I want you to delete the new ones, and implement the difference in new migrations, this way I can "update" my old migrations to the new ones. Were using Laravel Sail`
```
