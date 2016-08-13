# Building the Custom Checkout Form

Earlier, we were *rushing* to get the site up and the sheep shopping. That's why
we used Stripe's pre-built embedded form. And this is *completely* fine if you like
it. But I want to build a custom form that looks like native on our site.

To do that, go back to the Stripe docs. Instead of embedded form, click "Custom Form".
Using a custom form is *very* similar: we still send the credit card information
to Stripe, and Stripe will still give us back a token. The difference is that *we*
are responsible for building the HTML form.

## Setting up the Stripe JavaScript

To help communicate with Stripe, we need some JavaScript. Copy the first JavaScript
code and then find the `checkout.html.twig` template. At the top, override
`{% block javascripts %}` and then call the `{{ parent() }}` function. Paste the
script tag below:

[[[ code('b2dedeea0a') ]]]

This is just the Twig way of adding some new JavaScript to our page. The base layout
*also* has a `javascripts` block and jQuery is already included:

[[[ code('8edc31a7ce') ]]]

Next, we need to tell the JavaScript about our publishable key. Copy that code from
the docs and add it in the block:

[[[ code('62acd66c46') ]]]

We already know from our original code that we have a variable called `stripe_public_key`.
Inside of the JavaScript quotes, print `stripe_public_key`:

[[[ code('a920a87639') ]]]

Awesome!

## Rendering the HTML Form

With that done, it's time to build the form itself. And surprise! I already built
us a basic HTML form. Delete the old, embedded form code. Replace it with
`{{ include('order/_cardForm.html.twig') }}`:

[[[ code('5af7548fcd') ]]]

This will read this *other* template file I prepared: `_cardForm.html.twig`:

[[[ code('0744de8f61') ]]]

As you can see, this is a normal HTML form. Its `method` is post and its `action`
is still empty so that it will submit right back to the same URL and controller.
Then, there's just a bunch of fields that are rendered to look good with Bootstrap.

Let's see how awesome my design skills are: go back and refresh. Hey, it looks pretty
good! Probably because someone styled this for me.

## Do NOT Submit Card Data to your Server

There are a few *really* important things about this form. Most importantly, notice
that the input fields have *no* `name` attribute. This is crucial. Eventually, we
*will* submit this form, but we do *not* want to submit these fields because we do
*not* want credit card information passing through our server. Because these fields
do *not* have a `name` attribute, they are *not* submitted.

So instead of `name`, Stripe asks you to use a `data-stripe` attribute. This tells
Stripe *which* data this field holds. Since this is the cardholder name, we have
`data-stripe="name"`. Then below, `data-stripe="number"`, `data-stripe="exp"` and
so-on.

But I'm not choosing these values at random. Inside Stripe's documentation, it tells
you which `data-stripe` value to use for each piece. If you follow the rules, Stripe's
JavaScript will do all the work of collecting this data and sending it to Stripe.

OK, let's hook up that JavaScript logic next.
