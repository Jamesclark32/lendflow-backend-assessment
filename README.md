:wave: Hello!

## Tooling

I've installed a number of composer packages to aid in development: 

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

The caching key has been customized via `\App\Services\ResponseCache\CustomCacheProfile->useCacheNameSuffix()` to account
for API versioning numbers in the custom `x-api-version` header, ensuring each version is cached uniquely despite the 
shared URI across API versions. 


## Full-text search

Full-text search has been enabled via Laravel Scout, using Meilisearch.

The inclusion of Meilisearch does bring some complications to testing, to portability, and to the code itself. 
I debated making this move at all given the nature of this project. Ultimately, if I faced these needs on a 
live, production project I would recommend using Meilisearch or a similar product, so I went that route here.


## Business rule discussions

A few things I would have asked clarifying questions about in a real-world scenario: 

- Do we have price limits? We might be able to use mediumInteger for price and sale_price columns at the database level. 
  Need to understand the specific need and how it might evolve going forward
- How should price filtration work exactly? I'm treating the provided price as a maximum price, and include all values that
  are below or exactly match it. Does this sound right?
- Similarly, how should category filtration work when multiple categories are included? Should it return those records which
  match all specified categories or those matching one or more of them? For now it is assuming it should match all categories.
- API versioning via a custom request header has been included based on my understanding of the desired implementation. 
  This comes with a lot of complications to the codebase, which turns into a form of technical debt. Were I engaged in 
  adding the v2 in a real project, I would try to have conversations to explore working towards a URL based versioning schema
  Supporting the existing endpoints is a priority, but it might be a good idea to introduce URL based versioning 
  starting with v2, and assume the lack of version number is v1. I would want to explore if this code-based perspective
  of things makes sense and works okay from any business needs perspective. 

