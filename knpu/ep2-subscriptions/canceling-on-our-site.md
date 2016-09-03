# Tracking Cancelations in our Database

When the user cancels, we need to *somehow* update the user's row in the subscription
table so that we know this happened! And actually, it's kind of complicated: the
user canceled, but the subscription should still be active until the end of the
month. *Then* it'll really be canceled. So, how the heck can we manage this?

## Using Subscription endsAt

Open `ProfileController`. Right after we cancel the subscription in Stripe, grab
the subscription object by saying, `$this->getUser()->getSubscription()`. Here's
the plan: we are *not* going to delete the subscription from the subscription
table, because it's still active until the period end. Instead, we'll set the
`endsAt` date field to *when* the subscription will expire. That way, we'll know
if the susbcription is still active, meaning it's before the `endsAt` date, or it's
fully canceled, because it's after the `endsAt` date.

At the bottom of `Subscription`, add a helper function to do this:
`public function deactivateSubscription()`. Since we know the user has paid through
the end of the period, we can use that: `$this->endsAt = $this->billingPeriodEndsAt`.
Also set `$this->billingPeriodsEndsAt = null` - just so we know that there won't
be another bill at the end of this month.

Cool! To deactivate the subscription in the controller, it's as easy as saying
`$subscription->deactivateSubscription()` and then saving it to the database with
the standard `persist()` and `flush()` Doctrine code.

And that should do it! Let's give this guy a try. Go to the account page, then press
the new "Cancel Subscription" button. Ok, looks good! Check the customer page in
the Stripe dashboard. Yes! The most recent subscription - the one we're dealing
with in our code - is *active* but will cancel at the end of the month.

## Showing "Canceled" on your Account

But if you look at the Account page, everything here still looks "Active". We updated
the `endsAt` field on the subscription, but our code in this template isn't smart
enough... yet.

Open `account.html.twig`. Hmm, I need an easy way to know whether or not a subscription
is active, and if it *is* active, whether or not it's in this canceled state.

To help with this, let's create two methods inside the `Subscription` class.
First, `public function isActive()`. Meaning: does the user still have an active
subscription, even if it will cancel at the month's end? So, if
`$this->endsAt === null`, then the subscription is definitely active. OR,
`$this->endsAt` is greater than right now, `new \DateTime()`, meaning the subscription
is canceled, but is ending in the future.

The second method we need is `public function isCanceled()`, meaning: if the subscription
is active, has the user actually canceled it or not? This will simply be,
`return $this->endsAt !== null`.

Oh man, our setup is getting fancy! Let's get even fancier with one more helper
method, this time in `User`. Add a new `public function hasActiveSubscription()`.
A `User` has an active subscription if they have a subscription object related to
them and that subscription object `isActive()`. That'll save us some typing whenever
we need to check whether or not a user has an active subscription.

## Making the Account Template Awesome

Ok, back to the account template! This time, to be heros!

First, that "Cancel Subscription" button should only be there if the user has an
active subscription. No problem! Add `if app.user.hasActiveSubscription()`. But even
here, if the user has already *canceled* their subscription, we don't want to keep
showing them this button. Add another if: `if app.user.subscription.isCancelled()`,
then add a little "TODO" to add a re-activate button. If they've cancelled, they
might remember how cool your service is and want to come back LATER and reactivate!
In the else, show them the Cancel button.

Finish up the `endif` and the other `endif`. And actually, copy these first two lines:
we need to re-use them further below. In the section that tells us whether or not
we have an active subscription, we now have three states:  "active",
"active but canceled," and "none." Replace the old `if` statement with the two
that you just copied. If the subscription is canceled, add `label-warning` and say
"Canceled". Else, we know it's active.

If a user doesn't have any type of active subscription, keep the "none" from before.

Finally, copy *just* the first `if` statement and scroll down to "Next Billing at".
We should *only* show the next billing period if the user has an *active* subscription,
not just if they have a related subscription object, because it could be canceled.
Paste the `if` statement over this one.

Finally, do the same thing down below for the credit card: I don't want to confuse
someone by showing them credit card information when they don't have a subscription.

Phew! Ok, refresh! It's beautiful! There's our todo for the reactivate button and
the subscription is canceled. But wait! We don't want the "Next Billing at" and
credit card information to show up.

Ah, that's my bad! The `hasActiveSubscription()` returns true *even* if the user
already cancelled it. Open `User`: let's add one more method: 
`public function hasActiveNonCanceledSubscription()`. Inside,
`return $this->hasActiveSubscription() && !$this->getSubscription()->isCancelled()`.
Use this method in both places in the Twig template.

Refresh one more time! We got it!

But now that the user can cancel, let's make it possible for them to *reactivate*
the subscription. It's actually an easy win.
