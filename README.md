:wave: Hello!

## Tooling

I've installed a number of composer packages to aid in developement: 

- barryvdh/laravel-ide-helper 
- jamesclark32/dev-audit
- larastan/larastan 
- pestphp/pest-plugin-type-coverage 
- rector/rector 
- spatie/ray

## Database seeding

The provided data file has been saved to the project at database/import.json. A console command has been written which 
ingests the data in this file, `php artisan db:import`.
