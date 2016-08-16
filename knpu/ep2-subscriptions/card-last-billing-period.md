# Data: Card Last 4 Digits & Billing Period

Creating the subscriptions is just the first step. There's so many other
details we need to talk about, so what happens when subscriptions are renewed,
or cancelled. Let's start simple. As you can see in our account page, we can,
and probably should be a little bit more helpful to our users. We know that in
stripe, we have a card saved on our user. It would be good to tell our user
what card that is, Visa, and the last four digits on that, so that they know
which card is associated with their accounts.

In our user class, so in other words, our user table, I've already set up two
new columns called card brand and card last four, but these are empty right
now. Information does live inside of stripe on our customer object, we just
don't have access to it. This is going to be yet another thing that we are
going to choose to store in our database so that we can quickly and easily show
it to our user.

First go to the account, the HTML [inaudible 00:01:18] templates, and down
here, let's say, if app.user.cardbrand, and let's print some information about
the users credit card, like app.user.cardbrand, and then ending in
app.user.cardlastfour. That's just referencing the two properties inside of a
user object which are currently empty but in a second, they won't be empty
anymore.

Now as I mentioned, the cards are attached to our customers, and you can see if
you go into the stripe api docs and click on customers. Specifically they're
called sources under the customer object. Notice that sources is plural because
technically you can attach multiple cards to a customer. When we're handling
our checkouts, we're only ever attaching one card to our customers, so we'll
know that sources will always have exactly one thing in it.

In other words, under sources, there will be only one entry. That one entry
will have a data key, and that will describe a card object, which will have all
this information on there. If we want to get the card information, we first
need to get the stripe customer. We are going to record the card information
right at checkout. The [inaudible 00:03:36] at checkout, we already either
create or retrieve our customer, so let's actually set both of these to a
stripe customer object variable. Now in stripe clients, the create customer
method already retrieves the stripe customer object and returns the stripe
customer object, so we're good there.

The update customer card retrieves the customer object but does not return it,
so let's add a little return statement there so that both these methods return
stripe customer objects. Awesome. Now after this, we're going to use that
stripe customer object to update our user class. Instead of doing it here,
because I'd like to keep my controllers light weight, I'm going to do this
inside of subscription helper. Let's set a new public function, update card
details, and what we'll do is we'll take in the user objects that we want
updated and we'll take in a stripe customer object that's associated with that
user.

Really simple here, we can first say card details equals stripe customer arrow
sources.

Let me reset this and I'll move the car. [inaudible 00:05:24] bad time for you
to take [inaudible 00:05:26].

Give me a couple minutes.

Okay, great.

Arrow data, left [score 00:05:38] bracket zero.

Then user arrow set card brand, card details. You look over at the stripe api,
we have brand and we have last four that we're going to use. Arrow brand, and
card details arrow last four. Simple. The last thing we'll just say this with
this [inaudible 00:06:18] arrow persist user, and this arrow [inaudible
00:06:21] flush user to just say the user object.

Now, no matter how we checkout, finally instead of order controller, use this.
This arrow get, subscription arrow helper, arrow update card details, pass the
user, pass the stripe customer and we're good. Now every time we checkout,
we're going to make sure that their customer card information is updated in our
database.

Before we actually make another ... We're going to actually test that. Let's
also fill in next billing app, because the user's going to want to know when
they're going to be billed next. On our subscription class, so our subscription
table, I've already created a billing period ends-at column, so we just need to
make sure that whenever we create a subscription, that gets filled in with when
the billing period ends. In other words, in subscription helper, right here,
where we activate the subscription, we need to make sure that that's period end
shows up.

This method has passed the stripe subscription object, so check that out in the
docs. You'll notice that it has a current period end property, which is a Unix
time stamp, and that's exactly what we need. First, before we activate the
subscription, put a new period end variable, and we need to convert that Unix
time stamp into a nice date time object. We'll say equals/date time, colon,
colon create from format, U, which means Unix time stamp, and then pass it
stripe subscription arrow current underscore period, underscore end.

Then, let's actually pass that into or activate subscription method, so pass
period end here, and then we'll go into subscription, and we'll update activate
subscription to also accept a new date-time object called period end. We'll
just set this arrow billing period ends equals period end. Finally in our
template, we can use that. For next billing app, say if app.user.subscription,
so if they have a subscription, then we'll print
app.user.subscription.billing.period ends at the property that we just set
here. We'll pipe that into Twig's date filter, so we can render it with a nice
month-day string. [inaudible 00:09:44] we'll say, NA.

Phew! Okay, so if you go back down and refresh, it still says none down here,
and this says August 9th actually; that's incorrect because that field is blank
in the database, so it's just defaulting to today, and recording this on August
9th. To give those fields an update, let's actually go and checkout another
subscription. Now in real life, you probably wouldn't allow your users to buy
multiple subscriptions like this. Our database is really only set up for a user
to have one subscription, but it's really handy for testing just to be able to
checkout over and over and over again.

Checkout works, head back to accounts, and there is the correct date. September
9th, one month from now, and a Visa ending in 4242. Okay, so now that we have
this information, we need to handle a couple other really important details
like cancelling subscriptions and updating the correct card, and then we're on
to the hard stuff like webhooks.
