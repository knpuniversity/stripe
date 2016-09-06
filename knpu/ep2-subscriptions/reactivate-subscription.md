# Reactivate/Un-cancel my Subscription!

So, if someone cancels, they can't *un-cancel*. And that's a bummer! 

In the Stripe API docs, under the "canceling" section, there's actually a spot about
reactivating canceled subscriptions, and it's really interesting! It says that if
you use the `at_period_end` method of canceling, and the subscription has *not* yet
reached the period end, then reactivating is easy: just set the subscription's `plan`
to the same plan ID that it had originally. Internally, Stripe knows that means I
want to *not* cancel the subscription anymore.

## Route and Controller Setup

Let's hook it up! We're going to need a new endpoint that reactivates a subscription.
In `ProfileController`, add a new `public function reactivateSubscriptionAction()`.
Give it a route set to `/profile/subscription/reactivate` and a name:
`account_subscription_reactivate`:

[[[ code('9bf8f4d10f') ]]]

Good start! With this in place, copy the route name, open `account.html.twig` and
go up  to the "TODO" we added a few minutes ago. Paste the route, just to stash it
somewhere, then copy the entire *cancel* form and put it here. Update the form `action`
with the new route name, change the text, and use `btn-success` to make this look
like a really happy thing:

[[[ code('bf7e99fb6e') ]]]

Refresh and enjoy the nice, new Reactivate Subscription button. Beautiful!

## Expired Subscriptions Cannot be Reactivated

Let's get to work in the controller. Like everything, this will have two parts.
First, we need to reactivate the subscription in Stripe and second, we need to
update our database. For the first part, fetch the trusty `StripeClient` service
object with `$stripeClient = $this->get('stripe_client')`:

[[[ code('0933048473') ]]]

Next, open that class. Add a new `public function reactivateSubscription()`.
It will need a `User` argument whose subscription we should reactivate:

[[[ code('690162c8fe') ]]]

As the Stripe docs mentioned, we can only reactivate a subscription that has *not*
been fully canceled. If today is *beyond* the period end, then the user will need
to create an entirely new subscription. That's why we only show the button in our
template during this period.

But just in case, add an "if" statement: `if !$user->hasActiveSubscription()`,
then we'll throw a new exception with the text:

> Subscriptions can only be reactivated if the subscription has not actually ended.

[[[ code('5e2297d080') ]]]

Nothing should hit that code, but now we'll know if something does.

## Reactivate in Stripe

To reactivate the Subscription, we first need to fetch it. In the Stripe API docs,
find "Retrieve a Subscription." Every object can be fetched using the same retrieve
method. Copy this. Then, add, `$subscription =` and paste. Replace the subscription
ID with `$user->getSubscription()->getStripeSubscriptionId()`:

[[[ code('48ac532c25') ]]]

And remember, if any API call to Stripe fails - like because this is an invalid
subscription ID - the library will throw an exception. So we don't need to add extra
code to check if that subscription was found.

Finally, reactivate the subscription by setting its `plan` property equal to the
original plan ID, which is `$user->getSubscription()->getStripePlanId()`:

[[[ code('e007849a6a') ]]]

Then, send the details to Stripe with `$subscription->save()`:

[[[ code('a5ac1a2eb9') ]]]

And just in case, return the `$subscription`:

[[[ code('650034bb70') ]]]

Love it! Back in `ProfileController`, reactivate the subscription with,
`$stripeClient->reactivateSubscription($this->getUser())`:

[[[ code('84e1d6bcbe') ]]]

And we are done on the Stripe side.

## Updating our Database

The other thing we need to worry about - which turns out to be really easy - is to
update our database so that this, once again, looks like an active subscription. It's
easy, because we've already done the work for this. Check out `SubscriptionHelper`:
we have a method called `addSubscriptionToUser()`, which is normally used right after
the user originally buys a new subscription:

[[[ code('0168112c9a') ]]]

But we can could also call this after reactivating. In reality, this method simply
ensures that the Subscription row in the table is up-to-date with the latest `stripePlanId`,
`stripeSubscriptionId`, `periodEnd` and `endsAt`:

[[[ code('56bdb3194b') ]]]

These last two are the most important: because they changed when we deactivated
the subscription. So by calling `activateSubscription()`:

[[[ code('a7dedcc6c6') ]]]

All of that will be reversed, and the subscription will be alive!

Let's do it! In `ProfileController`, add a `$stripeSubscription =` in front of the
`$stripeClient` call. Below that, use `$this->get('subscription_helper')->addSubscriptionToUser()`
and pass it `$stripeSubscription` and the current user:

[[[ code('7d15244071') ]]]

And that is everything!

Give your user a happy flash message and redirect back to the profile page:

[[[ code('721550aa87') ]]]

I think we're ready to try this! Go back and refresh the profile. Press reactivate
and... our "cancel subscription" button is back, "active" is back, "next
billing period" is back and "credit card" is back. In Stripe, the customer's most recent
subscription also became active again. Oh man, this is kind of fun to play with:
cancel, reactivate, cancel, reactivate. The system is solid.
