# Webhook: Subscription Canceled

Eventually, this function will handle *several* event types. To handle each, create
a switch-case statement: `switch ($stripeEvent->type)`. That's the field that'll
hold one of those *many* event types we saw earlier.

The *first* type we'll handle is `customer.subscription.deleted`. We'll fill in the
logic here in a second. Add the break. 

We shouldn't receive *any* other event type, but if we do, throw an Exception:
"Unexpected webhook from Stripe" and pass the type.

At the bottom, well, we can return *anything* we want. How about a nice message:
"Event Handled" and then the type. The important thing is to return a 200 status.
If you return a *non* 200 status code, Stripe will think it failed and will try to
send it again, over and over again. But 200 means "yo Stripe, it's cool - I heard
you, I handled it".

## Quick! Cancel the Subscription!

Ok, let's cancel a subscription! First, we need to find the Subscription in our database.
And check this out: the subscription id lives at `data.object.id`. That's because
*this* type of event embeds the subscription in question. Add
`$stripeSubscriptionId = $stripeEvent->data->object->id`.

Next, our subscription table has a `stripeSubscriptionId` field on it. Let's query
to find it! Because I already know I'll want to re-use this, I'll put the logic into
a private function. On this line, call that future function with
`$subscription = $this->findSubscription()` and pass it `$stripeSubscriptionId`.

Scroll down and create this: `private function findSubscription()` with a `$stripeSubscriptionId`
argument. Query for the subscription with
`$subscription = $this->getDoctrine()->getRepository('AppBundle:Subscription')`
and then `findOneBy()` passing this an array with one item: `stripeSubscriptionId` -
the field name to query on - set to `$stripeSubscriptionId`.

If there is *no* matching Subscrpition... well, that shouldn't happen! But just in
case, throw a new Exception with a really confused message. Something is not right.

Finally, return the `$subscription` on the bottom.

Ok, head back up to the action method. Hmm, so all we really need to do now is call
the `cancel()` method on `$subscription`! But let's get a *little* bit more organized.
Open `SubscriptionHelper` and add a new method there: `public function fullyCancelSubscription()`
with the `Subscription` object that should be canceled. Below, really simple, say
`$subscription->cancel()`. Then, use the Doctrine entity manager to save this to
the database.

Mind blown!

Back in the controller, let's call this! Above the switch statement, add a `$subscriptionHelper`
variable set to `$this->get('subscription_helper')`. Finally, call
`$subscriptionHelper->fullyCancelSubscription($subscription)`. And that is it!

Yep, there's some setup to get the webhook controller started, but now we're in
really good shape.

Of course... we have no way to test this... So, ya know, just make sure you do a
*really* good job of coding and hope for the best! I'm kidding, I'll show you a few
ways to test next. But also, don't forget to configure your webhook URL in Stripe
once you finally deploy this to beta and production. I have a webhook setup for
each instance.
