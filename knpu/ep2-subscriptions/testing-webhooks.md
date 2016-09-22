# Testing Webhooks

The reason that webhooks are so hard is... well, they're kind of impossible to
test. There are certain things on Stripe - like my card being declined during a
renewal, which are *really* hard to simulate. And even if we could, Stripe can't
send a webhook to my local computer. Well actually, that last part isn't entirely
true, but more on that later.

So let's look at a few strategies for making sure that your webhooks are
*air tight*. I do *not* want to mess something up with these.

## Automated Test

The first strategy - and the one we use here on KnpU - is to create an automated
test that sends a fake webhook to the URL. 

To start, let's install PHPUnit into the project:

```bash
composer require phpunit/phpunit --dev
```

While Jordi is working on that, go back to the code and find a `tutorials` directory.
This is a special directory I created and you should have it if you downloaded the
start code from this page. It has a few things to make our life easier.

Copy the `WebhookControllerTest.php` file and put it into the `tests/AppBundle/Controller`
directory. Let's check this out:

[[[ code('150955bd6b') ]]]

This is the *start* of a test that's specifically written for Symfony. If you're not
using Symfony, the code will look different, but the idea is fundamentally the same.

## Testing Strategy

This test boots Symfony's kernel so that we have access to its container, and all
the useful objects inside, like the entity manager! I also added a private function
called `createSubscription()`. We're not using this yet, but by calling it, it will
create a new user in the database, give that user an active subscription, and save
everything. This won't be a *real* subscription in Stripe - it'll just live in our
database, and will have a random `stripeSubscriptionId`.

Because here's the strategy:

1. Create a fake User and fake Subscription in the database;
2. Send a webhook to `/webhooks/stripe` with *fake* JSON, where the
   subscription id in the JSON matches the fake data in the database;
3. Verify that the subscription is fully canceled after the webhook finishes.

Add the test: `public function testStripeCustomerSubscriptionDeleted()`:

[[[ code('038f6537c6') ]]]

OK, step 1: create the subscription in the database: `$subscription = $this->createSubscription()`.
Easy! Step 2: send the webhook... which I'll put as a TODO for now. Then step 3:
`$this->assertFalse()` that `$subscription->isActive()`:

[[[ code('e6a00dc0e2') ]]]

Make sense?

## Prepping some Fake JSON

To send the webhook, we first need to prepare a JSON string that matches what Stripe
sends. At the bottom of the class, create a new private function called
`getCustomerSubscriptionDeletedEvent()` with a `$subscriptionId` argument:

[[[ code('c9c3b2c52a') ]]]

To fill this in, go copy the *real* JSON from the test webhook. Paste it here with
`$json = <<<EOF` enter, and paste!

[[[ code('229d29d4ba') ]]]

Now here's the important part: our controller reads the `data.object.id` key to find
the subscription id. Replace this with `%s`:

[[[ code('459979901a') ]]]

Then, finish the function with `return sprintf($json, $subscriptionId)`:

[[[ code('f9ca15f5df') ]]]

Now, this function will create a realistic-looking JSON string, but with whatever `subscriptionId` we want!

Back in the test function, add `$eventJson = $this->getCustomerSubscriptionDeletedEvent()`
and pass it `$subscription->getStripeSubscriptionId()`, which is some fake, random
value that the function below created:

[[[ code('260d1351a7') ]]]

## Sending the Fake Webhook

To send the request, create a `$client` variable set to `$this->createClient()`.
This is Symfony's internal HTTP client: its job is to make requests to our app.
If you want, you can also use something different, like Guzzle. It doesn't really
matter because - one way or another - you just need to make an HTTP request to the
endpoint.

Now for the magic: call `$client->request()` and pass it a bunch of arguments:
`POST` for the HTTP method, `/webhooks/stripe`, then a few empty arrays for parameters,
files and server. Finally, for the `$content` argument - the *body* of the request -
pass it `$eventJson`:

[[[ code('d981695132') ]]]

And because things almost never work for me on the first try... and because I *know*
this won't work yet, let's `dump($client->getResponse()->getContent())` to see what
happened in case there's an error. Also add a sanity check, `$this->assertEquals()`
that 200 matches `$client->getResponse()->getStatusCode()`:

[[[ code('45feba117a') ]]]

Let's run the test! But not in this video... this video is getting too long. So go
get some more coffee and then come back. Then, to the test!
