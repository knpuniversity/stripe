# Webhook: Email User on Subscription Renewal

The second webhook *type* we need to handle is called `invoice.payment_succeeded`.
This one fires when a subscription is successfully *renewed*. Well, actually,
it fires whenever *any* invoice is paid, but we'll sort that out later.

This webhook is important to us for 2 reasons.

First, each subscription has a `billingPeriodEndsAt` value that we use to show the
user when Stripe will charge them next. Obviously, that needs to be updated each month!

Second, when you charge your customer, you should probably send them a nice email
about it, and maybe even attach a receipt! So let's get this setup.

## Receive all Webhook Types

Right now, Stripe is *not* sending us this webhook type. In the dashboard, update
the RequestBin webhook and set it to receive *all* webhooks, instead of just the
few that we select. You don't *need* to do this, but it does make it easier to keep
your various webhooks - like for the staging and production servers - identical.

But now we need to do some work in `WebhookController`. In the `default` section
of the switch-case, we *will* now receive unsupported webhooks, and that's cool!
Remove that exception.

## Inspecting the invoice.payment_succeeded Webhook

Next, every webhook type looks a little different. Head back to the dashboard to
send a test webhook, this time for the `invoice.payment_succeeded` event type. Hit
"Send test webhook" then go refresh your RequestBin.

Hmm, ok. This time, the embedded object is an invoice. But *we* will need to know
the Stripe subscription id that this invoice is for. And that's tricky: an invoice
may *not* actually contain a subscription. If you just buy some products on our site,
that creates an invoice... but with no subscription.

Fortunately, the `data` key covers this: it has a `subscription` field. This will
either be blank if there's no subscription or it will hold the subscription id.
In other words, awesome!

## Handling invoice.payment_succeeded

Go back into `WebhookController` and add a second `case` statement for statement
for `invoice.payment_succeeded`. Add the `break`, then let's get straight to business.
First, grab the subscription id with
`$stripeSubscriptionId = $stripeEvent->data->object->subscription`.

Next, if there *is* a `$stripeSubscriptionId`, then we need to load the corresponding
`Subscription` from the database. Re-use the `$this->findSubscription()` method from
earlier to do that.

Remember: the goal is to update this Subscription row to have the *new* `billingPeriodEndsAt`.
But the event's data doesn't have that date! No problem: if we fetch a fresh, full
`Subscription` object from Stripe's API, we can use its `current_period_end` field.

Open up `StripeClient` and add a new `public function findSubscription()` with a
`$stripeSubscriptionId` argument. Make this return
`\Stripe\Subscription::retrieve($stripeSubscriptionId)`.

Cool! Back in `WebhookController`, add
`$stripeSubscription = $this->get('stripe_client')->findSubscription()` and pass
it `$stripeSubscriptionId`.

## Updating the Subscription in the Database

Finally, let's update the Subscription in our database by using the data on the
Stripe subscription object. As usual, let's put this logic into `SubscriptionHelper`
so we can reuse it later.

Add a new public function called `handleSubscriptionPaid()` that has two arguments:
the `Subscription` object that just got paid and the related `\Stripe\Subscription`
object that holds the updated info.

First, we need to read the `current_period_end` field. But wait! We did this earlier
in `addSubscriptionToUser()`. Go steal that line! Past it here, but rename the
variable to `$newPeriodEnd`.

Now, let's set this `billingPeriodEndsAt` field with `$subscription->setBillingPeriodEnds()`.
But wait! Where's my auto-completion! Oh, that method doesn't exist yet. In `Subscription`,
I'll use the Code->Generate shortcut to select "Setters" and generate this setter.

Whoops, then update the method in `SubscriptionHelper` to be `setBillingPeriodEndsAt()`.

Finally, let's celebrate! Persist and flush the Subscription changes to the database.
Back in your controller, call this: `$subscriptionHelper->handleSubscriptionPaid()`
and pass it `$subscription` and `$stripeSubscription`.

I won't test this - let's call that homework for you - but now whenever a subscription
is renewed, the `billingPeriodEndsAt` will be updated.

## Sending an Email on Renewal

But there's just *one* other small important thing you'll want to do each time a
subscription is renewed: send the user an email! Ok, we're not *actually* going
to code up the email-sending logic - but it would live right here in `SubscriptionHelper`.

But wait! There is one gotcha: the `invoice.payment_succeeded` webhook will be triggered
when a subscription is renewed... but it will *also* be triggered at the moment that
the user *originally* buys their subscription. So if you send your user an email
that says: "thanks for renewing your subscription", then all of your new users will
be pretty darn surprised.

To fix this, add a new `$isRenewal` variable set to
`$newPeriodEnd > $subscription->getBillingPeriodEndsAt()`.

If this is a new subscription, then it was completed about 2 seconds ago, and we
would have already set the `billingPeriodEndsAt` to the correct date. When the webhook
fires, the dates will already match. But if this is a renewal, the `billingPeriodEndsAt`
in the database will be for *last* month, and `$newPeriodEnd` will be for next month.

In other words, you can use the `$isRenewal` flag to send the right *type* of email.
