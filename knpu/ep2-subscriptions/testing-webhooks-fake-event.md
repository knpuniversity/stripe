# Testing Part 2: Faking the Event Lookup

Let's run the test. Copy its method name, then open your terminal. It looks like
PHPUnit installed just fine. So, run:

```bash
./vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

Oh no! It blew up! Hmmm:

> Unknown database 'stripe_recording_test'

Ah, my bad!

I setup our project to use a *different* database for testing... and I forgot
to create it! Do that with:

```bash
./bin/console doctrine:database:create --env=test
```

And to create the tables, run:

```bash
./bin/console doctrine:schema:create --env=test
```

Try the test again:

```bash
./vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

Another error! Scroll to the top! The webhook returned a *500* error. And if you look
closely at the dumped response HTML, you can see the reason:

> No such event: evt_00000000000000

Ah, the `id` of the fake event that we're sending is `evt_00000000000000`. That's
*not* a real event in Stripe, and so when the `WebhookController` reads this and
uses Stripe's API to *fetch* this event, it's not there:

[[[ code('194f394201') ]]]

It's kind of funny: we added this API lookup to prevent a third-party from sending
fake events... and now it's stopping us from doing *exactly* that. Dang!

## Faking things in the Test Environment

Hmm, how to fix this? In the real world, we *do* want to use stripe's API to
fetch the `Event` object. But in the test environment, this would all work if our
code would simply use the JSON we're sending it as the event, and skip the lookup.

Let's do it! We'll set a special configuration variable in the *test* environment
only, then use that to change our logic in the controller.

Open `app/config/config.yml` and add a new parameter: `verify_stripe_event` set to
`true`:

[[[ code('34b804ea92') ]]]

Copy that, and open `config_test.yml`. Add a `parameters` key, paste this parameter,
but override it to be `false`:

[[[ code('2d2bdcd872') ]]]

Now, in `WebhookController`, we just need an if statement: if
`$this->getParameter('verify_stripe_event')` is true, then keep the normal behavior.
Otherwise, set `$stripeEvent` to `json_decode($request->getContent())`:

[[[ code('8799828542') ]]]

OK, this is not *technically* perfect: the first `$stripeEvent` is a `\Stripe\Event`
object, and the second will be an instance of `stdClass`. But, since you fetch data
off both the same way, it should work.

Let's see if does! Try the test again:

```bash
./vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

This time, no errors! And the dumped response content looks perfect: event handled.

## Refreshing the Data after the Test

But, the test didn't pass:

> Failed asserting that true is false on line 43

It looks like our webhook is *not* working, because the subscription is still active.
But actually, that's not true: Doctrine is tricking us! In reality, the database
*has* been updated to show that the Subscription is canceled, but this Subscription
object is out-of-date. Query for a fresh one with `$subscription = $this->em` - I
set the EntityManager on that property in `setup()` - then `->getRepository('AppBundle:Subscription')`
with `find($subscription->getId())`:

[[[ code('060d68df1c') ]]]

This subscription will have *fresh* data.

Try the test!

```bash
./vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

And we are green!

I know, that was kind of hard! But if you want to have automated webhook tests,
this is the way to do it. To make matters worse, for other webhooks, you may need
to fake *additional* API calls that you're making to Stripe.

But, there are also a couple of other, manual, but easy ways to test. Let's check
'em out!
