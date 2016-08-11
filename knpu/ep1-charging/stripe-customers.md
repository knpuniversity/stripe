# We <3 Creating Stripe Customers

Head back to `OrderController`. Create a `$user` variable set to `$this->getUser()`:

[[[ code('a06822faae') ]]]

This is the User object for *who* is currently logged in. I'll add some inline documentation
to show that.

When the user submits the payment form, there are *two* different scenarios. First,
`if (!$user->getStripeCustomerId())`, then this is a first-time buyer, and we need
to create a new Stripe Customer for them:

[[[ code('082b523d67') ]]]

To do that, go back to their API documentation and find [Create A Customer][create_customer].
Oh hey, it wrote the code for us again! Steal it! And paste it right inside the
`if` statement:

[[[ code('3bf2de83fc') ]]]

Customer has a lot of fields, but most are optional. Let's set `email` to
`$user->getEmail()`:

[[[ code('38849b82d4') ]]]

So we can easily look up a user in Stripe's dashboard later.

The *really* important field is `source`. This refers to the *payment source* -
so credit or debit card in our case - that you want to attach to the customer. Set
this to the `$token` variable:

[[[ code('a29958114e') ]]]

This is huge: it will attach that card to their account, and allow us - if we
want to - to charge them using that same card in the future.

Set this call to a new `$customer` variable: the  `create()` method returns a
`Stripe\Customer` object:

[[[ code('fe0a2dd6a9') ]]]

And we like that because *this* object has an `id` property.

To save that on our user record, say `$user->setStripeCustomerId($customer->id)`:

[[[ code('2654700fb0') ]]]

Then, I'll use Doctrine to run the UPDATE query to the database:

[[[ code('0645a657d3') ]]]

If you're not using Doctrine, just make sure to update the user record in the database
however you want.

## Fetching the Existing Customer Object

Now, add the `else`: this means the user *already* has a Stripe customer object.
Repeat customer! Instead of creating a new one, just fetch the customer with
`\Stripe\Customer::retrieve()` and pass it `$user->getStripeCustomerId()`:

[[[ code('972d5172b0') ]]]

Since this user is already in Stripe, we *might* eventually re-work our checkout
page so that they *don't* need to re-enter their credit card. But, we haven't done
that yet. And since they just submitted *fresh* card information, we should *update*
their account with that. After all, this might be a different card than what they used
the first time they ordered.

To do that, update the `source` field: set it to `$token`. To send that update to
Stripe, call `$customer->save()`:

[[[ code('d987943639') ]]]

So in *both* situations, the token will now be attached to the customer that's associated
with our user. Phew!

## Charging the User

The last thing we need to update is the Charge: instead of passing `source`, charge
the *customer* instead. Set `'customer' => $user->getStripeCustomerId()`:

[[[ code('0e00498024') ]]]

We're no long saying "Charge this credit card", we're saying "Charge this customer,
using whatever credit card they have on file".

Ok, time to try it out! Go back and reload this page. Run through the checkout with
our fake data and hit Pay. Hey, hey - no errors!

So go check your Stripe dashboard. Under Payments, you should see this new charge.
And if you click into it, it is now associated with a *customer*. Success! The customer
page shows even more information: the attached card, any past payments and eventually
subscriptions. This is one big step forward.

Copy the customer's id and query for that on our `fos_user` table. Yes, it *did*
update!

Since adding a customer went so well, let's talk about *invoices*.


[create_customer]: https://stripe.com/docs/api#create_customer
