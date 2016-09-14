# Saving the Updated Card Details

When the user fills out this form, our JavaScript sends that info to Stripe, Stripe
then sends back a token, we add the token as a hidden field in the form and then
submit it. Both the checkout form and update card form will work like this.  But,
we need to submit the update card form to a new endpoint, that'll *update* the card,
but *not* charge the user.

## Submitting the Form to the new Endpoint

Open `ProfileController` and add a new endpoint: `public function updateCreditCardAction()`.
Give it the URL `/profile/card/update` and a fancy name: `account_update_credit_card`.
Add the `@Method("POST")` to be extra cool:

[[[ code('26a2af9112') ]]]

With this in place, we need to update the form to submit here. Check out `_cardForm.html.twig`.
Hmm, the `action` attribute is *empty*:

[[[ code('f5c7ae15e9') ]]]

That's because we want the checkout form to submit right back to `/checkout`. But this won't
work for the update card form: it should submit to a different URL.

Instead, render a new variable called `formAction` and pipe that to the `default`
filter with empty quotes:

[[[ code('2457a0c555') ]]]

Now we can override this! In `account.html.twig`, add another variable to the include: `formAction`
set to `path()` and the new route name:

[[[ code('5fe671f639') ]]]

Refresh and check out the source. Ok, the form action is ready!

## Saving the Credit Card

Let's get to work in `ProfileController`! But actually... this will be very similar
to our checkout logic, so let's go steal code! Copy the line that fetches the
`stripeToken` POST parameter and then head back to `ProfileController`. Make
sure you have a `Request` argument and the `Request` use statement:

[[[ code('dc5cc2d872') ]]]

Then, first, paste that line to fetch the token. Second, fetch the current user
object with `$user = $this->getUser()`. And third, we need to make an API request
to stripe that updates the Customer and attaches the token as their new card.
That means we'll be using the `StripeClient`. Fetch it first with
`$stripeClient = $this->get('stripe_client')`:

[[[ code('2dad87123f') ]]]

Here's the awesome part: open `StripeClient`. We *already* have a method called
`updateCustomerCard()`:

[[[ code('447ceb4d5a') ]]]

We pass the `User` object and the submitted payment token and *it* sets it on
the `Customer` and saves.

Victory for code organization! In the controller, just say `$stripeClient->updateCustomerCard()`
and pass it `$user` and `$token`:

[[[ code('8c6c37557b') ]]]

That takes care of things inside Stripe.

## Updating cardLast4 and cardBrand

Now, what about our database? Do we store any information about the credit card?
Actually, we do! In the `User` class, we store `cardLast4` and `cardBrand`. With
the new card, this stuff probably changed!

But wait, we've got this handled too guys! Open `SubscriptionHelper` and check out
the handy `updateCardDetails()` method:

[[[ code('eedf437969') ]]]

Just pass it the `User` and `\Stripe\Customer` and it'll make sure those fields are set.

In `ProfileController`, call this: `$this->get('subscription_helper')->updateCardDetails()`
passing `$user` and `$stripeCustomer`... which doesn't exist yet. Fortunately,
`updateCustomerCard()` returns that, so create that variable on that line:

[[[ code('d8e429f1ab') ]]]

That's it! Add a success message so that everyone feels happy and joyful, and redirect
back to the profile page:

[[[ code('cc9a6f0522') ]]]

Time to try it! Refresh and put in the fake credit card info. But use a different
expiration: 10/25. Hit "Update Card". Ok, it looks like it worked. Refresh the
Customer page in Stripe. The expiration *was* 10/20 and now it's 10/25. Card update
successful!

But, we still need to handle one more case: when the card update fails.
