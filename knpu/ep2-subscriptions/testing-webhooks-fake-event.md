# Testing Part 2: Faking the Event Lookup

Hey! Let's try the test. Copy its method name, then head over to the terminal. It
looks like PHPUnit installed just fine. Now, run:

```bash
./vendor/bin/phpunit --filter testStripeCustomerSubscriptionDeleted
```

Ah, it blew up! Hmmm:

> Unknown database 'stripe_recording_test'

Ok, my bad!

--> HERE

unknown database stripe recording_test. I have, in my Symfony project, a
different test database set up. You can see it down here, with database_name
which is our normal database name, _test. That's a Symfony specific thing that
means I need to set up my database as a test environment, which I'll do with
BIN console, doctrine:database:create --env=test. Then same thing with
doctrine:schema. Create. Then, we can try running our test again.

Okay, another big explosion. Let's go all the way to the top, and check this
out. You can see the test actually failed, asserting that 500, a status code,
matches 200. So there was a 500 status code, and down here is actually where
we're dumping the content of the response. You can see immediately in the
title, the problem. No such event evt_00000. What's happening is, the ID of our
fake event is evt_0000. That's clearly not a real event. But we set up our web
hook controller to read the ID off of this, and then use stripe's API to go
fetch this event. We did that to prevent a third party from sending fake
events. Well guess what, we now are that third party trying to send fake
events. Except we do want it to work.

What we basically want is, in the real world, we do want to use stripe's API to
fetch this event object. But in the test environment, we don't want to do this.
We want our code just to use the raw body that we're sending up, without
verifying to see if that's valid with stripe. To do that, we're going to set a
little flag in the test environment only. An app config can fake that YML under
parameters. I'm going to create a new parameter called verify stripe event, set
that to true. I'm going to copy that, and then in config_test.YML, I'll create
a parameters key here, and it will set this to false.

Now, in our web hook controller, we just need an if statement. If this arrow
get parameter, verify stripe event is true, then we'll keep a normal behavior
that looks it up for the API. Else, we're going to fake the stripe event in the
test environment. We'll do that by saying stripe event equals JSON decode
request arrow get content.

This is not technically perfect, because this stripe event up here is a stripe
event object, and this stripe event down here is actually a standard class
object. But both of them allow you to fetch the data off just by using the
arrow operator. Unless you use some other specific stripe event object methods,
this is a good way to fake it.

With that fixed, let's try our test again. This time, no errors. In fact, you
can see our dumped content here looks good, event handled. But failed asserting
that true is false on line 43. It looks like our web hook is not working,
because the subscription is still active. Actually, this is not entirely true,
this is a little thing specific to doctrine. What happens is, the database
probably has been updated, but we need to re-query for a fresh subscription
object, because this subscription object is out of date. We'll say subscription
equals this arrow EM, because I have an entity manager property set up near the
top. Then we'll say, "Get repository, app bundle subscription", and then we'll
find a new fresh subscription.

Then finally, we'll run that, and it works.

This is my favorite way of testing web hooks. It might get even more
complicated later. If your web hook handling logic makes further API requests
to stripe, you're also going to need to fake those. I'm going to show you one
other way.

Let me just mention two other ways that you can test your web hooks. Probably
the most reliable one is to actually use real web hooks. What I mean here is,
after you deploy your code to beta, you can actually set up your web hooks to
point to your beta server. The only problem with that is waiting for a payment
failure to happen over a 30 day period is going to take a really long time to
test your API. One thing you can do is, you can temporarily create a new custom
plan, and down here under interval, you can actually get down all the way to
one day. So at least you can test different situations every day, over about a
week period. You can test that the subscription is renewed. You could then
update your credit card to a credit card who is going to fail payment, and then
wait to see what happens after you fail a payment. It's not perfect, it's slow,
but it's a really nice way to go.

Another option is to actually point the web hooks temporarily at your local
computer. We can't do that now, because my local machine is not accessible to
the internet. But by using a cool utility called Ngrok, you can temporarily set
up a URL locally, that's accessible to the entire internet. Let me show you
what I mean here. I already have this installed. I'm going to go into my
terminal, go into the web directory since that's the document root of our
project. I'm going to type ngrok http 8000, because we want to expose port
8000. As soon as we do that, it gives us a cool little URL here.

Let's go back, paste that in, and it won't work initially because it hits this
little security check to make sure that we are not accessing the dev
environment from outside of our computer. Let's go into the web directory, and
just temporarily, I'm going to comment out these two lines down here that
prevent outside access to the dev environment. Then when you refresh,
everything looks good. This means that we can go to our web hook configuration,
add an end point, switch to the test environment, and we're going to have it
just send us the one event that we support. Right now,
customer.subscription.deleted. Make sure that you have a correct URL set up,
which for us is /webhooks/stripe. That's better.

Here's the cool part. In our application right now, we do have an active
subscription. If we go to customers, we can see our one customer here, and we
can see their current active subscription. The one we have in our database
should be this top most recent one. If we go here and cancel that subscription,
select it immediately so it actually cancels it and sends the web hook event,
that should send the web hook to ngrok, and if we refresh, it should know that
our web hook is canceled. It hasn't worked yet, so let's look into what's going
on here.

If we go to events and web hooks, you should see one on top, and actually
you'll see that this says that it was actually successful. You can even see the
response down here that says we handled that. If we refresh now, it actually
goes away, so we were just a little fast at first there, but that is your web
hook in action. Unfortunately, you can't use that to test all of your web
hooks. There are certain things like a payment failing that might be a little
bit harder, but that's a really good way to go.