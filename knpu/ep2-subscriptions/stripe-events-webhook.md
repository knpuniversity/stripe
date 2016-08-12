# Stripe Events Webhook

It's now time to talk about the big subject I've been avoiding, webhooks. You see, every month Stripe is going to try to renew your user subscription, but if it fails to charge your user then you might want to send them an email, or when it's successful you might want to send an email, or if it fails so many times that the subscription needs to be cancelled, then we need to know about that on our side. In short, Stripe needs to communicate back to us when certain events happen inside of it.

First, whenever you look at a customer, at the bottom you'll see an events section, and basically the idea is whenever any little thing happens in Stripe, an event happens in the background. For example, when we add a new card, there is an event object whose type field is set to customer.source.created. Every action is an event, and there are many different types of events for the different types of things that happen in the system. In fact, in the API reference, event is actually and API object, which means we can fetch and read these events from Stripe. If you click types of events, you'd actually see a big, giant, long list of different events types that you can read to figure out what's going on.

Now let's talk about the most important event that we need to handle, and that's what happens when a user's account can't be charged, and so we need to cancel their subscription. Inside of your dashboard, go to account settings, and go to subscriptions. This is a really important page because it determines how Stripe handles credit cards that can't be charged, and these settings are pretty good. Basically it says that it's going to attempt to charge the card once, it'll charge it again 3 days later, 5 days later, 7 days later, and then finally it will cancel the subscription. You can tweak these if you want to, but the important thing is this, Stripe will automatically attempt to charge the card a few times, and then ultimately will cancel the subscription.

Now when that happens, we need Stripe to tell us that subscription was cancelled, and the way you're gong to do that is with a webhook. Basically we will give a URL to Stripe to our site, and then whenever certain events happen, it will actually send a request to our site, and deliver the event object that deals with that. A really good way to test webhooks is by using something called RequestBin. This is a cool little site where you can create a temporary RequestBin. I gives you a URL, and then if you make web requests to this URL, it will record those webrequests so we can see what they look like. Instead of our dashboard, let's an end point for that RequestBin. Put it in test mode, so that we receive the webhooks, the events from the test environment, then click select events. Instead of receiving all types of events, we're just going to select the ones we want. The most important one that we need, is customer.subscription.deleted. This is the event that happens when a subscription is cancelled for any reason, and the reason it's important for us, is this is the event that will happen when the user can't be charged and Stripe finally cancels their subscription.

Create that webhook, and then go to send test webhook, and let's actually send a test webhook for subscription deleted. It sent, and then go refresh your RequestBin. Perfect, so here it is. It's showing us the raw request body that was sent to us. Here's the cool thing, these events that happen are just objects in the API like anything else, but if you configure a webhook, then Stripe actually sends that event to you. Here we can see that there is an event being shown to us, here's the event's ID, obviously it's a fake ID.

Hello?

Hi dude.

How you doing?

Good, man.

Just wanted to let you know I got the toilet all hooked up upstairs.

Yes.

Everything's swept up in there, and all the tools are just off to in the room, so you can start using [inaudible 00:05:30]

Yes. Sounds good, we can move back upstairs.

[inaudible 00:05:35]

You can see that its type is customer.subscription.deleted. Here's the goal, when you're going to set up a route in a controller on our site, in Intel Stripe, to send these events to us, then if we see that we receive a ... This will allow us to perform us to perform different actions when events happen on Stripe, so let's do that next.
