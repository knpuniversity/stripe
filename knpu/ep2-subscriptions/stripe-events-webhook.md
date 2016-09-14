# Stripe Events & Webhooks

Oh boy, it's *finally* time to talk about the big subject I've been avoiding: webhooks.
You see, every month Stripe is going to *try* to renew each customer's subscription.
If it fails to charge their card, you might want to send them an email. If it's
successful, you might want to send them a receipt. Or, if it fails so many times
that the subscription needs to be cancelled, then we need to update our database
to reflect that.

In short, Stripe needs to communicate back to *us* when certain things, or *events*
happen.

## Stripe Events

Go to our Customer page in Stripe. At the bottom, you'll see an *events* section.
Basically, whenever *anything* happens in Stripe, an event is created. For example,
when we added a new card, there was an event whose `type` field is set to
`customer.source.created`. Every action becomes an *event* and there are many different
*types* of events for the many things that can happen.

In fact, switch over to Stripe's API docs. Event is so important that it's an *object*
in the API: you can list and fetch them. Click [Types of events][event_types].
Awesome! A big, giant, beautiful list of all the different event types so you can
figure out what each event means.

## What Happens when we Can't Charge a User?

Out of this list, there are just a few types that you'll need to worry about. The
first is the event type that occurs when the customer's subscription is canceled
when Stripe can't charge their card for renewal.

So, what *actually* happens when Stripe can't charge a card? Go back to the Stripe
Dashboard and go to "Account Settings", and then "Subscriptions". This screen is *really*
important: it determines *exactly* what happens when a card can't be charged. By
default, Stripe will attempt to charge the card once, then try again 3, 5 and 7 days
later. If it *still* can't charge the card, it will finally cancel the subscription.
You can tweak the timing, but the story is always this: Stripe tries to charge
a few times, then eventually cancels the subscription.

## Hello Webhooks & requestb.in

When this happens, we need Stripe to tell us that the subscription was canceled.
And we'll do this via a webhook. It's pretty simple: we configure a webhook URL
to our site in Stripe. Then, whenever certain event *types* happen, Stripe will
send a request to our URL that contains the *event* object as JSON. So if Stripe
sent us a webhook whenever a subscription was canceled, we would be in business!

A really nice way to test out webhooks is by using a site called [http://requestb.in][request_bin].
Click "Create a RequestBin". Ok, real simple: this page will record and display
any requests made to this temporary URL. 

Back on our dashboard, add a webhook and paste the URL. Put it in test mode, so
that we only receive events from the "test" environment. Next, click "Select events".
Instead of receiving *all* event types, let's just choose the few we want. For now,
that's just `customer.subscription.deleted`.

Yep, this is the event that happens when a subscription is cancelled, for any reason,
including when a user's card couldn't be charged.

Create that webhook! Ok, let's see what a test webhook looks like. Click
"Send test webhook" and change the event to `customer.subscription.deleted`. Now,
send that webhook!

Refresh the RequestBin page. So cool! This shows us the raw JSON request body that
was just sent to us. These events are just objects in Stripe's API, like Customer,
Subscription or anything else. But if you configure a webhook, then Stripe will send
that event to *us*, instead of us needing to fetch it from them.

Here's the next goal: setup a route and controller on our site that's capable of
handling webhooks and doing different things in our system when different event
types happen.


[event_types]: https://stripe.com/docs/api#event_types
[request_bin]: http://requestb.in
