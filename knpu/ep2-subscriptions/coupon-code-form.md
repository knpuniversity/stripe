# Coupons! Adding the Form

Let's talk about something fun: coupon codes. When a user checks out, I want them
to be able to add a coupon code. Fortunately Stripe *totally* supports this, and
that makes our job easier.

In fact, in the Stripe dashboard, there's a Coupon section. Create a new coupon:
you can choose either a percent off or an amount off. To make things simple, I'm
*only* going to support coupons that are for a specific *amount* off.

Create one that saves us `$50`. Oh, and in case this is used on an order with a
subscription, you can set the duration to once, multi-month or forever.

Then, give the code a creative, unique code: like CHEAP_SHEEP. Oh, and the coupon
codes *are* case sensitive.

***TIP
I'm choosing to create my coupons through Stripe's admin interface. If you want
an admin section that does this on *your* site, that's totally possible! You can
create new Coupons through Stripe's API.
***

## Adding a Coupon During Checkout

Back on our site, before we get into any Stripe integration, we need to add a spot
for adding coupons during checkout.

Open the template: `order/checkout.html.twig`. Below the cart table, add a button,
give it some styling classes and a `js-show-code-form` class. Say, "I have a coupon code":

[[[ code('7dcf927f98') ]]]

Instead of adding this form by hand, open your `tutorial/` directory: this is
included in the code download. Open `coupon-form.twig`, copy its code, then paste
it below the button:

[[[ code('d3f8301cab') ]]]

This new `div` is hidden by default and has a `js-code-form` class that we'll use soon
via JavaScript. And, it has just one field named `code`. 

Copy the `js-show-code-form` class and scroll up to the `javascripts` block. Add
a new `document.ready()` function:

[[[ code('62567e537d') ]]]

Inside, find the `.js-show-code-form` element and on `click`, add a callback. Start
with our favorite `e.preventDefault()`:

[[[ code('a7977f8154') ]]]

Then, scroll down to the form, copy the `js-code-form` class, use jQuery to select this,
and... drumroll... show it!

[[[ code('7ce35e3a8a') ]]]

Cool! Now when you refresh, we have a new link that shows the form. 

## Submitting the Coupon Form

So let's move to phase two: when we hit "Add", this should submit to a new endpoint
that validates the code in Stripe and attaches it to our user's cart.

To create the new endpoint, open `OrderController`. Near the bottom add a new public
function `addCouponAction()` with `@Route("/checkout/coupon")`. Name it `order_add_coupon`.
And to be extra-hipster, add `@Method("POST")` to guarantee that you can only POST
to this:

[[[ code('a4a75f7593') ]]]

Cool! Copy the route name, then find the coupon form in the checkout template. Update
the form's `action`: add `path()` and then paste the route name:

[[[ code('e720ad0893') ]]]

Next, we'll read the submitted code and check with Stripe to make sure it's real,
and not just someone trying to guess clever coupon codes. Come on, we've all tried
it before.
