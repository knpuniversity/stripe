# Testing Webhooks

The reason that web hooks are so hard is... well, they're kind of impossible to
test. There are certain things on Stripe - like my card being declined during a
renewal, which are *really* hard to simulate. And even if we could, Stripe can't
send a webhook to my local computer. Well actually, that last part isn't true,
but more on that later.

So let's look at a coiuple of strategies for making sure that your webhooks are
*air tight*. I do *not* want to mess something up and confuse my users.

## Automated Test

The first strategy - and the one we use here on KnpU - is to create an automated
test that sends a fake webhook to the URL. 

To start, let's install PHPUnit into the project:

```bash
composer require phpunit/phpunit --dev
```

While Jordi is working on that, go back to the code and find a `tutorials` directory.
This is a special directory that you'll have if you downloaded the code from this
page. It has a few things to make our life easier.

Copy the `WebhookControllerTest.php` file and put it into the `tests/AppBundle/Controller`
directory. Let's check this out: this is the *start* of a testing that's specifically
written for Symfony. If you're not using Symfony, the code will look different, but
the idea is fundamentally the same.

## Testing Strategy

This test boots Symfony's kernel so that we have access to its container, and all
the useful things inside, like the entity manager! I also added a private function
called `createSubscription()`. We're not using this yet, but by calling it, it will
create a new user in the database, give that user an active subscription, and save
everything. This won't be a *real* subscription in Stripe - it'll just live in our
database, and will have a random stripeSubscriptionId.

Because here's the strategy:

1. Create a fake User and fake Subscription in the database;
2. Send a webhook to /webhooks/stripe with *fake* JSON, where the
    subscription id in the JSON matches the fake data in the database;
3. Verify that the subscription is fully canceled after the webhook processes.

Add the test: `public function testStripeCustomerSubscriptionDeleted()`. Ok, step 1:
create the subscription in the database: `$subscription = $this->createSubscription()`.
Easy! Step 2: send the webhook... which I'll put as a TODO for now. Then step 3:
`$this->assertFalse()` that `$subscription->isActive()`. Make sense?

## Sending the Fake Webhook

To send the webhook, we first need to prepare a JSON string that matches what Stripe
sends. At the bottom of the class, create a new private function called
`getCustomerSubscriptionDeletedEvent()` with a `$subscriptionId` argument. To fill
this in, go copty the *real* JSON from the test web hook. Paste it here with
`$json = <<<EOF` enter, and paste!

Now here's the important part: our controller reads the `data.object.id` key to find
the subscription id. Replace this with `%s`. Then, finish the function with
`return sprintf($json, $subscriptionId)`. Now, this function will create a
realistic-looking JSON string, but with whatever subscriptionId we want!

Back in the test function, add `$eventJson = $this->getCustomerSubscriptionDeletedEvent()`
and pass it `$subscription->getStripeSubscriptionId()`, which is some fake, random
value that the function below created.

To send the request, create a `$client` variable set to `$this->createClient()`.
This is Symfony's internal HTTP client: it's job is to make requests to our app.
If you want, you can also use something different, like Guzzle. It doesn't really
matter because - one way or another - you just need to make an HTTP request to the
endpoint.

Now for the magic: call `$client->request()` and pass it a bunch of arguments:
`POST` for the HTTP method, `/webhooks/stripe`, then a few empty arrays for parameters,
files and server. Finally, for the `$content` argument - the *body* of the request -
pass it `$eventJson`.

And because things almost never work for me on the first try, let's
`dump($client->getResponse()->getContent()` to see what happened if there's an error.
And, as a sanity check, `$this->assertEquals()` that 200 matches the
`$client->getResponse()->getStatusCode()`.

Let's run the test! But not in this video... this video is getting too long. Go
get some more coffee and then come back. Then, to the test!
