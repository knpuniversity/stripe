# Cancelation Edge-Case Bugs

I hope you now think that canceling and reactivating feels pretty easy! Well, it is!
Except for 2 minor, edge-case bugs that have caused us problems in the past.
Let's fix them now.

## Problem 1: Canceling Past Due Accounts

First, go to the Stripe API docs and go down to subscription. You'll notice
that one of the fields is called `status`, which has a number of different
values. The most important ones for us are `active`, `past_due`, which means it's
still in an active state, but we're having problems charging their card, and
`canceled`.

Here's problem number 1: at the end of the month, Stripe will try to charge your
user for the renewal. To do that, it will create an invoice and then charge
that invoice. If, for some reason, the user's credit card can't be charged, the
invoice remains created and Stripe will try to charge that invoice a few more
times. That's something we'll talk a lot more about in a few minutes.

Now, imagine that the invoice has been created and we're having problems charging
the user's credit card. Then, the user goes to our site and cancels. Since we're
canceling "at period end", the invoice in Stripe won't be deleted, and Stripe will
continue to try to charge that invoice a few more times. In other words, we will
attempt to charge a user's credit card, after they cancel! Not cool!

To fix this, we need to *fully* cancel the user's subscription. That will close the
invoice and stop future payment attempts on it.

## Squashing the Bug: Fully Cancel

In `StripeClient::cancelSubscription()`, it's time to squash this bug. First, create
a new variable called `$cancelAtPeriodEnd` and set it to `true`. Then, down below, set
the `at_period_end` option to this variable:

[[[ code('d774564604') ]]]

Now, here's the trick: if `$subscription->status == 'past_due'`, then it means that
the invoice *has* been created and we're having problems charging it. In this case,
set `$cancelAtPeriodEnd` to `false`:

[[[ code('e6ef024163') ]]]

This will cause the subscription to cancel immediately and close that invoice!

## Problem 2: Canceling within 1 Hour of Renewal

But there's one other, weirder, but similar problem. At the end of the month, 1 hour
before charging the user, Stripe creates the invoice. It then waits 1 hour, and tries
to charge the user for the first time. So, if your user cancels *within* that hour,
then we also need to fully cancel that subscription to prevent its invoice from being paid.

This is a little trickier: we basically need to see if the user is canceling within
that one hour window. To figure that out, create a new variable called `$currentPeriodEnd`
and set that to a `\new DateTime()` with the `@` symbol and `$subscription->current_period_end`:

[[[ code('9bc65cfea7') ]]]

This converts that timestamp into a `\DateTime` object.

Now, if `$currentPeriodEnd < new \DateTime('+1 hour')`, then this means that we're
probably in that window and should set `$cancelAtPeriodEnd = false`:

[[[ code('a967624162') ]]]

An easy way of thinking of this is, if the user is *pretty* close to the end of their
period, then canceling now versus at period end, is almost the same. So, we'll just
be careful.

But for this to work, your server's timezone needs to be set to UTC, which is the
timezone used by the timestamps sent back from Stripe. If you're not sure, you could
give yourself some more breathing room, but fully-canceling anyone's subscription
that is within one day of the period end.

## Fully Canceling in the Database

These fixes created a *new* problem! Now, when the user clicks the "Cancel Subscription"
button, we *might* be canceling the subscription *right* now, and we need to update
the database to reflect that. 

To do that, first return the `$stripeSubscription` from the `cancelSubscription()`
method:

[[[ code('7c5e453c08') ]]]

Then, in `ProfileController`, add `$stripeSubscription =` before the `cancelSubscription()`
call:

[[[ code('d2ab203449') ]]]

Finally, we can use the `status` field to know whether or not the subscription has
truly been canceled, or if it's still active until the period end. In other words,
if `$stripeSubscription->status == 'canceled'`, then the subscription is done! Else,
we're canceling at period end and should just call `deactivate()`:

[[[ code('e69bbb6676') ]]]

To handle full cancelation, open up `Subscription` and add a new public function
called `cancel()`. Here, set `$this->endsAt` to right now, to guarantee that it will
look canceled, and `$this->billingPeriodEndsAt = null`:

[[[ code('5fadfa1fd4') ]]]

In `ProfileController`, call it: `$subscription->cancel()`:

[[[ code('7c92ce1129') ]]]

And we are done!

Now, testing this is a bit difficult. So let's just make sure we didn't break anything
major by hitting cancel. Perfect! And we can reactivate.

And *this* is why subscriptions are hard.
