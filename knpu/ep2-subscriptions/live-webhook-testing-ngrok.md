# Live Webhook Testing with Ngrok

If the automated tests aren't your thing, or if you just *really* want to see your
webhooks in action... in real life, you've got two options.

## Testing on Beta

The best way, honestly, is to test your webhooks for real, out in the wild, on a
beta server. Yep, I simply mean: deploy your code to beta, setup a webhook to point
to your beta server, and then start creating test subscriptions through your site.

The only problem is that some webhooks are harder to simulate than others. Want
to simulate `customer.subscription.deleted`? No problem: create a subscription on
your site, then log into Stripe and cancel it. Then watch the webhook magic happen.

But faking the `invoice.payment_failed` webhook because your card is being declined
on subscription renewal... well... that's a bit harder. You *could* wait 1 month
and see what happens... if you have a lot of time. Or, you could temporarily create
a *new* subscription plan and set its interval to one day.

Then, you can test a different situation each day: like make sure the subscription
renewal webhook works, then update your card to one that will fail, wait one more
day, and see how your system handles the `invoice.payment_failed` webhook.

It's not perfect, it's slow, but it's totally real-world.

## Using your Local Machine with Ngrok

The second option is to point a webhook at your *local* development machine. But
wait! That's not possible: our local machine is not accessible by the internet.

Well... that doesn't *have* to be true. By using a cool utility called [Ngrok][ngrok],
you can temporarily tunnel a *public* URL to your computer.

Let's try it! Since I already have ngrok installed, I can use it from any directory
on my system:

```bash
ngrok http 8000
```

That will expose port 8000 - the one we're serving our site on - to the web via
this cool public URL.

Copy that and paste it into your browser! Ah, that's a little security check that
prevents any non-local users from accessing our `dev` environment. Just for now,
go into the `web/` directory, open `app_dev.php`, and comment-out the two security
lines:

[[[ code('012d540dfe') ]]]

Refresh again! Hey, it's our site! Via a public URL.

## Using the Ngrok URL as a Webhook Endpoint

Now we're super dangerous! In your Stripe Webhook configuration, add an endpoint.
Paste the URL and put this in the "Test" environment. For now, *just* receive the
`customer.subscription.deleted` event. Create the endpoint!

Oh, wait, make sure the endpoint URL ends in `/webhooks/stripe`. That's better!

In our app, we *already* have an active subscription. So, in Stripe, click "Customers"
and open our one Customer. Find the top subscription and... cancel it! Immediately!

That *should* cause a webhook to be sent to our local machine. So, moment of truth:
refresh the account page! Oh no! The subscription doesn't look canceled!

Hmm, go back to Stripe. At the bottom of the Customer page, you can see all the events
for this Customer. Or, another way to look at this is by clicking "Events & webhooks"
on the left. Ah! And we *can* see the `customer.subscription.deleted` event! And
at the bottom, it shows that the webhook to our ngrok URL *was* successful.

So, refresh the account page again. Ah, *now* the subscription is canceled. We
were just *too* fast the first time.

So, choose your favorite method of testing these crazy webhook things, and make sure
they are bug-free.


[ngrok]: https://ngrok.com/
