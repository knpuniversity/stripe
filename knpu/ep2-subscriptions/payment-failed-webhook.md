# Webhook: Payment Failed!

Ok, there's just *one* more webhook we need to worry about, and it's the easiest
one: `invoice.payment_failed`. Send a webhook for this event type.

Refresh your RequestBin to check it out.

This webhook type is important for only one reason: to send your user an email so
that they know we're having problems charging their card. That's it! We're already
using a different webhook to actually *cancel* their subscription if there are multiple
failures.

This has almost the same body as the `invoice.payment_succeeded` event: the embedded
object is an `invoice` and if that invoice is related to a subscription, it has a
`subscription` property.

That means that in `WebhookController`, this is a pretty easy one to handle. Add
a new case for `invoice.payment_failed`. Then, start just like before: grab the
`$stripeSubscriptionId`. Then, add an `if` statement - just in case this invoice
has no subscription.

## What to do when a Payment Fails?

Earlier, we talked about what happens when a payment fails. It depends on your
Subscription settings in Stripe, but ultimately, Stripe will attempt to charge the
card a few times, and then cancel the subscription.

You *could* send them an email *each* time it tries to charge their card and fails,
but that'll probably be a bit annoying. So, I like to send an email *only* after the
first attempt fails.

To know if this webhook is being fired afte the first, second or third attempt, use
a field called `attempt_count`. If this equals one, let's send an email. In the
controller, add if `$stripeEvent->data->object->attempt_count == 1`, then send them
an email. Well, I'll leave that step to you guys.

If you need to know *which* user the subscription belongs to, first fetch the
`Subscription` from the database by using our `findSubscription()` method. Then,
add `$user = $subscription->getUser()`.

I like this webhook - it was easy! And actually, we're done with webhooks! Oh, except
for preventing replay attacks... which is painless.
