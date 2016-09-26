# Webhook: Payment Failed!

Ok, there's just *one* more webhook we need to worry about, and it's the easiest
one: `invoice.payment_failed`. Send a test webhook for this event.

Refresh RequestBin to check it out.

This webhook type is important for only one reason: to send your user an email so
that they know we're having problems charging their card. That's it! We're already
using a different webhook to actually *cancel* their subscription if the failures
continue.

This has almost the same body as the `invoice.payment_succeeded` event: the embedded
object is an `invoice` and if that invoice is related to a subscription, it has a
`subscription` property.

That means that in `WebhookController`, this is a pretty easy one to handle. Add
a new case for `invoice.payment_failed`:

[[[ code('ad9d790828') ]]]

Then, start just like before: grab the `$stripeSubscriptionId`. Then, add an `if`
statement - just in case this invoice has no subscription:

[[[ code('658110b44a') ]]]

## What to do when a Payment Fails?

Earlier, we talked about what happens when a payment fails. It depends on your
Subscription settings in Stripe, but ultimately, Stripe will attempt to charge the
card a few times, and then cancel the subscription.

You *could* send your user an email *each* time Stripe tries to charge their card
and fails, but that'll probably be a bit annoying. So, I like to send an email *only*
after the first attempt fails.

To know if this webhook is being fired after the first, second or third attempt,
use a field called `attempt_count`. If this equals one, send an email. In the
controller, add if `$stripeEvent->data->object->attempt_count == 1`, then send them
an email. Well, I'll leave that step to you guys:

[[[ code('fd0f0b1980') ]]]

If you need to know *which* user the subscription belongs to, first fetch the
`Subscription` from the database by using our `findSubscription()` method. Then,
add `$user = $subscription->getUser()`:

[[[ code('c29a073064') ]]]

I like this webhook - it's easy! And actually, we're done with webhooks! Except
for preventing replay attacks... which is important, but painless.
