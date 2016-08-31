# Webhooks: Preventing Replay Attacks

There's one last teeny, tiny little detail that we need to worry about with webhooks:
replay attacks. This is both a security concern *and* a practical concern.

We already know that nobody can send us, random, fake event data because we fetch
fresh event data from Stripe. But, someone *could* intercept a valid webhook, and
send it to us multiple times. I don't know why they would do that, but weird things
would happen.

And there's also a more practical concern. Suppose Stripe sends us a webhook and
we process it. But somehow, there was a connection problem between our server and
Stripe, so Stripe never received our 200 status code. Then, thinking that the webhook
failed, Stripe tries to send the webhook again. If this were for an `invoice.payment_succeeded`
event, one user might receive *two* subscription renewal emails. I do *not* like
that.

## Creating the stripe_event_log Table

Let's prevent replay attacks. And it's simple: create a database table that records
all the event id's we've handled. Then, just query that table before processing a
webhook to make sure we haven't seen it before.

In the `AppBundle/Entity` directory, create a new PHP Class called `StripeEventLog`.
Give it a few properties: `$id`, `$stripeEventId` and a `$handledAt` date field.

Since this project uses Doctrine, I'll add a special `use` statement on top and
then add some annotations, so that this new class will become a new table in the
database. Use the Code->Generate menu, or command+N on a Mac and select "ORM Class".
Then, repeat that and select "ORM Annotation". Choose all the fields.

Update `stripeEventId` to be a `string` field - that'll translate to a varchar in
the database.

To set the properties, create a new `__construct()` method with a `$stripeEventId`
argument. Inside, set that on the property and also set `$this->handledAt` to a new
`\DateTime()` to set this field to "right now".

Brilliant! Now that we have the entity class, find your terminal and run:

```bash
bin/console doctrine:migrations:diff
```

This generates a new file in the `app/DoctrineMigrations` directory that contains
the raw SQL needed to create this new table. Execute that query by running:

```bash
bin/console doctrine:migrations:migrate
```

## Preventing the Replay Attack

Finally, in `WebhookController`, start by querying to see if this event has been
handled before. First, fetch the EntityManager, and then add
`$existingLog = $em->getRepository('AppBundle:StripeEventLog')` and call
`findOneBy()` on it to query for `stripeEventId` set to `$eventId`.

If an `$existingLog` exists, then we don't want to handle this. Just return
`new Response()` that says "Event previously handled". If you want to log a
message so that you know when this happens, that's not a bad idea.

But if there is *not* existing log, time to process this webhook! Create a new
`StripeEventLog` and pass it `$eventId`. Then, persist and flush *just* the log.

And yea, replay attacks are gone!

## Update the Test!

To make sure we didn't mess anything up, open `WebhookControllerTest` and copy our
test method. Run that:

```bash
vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

Bah! Of course... but for a silly reason: I need to update my *test* database - it's
different than my normal, development database. A shortcut to do that is:

```bash
bin/console doctrine:schema:update --force --env=test
```

Try the test now:

```bash
vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

It works! So hey, run it again!

```bash
vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

It fails?!

> Failed to assert that true is false.

Ah, every event in the test has the *same* event id. So when you run the test the
second time, this already exists in the `StripeEventLog` table and the webhook
is skipped. Well hey, at least we know that system is working.

To fix this, we need to set a little bit of randomness to the event id by adding
a` %s` at the end and adding an `mt_rand()` to the `sprintf`.

Now, every event id will be unique. Try the test again:

```bash
vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

Everything is happy!

Ok, let's move on from webhooks to something fun: allowing our customers to *upgrade*
from one subscription to another.
