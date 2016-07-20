# Centralize your Stripe Code

Stripe's API is really organized. Our code that *talks* to it is getting a little
crazy, unless you like long, procedural code that you can't re-use. Please tell me
that's not the case.

Let's get this organized! At the very least, we should do this because eventually
we're going to need to re-use some of this logic - particularly with subscriptions.

Here's the goal of the next few minutes: move each *thing* we're doing in the controller
into a set of nice, re-usable functions. To do that, inside AppBundle, create a new
class called `StripeClient`. Make sure this has the `AppBundle` namespace. We're
going to fill this with functions that work with Stripe, like `createCustomer()`
or `updateCustomerCard()`.

## Moving createCustomer()

In the controller, the first thing we do is create a Customer. In `StripeClient`,
add a new `createCustomer()` method that will accept the User object which should
be associated with the customer, and the `$paymentToken` that was just submitted.

Copy the logic from the controller and paste it here. Update `$token` to `$paymentToken`.
Then, return the `$customer` at the bottom, just in case we need it. You'll see me
do with this most functions in this class.

The only problem is with the entity manager - the code used to update the user record
in the database. The way we fix this is a bit specific to Symfony. First, add a
`public function __construct()` with an `EntityManager $em` argument. Set this
on a new `$em` property.

Down below, just say `$em = $this->em`.

## Registering the Service

To use the new function in our controller, we need to register it as a service. Open
up `app/config/services.yml`. Add a service called `stripe_client`, set its `class`
key to `AppBundle\StripeClient` and set `autowire` to true. With that, Symfony will
guess the constructor arguments to the object.

If you're not coding in Symfony, that's ok! Do whatever you need to in order to
have a set of re-usable functions for interacting with Stripe.

In the controller, clear out all the code in the `if` statement, and before it, add
a new variable called `$stripeClient` set to `$this->get('stripe_client')`. This
will be an instance of that `StripeClient` class.

In this `if`, call `$stripeClient->createCustomer()` and pass it the `$user` object
and the `$token`. Done.

## Moving updateCustomerCard()

Let's keep going!

The *second* piece of logic is responsible for updating the card on an existing
customer. In `StripeClient`, add a `public function updateCustomerCard()` with a
`User $user` whose related Customer should be updated, and the new `$paymentToken`.

In `OrderController`, call this with `$stripeClient->updateCustomerCard()` passing
it `$user` and `$token`. Now the `StripeClient` class is getting dangerous!

## Always setting the API Key

But, there's one small problem. This *will* work now, but look at the `setApiKey()`
method call that's above everything. We *must* call this before we make any API calls
to Stripe. So, if we tried to use the `StripeClient` somewhere *else* in our code,
but we forgot to call this line, we would have *big* problems.

Instead, I want to *guarantee* that if somebody calls a method on `StripeClient`,
`setApiKey()` will always be called first. To do that, copy that line, delete it
and move it into StripeClient's `__construct()` method.

Symfony user's will know that the `getParameter()` method won't work here. To fix
that, add a new *first* constructor argument called `$secretKey`. Then, use that.

To tell Symfony to pass this, go back to `services.yml` and add an `arguments` key
with one entry: `%stripe_secret_key%`. Thanks to auto-wiring, Symfony will pass the
`stripe_secret_key` parameter as the first argument, but then autowire the second,
`EntityManager` argument.

The end-result is this: when our `StripeClient` object is created, the API key is
set immediately.

## Moving Invoice Logic

Ok, the hard stuff is behind us: let's move the last two pieces of logic: creating
an `InvoiceItem` and creating an `Invoice`. In `StripeClient`, add
`public function createInvoiceItem()` with an `$amount` argument, the `$user` to
attach it to and a `$description`. Copy that code from our controller, remove it,
and paste it here. Update `amount` to use `$amount` and `description` to use `$description`.
Add a `return` statement just in case.

In `OrderController`, call this `$stripeClient->createInvoiceItem()` passing it
`$product->getPrice() * 100`, `$user` and `$product->getDescription()`.

Perfect! For the last piece, add a new `public function createInvoice()` with a
`$user` whose customer we should invoice and a `$payImmediately` argument that defaults
to `true`. Who knows, there might be some time in the future when we *don't* want
to pay an invoice immediately.

You know the drill: copy the invoice code from the controller, remove it and paste
it into `StripeClient`. Wrap the `pay()` method inside `if ($payImmediately)`. Finally,
return the `$invoice`.

Call that in the controller: `$stripeClient->createInvoice()` passing it `$user`
and `true` to pay immediately.

Phew! This was a giant step sideways - but not only is our code more re-usable, it
just makes a lot more sense when you read it!

Double-check to make sure it works. Add something to your cart. Check-out. Yes!
No error! The system still works and this `StripeClient` is really, really sweet.
