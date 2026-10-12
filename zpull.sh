#!/bin/bash

git pull
sleep 2
composer update -W
npm install
npm audit fix
./artisan migrate
npm run build
