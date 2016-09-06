# So, When is my Next Invoice?

Back on the account page, our customers would *love* us if we would show them *when*
they will be billed next. In other words: when will my subscription renew?

Open the `Subscription` class - aka the subscription table. I've already added a
`billingPeriodEndsAt` column:

[[[ code('f50e0b8d81') ]]]

*All* we need to do is set this when the subscription is first created. Ok, we also
need to update it each month when the subscription is renewed - but we'll talk about
that later with webhooks.

The `Subscription` is created - or retrieved - right here in `SubscriptionHelper`.
This is the spot to set that date.

And hey! This method is passed a `\Stripe\Subscription` object:

[[[ code('502804fba9') ]]]

Let's check out the API docs to see what that gives us. Oh yes, it has
a `current_period_end` property, which is a UNIX timestamp. Bingo!

## Setting the billingPeriodEndsAt

In `SubscriptionHelper`, before we activate the subscription, add a new `$periodEnd`
variable. To convert the timestamp to a `\DateTime` object, say
`$periodEnd = \DateTime::createFromFormat('U')` - for UNIX timestamp -
`$stripeSubscription->current_period_end`:

[[[ code('ff570c0a91') ]]]

Now, pass *that* into the `activateSubscription()` method as a new argument:

[[[ code('a4b67fd12e') ]]]

Open that function in `Subscription` and add a new `\DateTime` argument called `$periodEnd`.
Set the property with `$this->billingPeriodEndsAt = $periodEnd`:

[[[ code('837ef79587') ]]]

Done!

## Rendering the Next Billing Date

To celebrate, open the `account.html.twig` template. For "Next Billing At", add
`if app.user.subscription` - so if they have a subscription - then print
`app.user.subscription.billingPeriodEndsAt|date('F jS')` to format the date
nicely:

[[[ code('3f021ff04a') ]]]

OK team! Refresh that page! The "Next Billing At" is... wrong! August 9th! That's
today! But no worries, that's just because the field is blank in the database, so
it's using today. To *really* test if this is working, we need to checkout with a
new subscription.

Now, in real life, you probably won't allow your users to buy multiple subscriptions.
Afterall, we're only storing info in the database about *one* Subscription, per user.
But, for testing, it's really handy to be able to checkout over and over again.

The checkout worked! Click "Account". Yes! There is the correct date: September
9th, one month from today. And the VISA card ends in 4242.

Alright: the informational part of the account page is done. But, the user *still*
needs to be able to do some pretty important stuff, like cancelling their subscription -
yes, this *does* happen, it's nothing personal - and updating their credit card.
Let's get to it.
