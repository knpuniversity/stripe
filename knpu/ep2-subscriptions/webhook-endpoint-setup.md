# Webhook Endpoint Setup

Let's get right to work on our webhook endpoint. In the `App/Controller` directory,
create a new `WebhookController` class. Make it extend a `BaseController` class I
setup - it just has a few small shortcuts.

Now, create the endpoint with `public function stripeWebhookAction()`. Give an `@Route`
annotation set to `/webhooks/stripe` and a similar name. Make sure you have the
`Route` use statement.

Start simple: return a `new Response()` from the `HttpFoundation` component. That's
just enough to try out; go to `/webhooks/stripe` in your browser. It's alive!

## Decoding the Event

Thanks to RequestBin, we know more or less what the JSON body will look like. The
most important thing is this *event* `id`. Let's decode the JSON and grab this.

To do, add `$data = json_decode()`, but pause there. We need to pass this the *body*
of the Request. In Symfony, we get this by adding a `Request` argument - don't forget
the `use` statement! Then, use `$request->getContent()`. Also, pass `true` as the
second argument so that `json_decode` returns an associate of array.

Next, it *shouldn't* happen, but just in case, if `$data` is null, that means Stripe
sent us invalid JSON. Shame on you Stripe! Throw an exception in this case... and
make sure you spell `Exception` correctly!

Finally, get the `$eventId` from `$data['id']`.

## We Found the Event! Now, Fetch the Event?!

Ok, let's refocus on the next steps. Ultimately, I want to read these fields in the
event, find the Subscription in the database, and cancel it. But instead of reading
the JSON body directly, we're going to use Stripe's API to fetch the Event object
with this `$eventId`.

Wait, but won't that just return the *exact* same data we already have? Yes! We
do this not because we *need* to, but for security. If we read the request JSON
directly, it's possible that the request is coming from some external, mean person
instead of from Stripe. By fetching a fresh event from Stripe, it guarantees the
event data is legit.

Since we always make all API requests through the `StripeClient` class, open it up
and scroll to the bottom. Add a new public function called `findEvent()` with an
`$eventId` argument.

Inside, just return `\Stripe\Event::retrieve()` and pass it `$eventId`.

Back in the controller, add `$stripeEvent = $this->get('stripe_client')->findEvent($eventId)`.
If this were an invalid event id, Stripe would throw an exception.

Ok, let's handle some events!
