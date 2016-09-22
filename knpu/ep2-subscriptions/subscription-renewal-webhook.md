# Webhook: Email User on Subscription Renewal

The second webhook *type* we need to handle is called `invoice.payment_succeeded`.
This one fires when a subscription is successfully *renewed*. Well, actually,
it fires whenever *any* invoice is paid, but we'll sort that out later.

This webhook is important to us for 2 reasons.

First, each subscription has a `$billingPeriodEndsAt` value that we use to show the
user when Stripe will charge them next:

[[[ code('ae529ce01e') ]]]

Obviously, that needs to be updated each month!

Second, when you charge your customer, you should probably send them a nice email
about it, and maybe even attach a receipt! So let's get this setup.

## Receive all Webhook Types

Right now, Stripe is *not* sending us this webhook type. In the dashboard, update
the RequestBin webhook and set it to receive *all* webhooks, instead of just the
few that we select. You don't *need* to do this, but it does make it easier to keep
your various webhooks - like for your staging and production servers - identical.

But now we need to do some work in `WebhookController`. In the `default` section
of the switch-case, we *will* now receive unsupported webhooks, and that's cool!
Remove the exception:

[[[ code('b2fbc4565d') ]]]

## Inspecting the invoice.payment_succeeded Webhook

Each webhook type has a JSON body that looks a little different. Head back to the
dashboard to send a test webhook, this time for the `invoice.payment_succeeded` event
. Hit "Send test webhook" and then go refresh RequestBin.

Hmm, ok. This time, the embedded object is an invoice. But *we* will need to know
the Stripe subscription ID that this invoice is for. And that's tricky: an invoice
may *not* actually contain a subscription. If you just buy some products on our site,
that creates an invoice... but with no subscription.

Fortunately, the `data` key covers this: it has a `subscription` field. This will
either be blank if there's no subscription or it will hold the subscription ID.
In other words, it's perfect!

## Handling invoice.payment_succeeded

Back in `WebhookController` and add a second `case` statement `invoice.payment_succeeded`.
Add the `break`, then let's get to work:

[[[ code('4faf2abf6d') ]]]

First, grab the subscription ID with `$stripeSubscriptionId = $stripeEvent->data->object->subscription`:

[[[ code('03863ef6b2') ]]]

Next, if there *is* a `$stripeSubscriptionId`, then we need to load the corresponding
`Subscription` from our database. Re-use `$this->findSubscription()` from earlier
to do that:

[[[ code('a241c3596e') ]]]

Remember: the goal is to update this Subscription row to have the *new* `billingPeriodEndsAt`.
But the event's data doesn't have that date! No problem: if we fetch a fresh, full
`Subscription` object from Stripe's API, we can use its `current_period_end` field.

Open up `StripeClient` and add a new `public function findSubscription()` with a
`$stripeSubscriptionId` argument:

[[[ code('afec3d2fc2') ]]]

Make this return the classic `\Stripe\Subscription::retrieve($stripeSubscriptionId)`:

[[[ code('f8e70d3833') ]]]

Cool! Back in the controller, add `$stripeSubscription = $this->get('stripe_client')->findSubscription()`
and pass it `$stripeSubscriptionId`:

[[[ code('70997731d6') ]]]

## Updating the Subscription in the Database

Finally, let's update the Subscription in our database by using the data on the
Stripe subscription object. As usual, we'll add this logic to `SubscriptionHelper`
so we can reuse it later.

Add a new public function called `handleSubscriptionPaid()` that has two arguments:
the `Subscription` object that just got paid and the related `\Stripe\Subscription`
object that holds the updated details:

[[[ code('8eccccef87') ]]]

Then, we need to read the `current_period_end` field. But wait! We totally did this
earlier in `addSubscriptionToUser()`. Steal that line! Paste it here, but rename
the variable to `$newPeriodEnd`:

[[[ code('12417c96b6') ]]]

Now, set this `billingPeriodEndsAt` field via `$subscription->setBillingPeriodEnds()`:

[[[ code('22d28392b1') ]]]

But wait! Where's my auto-completion! Oh, that method doesn't exist yet. In `Subscription`,
I'll use the "Code"->"Generate" shortcut to select "Setters" and generate this setter:

[[[ code('20ce6606b8') ]]]

Whoops, then update the method in `SubscriptionHelper` to be `setBillingPeriodEndsAt()`:

[[[ code('22d28392b1') ]]]

Finally, celebrate! Persist and flush the Subscription changes to the database:

[[[ code('62a6a180ed') ]]]

Back in your controller, call this: `$subscriptionHelper->handleSubscriptionPaid()`
and pass it `$subscription` and `$stripeSubscription`:

[[[ code('3308b3b7b9') ]]]

I won't test this - let's call that homework for you - but now whenever a subscription
is renewed, the `$billingPeriodEndsAt` will be updated.

## Sending an Email on Renewal

But there's just *one* other small important thing you'll want to do each time a
subscription is renewed: send the user an email! Ok, we're not *actually* going
to code up the email-sending logic now - but it would live right here in `SubscriptionHelper`.

But wait! There is one gotcha: the `invoice.payment_succeeded` webhook will be triggered
when a subscription is renewed... but it will *also* be triggered at the moment that
the user *originally* buys their subscription. So if you send your user an email
that says: "thanks for renewing your subscription", then any new users will be pretty
confused.

To fix this, add a new `$isRenewal` variable set to
`$newPeriodEnd > $subscription->getBillingPeriodEndsAt()`:

[[[ code('652f40364e') ]]]

If this is a new subscription, then it was completed about 2 seconds ago, and we
would have already set the `billingPeriodEndsAt` to the correct date. When the webhook
fires, the dates will already match. But if this is a renewal, the `billingPeriodEndsAt`
in the database will be for *last* month, and `$newPeriodEnd` will be for next month.

In other words, you can use the `$isRenewal` flag to send the right *type* of email.
