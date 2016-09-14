# The Update Card Form!

Eventually, our customers will need to *update* the credit card that we have stored.
And on surface, this is pretty easy: the card is just a property on the Stripe customer
called `source`. And basically, we need to *nearly* duplicate the checkout process:
show the user a card form, exchange their card info for a Stripe token, submit that
token, and then save it on the user as their *new* card.

Ok, let's rock! Step 1: add an "Update Card" button to the account page and make it
re-use the same card form we already built for checkout. Because, ya know, reusing
code is awesome!

## Re-using the Card Form

In `account.html.twig`, find the credit card section. Add a button, give it some
classes for styling *and* a `js-open-credit-card-form` class:

[[[ code('88bfdbaa65') ]]]

In a second, we'll attach some JavaScript to this.

Next, find the `col-xs-6`:

[[[ code('dd035f59ee') ]]]

This is the *right* side of the page, and I've kept it empty until now *just* so we can
show the form here. Add a div with a JS class so we can hide/show this element:
`js-update-card-wrapper`. Make it `display: none;` by default:

[[[ code('240347d78d') ]]]

Inside, add a cute header:

[[[ code('47ec54429e') ]]]

Ok, I want to re-use the entire checkout form right here. Fortunately, we already
isolated that into its own template: `_cardForm.html.twig`. Yay!

In `account.html.twig`, use the Twig `include()` function to bring that in:

[[[ code('50c5459f28') ]]]

## Hide/Show the Form

Ok! Let's hide/show this form whenever the user clicks the "Update Card" button.
At the top of the file, override the block called `javascripts`, and call `endblock`.
Inside, call the `parent()` function:

[[[ code('b3e80daedc') ]]]

In this project, any JS we put here will be included on the page.

Add a `script` tag and a very simple `document.ready()` block:

[[[ code('96bdb67d39') ]]]

Inside of that, find the `.js-open-credit-card-form` element and on `click`, create
a callback function. Start with the normal `e.preventDefault()`:

[[[ code('9473343840') ]]]

Now, find the other wrapper element, which is `js-update-card-wrapper`. Call `slideToggle()`
on that to show/hide it:

[[[ code('351486cc25') ]]]

So, fairly easy stuff.

Well, maybe we should see if it works first. Refresh! Ah, it doesn't! Huge error:

> Variable "error" does not exist in `_cardForm.html.twig` at line 56

Hmm, checkout that template:

[[[ code('c14d830b7b') ]]]

Ah yes, on the checkout page, after we submit, if there was an error, we set this variable
and render it here. For now, we don't have any errors. In `account.html.twig`, we could pass
an `error` variable to the `include()` call. But, we could also do it in `ProfileController::accountAction()`.
Add `error` set to `null`:

[[[ code('24a8f91e1f') ]]]

Refresh and click "Update Card". We are in business!

## Fixing the Button Text

But, this has two cosmetic problems. First, the button says "Checkout"! That's a little
scary, and misleading. Let's change it!

In `_cardForm.html.twig`, replace "Checkout" with a new variable called
`buttonText|default('Checkout')`:

[[[ code('49d4bebeea') ]]]

So, if the variable is *not* defined, it'll print "Checkout".

Now, override that in `account.html.twig`. Give `include()` a second argument:
an array of extra variables. Pass `buttonText` as `Update Card`:

[[[ code('9b979fdb28') ]]]

Refresh! Cool, button problem solved.

## Sharing Card JavaScript

The second problem is pretty obvious if you fill out the card number field on checkout,
and then compare it with the profile page! On the checkout page, we included a lot
of JavaScript that does cool stuff like format this field. And, *much* more importantly,
the JS is also responsible for sending the credit card information to Stripe, fetching
the token, putting it in the form, and submitting it. We *definitely* still need
that.

Ok, how can we reuse the JavaScript? In `checkout.html.twig`, we just inlined all
of our JS right in the template. That's not great, but since this isn't a course
about JavaScript, let's solve this as easily as possible. Copy all of the JS and
create a new template called `_creditCardFormJavaScript.html.twig` inside the `order/`
directory. Paste this there:

[[[ code('784d4b83ca') ]]]

Now, in checkout, include that template!

[[[ code('ca690c0af3') ]]]

Copy that and include the same thing in `account.html.twig` at the top of the `javascripts`
block:

[[[ code('a4e2440a2b') ]]]

Ok, refresh and hope for the best! Ah, another missing variable: `stripe_public_key`:

> Variable "stripe_public_key" does not exist in `_creditCardFormJavaScript.html.twig` at line 5

We're printing this in the middle of our JS:

[[[ code('9cb5efbb35') ]]]

And the variable comes from `OrderController`. Copy that line, open `ProfileController`,
and paste it there:

[[[ code('2863d6e14d') ]]]

Now the page works! *And* - at the very least - the JS formatting is rocking.

Frontend stuff is done: let's submit this and update the user's card in Stripe.
