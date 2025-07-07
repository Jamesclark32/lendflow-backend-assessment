:wave: Hello! 

This is my output after completing the challenge as described in [The requirements document](requirements.pdf). 
I've written some high-level notes here. I'd love to hear any questions or feedback, and talk through my thinking
while working through this. Thanks for reading!


## Dependencies

This application requires an instance of Meilisearch and mysql working, and relevant configurations set in .env.
You will then need to do a one-time sync of Meilisearch settings using: `php artisan scout:sync-index-settings`


## Database seeding

A user can be registered using `php artisan app:register`. This will prompt you for a name, email, and password.
An account will be registered based on the provided values, and an API token will be generated and displayed on screen.

The data file you provided has been saved to the project at database/import.json. Running the console command `php artisan db:import` adds this data to the database.


## Interacting with the API

Once you have registered a user and obtained an API token, you can send requests to the API endpoints. 
Be sure to add an authorization header to your requests: `Authorization: Bearer <your_api_token_here>`
as well as an API version header (unless you are happy to default to v1): `X-API-Version: 1`

You should then be able to send relevant GET requests. 
The available routes are: 
`api/categories`
`api/products`

The `api/products` route supposes some query parameters:
    `search` to search against product names
    `categories[]` to filter by categories
    `price` to filter by a max price
    `on_sale` to filter based on product sale status
    `color` to filter by product color

For example, `api/products?search=linen&color=white&on_sale=true&categories[]=shirts`

## Response caching

Caching is handled via the `spatie/laravel-responsecache` package. The configuration is mostly standard. Some relevant
response headers such as cache age have been enabled for better visibility. Rather than enabling response caching globally, it is being applied as
middleware as part of the route definition.

The caching key has been customized via `\App\Services\ResponseCache\CustomCacheProfile->useCacheNameSuffix()` to handle 
the request header-based API versioning numbers in the custom `X-API-Version` header, ensuring each version is cached uniquely despite the
shared URL across API versions. 


## Full-text search

Full-text search has been enabled via Laravel Scout, using Meilisearch.

The inclusion of Meilisearch does bring some complications to testing, portability, and to the code itself. 
I debated making this move at all given the nature of this project. Ultimately, if I faced these needs on a 
live, production project I would recommend using Meilisearch or a similar product, so I went that route here.


## Business rule discussions

A few things I would have asked clarifying questions about in a real-world scenario: 

- Do we have price limits? We might be able to use mediumInteger for price and sale_price columns at the database level. 
  Need to understand the specific need and how it might evolve going forward.
- How should price filtration work exactly? I'm treating the provided price as a maximum price, and including all values that
  are below or exactly match it. Does this sound right?
- Similarly, how should category filtration work when multiple categories are included? Should it return those records which
  match all specified categories or those matching one or more of them? For now it is assuming it should match all categories.
- API versioning via a custom request header has been included based on my understanding of the desired implementation. 
  This comes with a lot of complications to the codebase, which turns into a form of technical debt. Were I engaged in 
  adding the v2 in a real project, I would try to have conversations to explore working towards a URL-based versioning schema.
  Supporting the existing endpoints is a priority, but it might be a good idea to introduce URL-based versioning 
  starting with v2, and assume URLs that don't specify the version number are v1. I would want to explore if this code-based perspective
  of things makes sense and works okay for business needs. I have structured the controllers and form request classes as 
  if each was a distinct route even though they are so similar. No, this isn't very DRY. In my experience, this is likely
  to be the cleanest and most maintainable solution going forward as new changes come in. The action classes are currently shared,
  as there was no need to differentiate between the two versions for them. Inevitable a scenario would come up where a solution
  would need to be architected, but best to defer that decision until it comes up and more information is available.
- Any number of small topics such as which columns to include in responses and slugs vs. uuids.


## Testing

I'm using pest for running tests, but prefer writing phpunit style class-based tests. This results in a mixture of 
test styles as all the boilerplate tests automatically installed are pest styled. A bit messy, perhaps, but effective.

As these are end-to-end tests, they include live Meilisearch interactions. This, unfortunately, means tests including
 `sleep()` calls to wait for Meilisearch indexing to complete. A solid strategy for streamlining test more against a 
single data import would likely be a near-term priority for me if moving forward with repository to limit this slow-down.

With tests, as with all things, I'm always happy to adapt to the conventions of a codebase to keep things consistent.


## Laravel Breeze

Breeze is considered a little old-fashioned with the newer generation of starter kits having taken the stage, but the "API only"
option felt like a perfect fit for this need and has therefore been included. This created a number of endpoints for 
account creation and management, however no consuming front end has been created.


## Tooling

I've installed a number of composer packages to aid in development:

- barryvdh/laravel-ide-helper
- jamesclark32/dev-audit
- larastan/larastan
- pestphp/pest-plugin-type-coverage
- rector/rector
- spatie/ray

dev-audit may be of interest as it's a package I wrote and maintain. The code for it has some issues and needs
to be rewritten some day, but I enjoy and rely upon its functionality in my day-to-day on many repositories. 
