#!/bin/bash

composer update

npm install

npm run build

echo "prepare database"
sleep 5
echo "prepare .env"
sleep 5
echo "./artisan migrate:fresh --seed"

npm run dev
