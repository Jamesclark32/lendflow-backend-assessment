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


## Response caching

Caching is handled via the `spatie/laravel-responsecache` package. The configuration is mostly standard. Some relevant
headers have been enabled for better visibility. Rather than enabling response caching globally, it is being applied as
middleware as part of the route definition.


## Full-text search

Full-text search has been enabled via Laravel Scout. 

For the sake of simplicity in this demo, the `database` engine has been used. Switching to a more robust solution would
likely be a good idea if using this production.


## Business rule discussions

A few things I would have asked clarifying questions about in a real-world scenario: 

- Do we have price limits? We might be able to use mediumInteger for price and sale_price. Need to understand the specific need
  and how it might evolve going forward
- How should price filtration work exactly? I'm treating the provided price as a maximum price, and include all values which
  are below or exactly match it. Does this sound right?
- Similarly, how should category filtration work when multiple categories are included? Should it return those records which
  match all specified categories or those matching one or more of them? For now it is assuming it should match all categories.

