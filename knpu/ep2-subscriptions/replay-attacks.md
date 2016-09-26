# Webhooks: Preventing Replay Attacks

There's one last teeny, tiny little detail we need to worry about with webhooks:
replay attacks. These are a security concern but *also* a practical one.

We already know that nobody can send us, random, fake event data because we fetch
a fresh event from Stripe:

[[[ code('5b297073e8') ]]]

But, someone *could* intercept a real webhook, and send it to us multiple times.
I don't know why they would do that, but weird things would happen.

And there's also the practical concern. Suppose Stripe sends us a webhook and
we process it. But somehow, there was a connection problem between our server and
Stripe, so Stripe never received our 200 status code. Then, thinking that the webhook
failed, Stripe tries to send the webhook again. If this were for an `invoice.payment_succeeded`
event, one user might get *two* subscription renewal emails. That's weird.

## Creating the stripe_event_log Table

Let's prevent that. And it's simple: create a database table that records
all the event ID's we've handled. Then, query that table before processing a
webhook to make sure we haven't seen it before.

In the `AppBundle/Entity` directory, create a new PHP Class called `StripeEventLog`:

[[[ code('2de91e222f') ]]]

Give it a few properties: `$id`, `$stripeEventId` and a `$handledAt` date field:

[[[ code('643848ce0a') ]]]

Since this project uses Doctrine, I'll add a special `use` statement on top and
then add some annotations, so that this new class will become a new table in the
database. Use the "Code"->"Generate" menu, or `Command` + `N` on a Mac and select "ORM Class":

[[[ code('79419edccd') ]]]

Repeat that and select "ORM Annotations". Choose all the fields:

[[[ code('9ff9970126') ]]]

Update `stripeEventId` to be a `string` field - that'll translate to a varchar in MySQL:

[[[ code('0489cdda17') ]]]

To set the properties, create a new `__construct()` method with a `$stripeEventId`
argument. Inside, set that on the property and also set `$this->handledAt` to a new
`\DateTime()` to set this field to "right now":

[[[ code('7f89322e3b') ]]]

Brilliant! And now that we have the entity class, find your terminal and run:

```bash
./bin/console doctrine:migrations:diff
```

This generates a new file in the `app/DoctrineMigrations` directory that contains
the raw SQL needed to create the new table:

[[[ code('d22d8fb2d0') ]]]

Execute that query by running:

```bash
./bin/console doctrine:migrations:migrate
```

## Preventing the Replay Attack

Finally, in `WebhookController`, start by querying to see if this event has been
handled before. Fetch the EntityManager, and then add
`$existingLog = $em->getRepository('AppBundle:StripeEventLog')` and call
`findOneBy()` on it to query for `stripeEventId` set to `$eventId`.

[[[ code('64703a097b') ]]]

If an `$existingLog` is found, then we don't want to handle this. Just return a
`new Response()` that says "Event previously handled":

[[[ code('8aee1af342') ]]]

If you also want to log a message so that you know when this happens, that's not
a bad idea.

But if there is *not* an existing log, time to process this webhook! Create a new
`StripeEventLog` and pass it `$eventId`. Then, persist and flush *just* the log:

[[[ code('90c5b97114') ]]]

And yea, replay attacks are gone!

## Update the Test!

To make sure we didn't mess anything up, open `WebhookControllerTest` and copy our
test method. Run that:

```bash
./vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

Bah! Of course... it failed for a silly reason: I need to update my *test* database -
to add the new table. A shortcut to do that is:

```bash
./bin/console doctrine:schema:update --force --env=test
```

Try the test now:

```bash
./vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

It works! So hey, run it again!

```bash
./vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

It fails?!

> Failed to assert that `true` is `false`.

Well, that's not clear, but I know what the problem is: every event in the test has
the *same* event ID:

[[[ code('ff27760340') ]]]

So when you run the test the second time, this already exists in the `StripeEventLog`
table and the webhook is skipped. Well hey, at least we know the replay attack system
is working.

To fix this, we need to set a little bit of randomness to the event ID by adding
a` %s` at the end and adding an `mt_rand()` to the `sprintf()`:

[[[ code('b808d0ab36') ]]]

Now, every event ID will be unique. Try the test again:

```bash
./vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

Green and happy!

Ok, *enough* webhooks. Let's do something fun, like making it possible for a user
to *upgrade* from one subscription to another.
