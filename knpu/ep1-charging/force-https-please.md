# Force HTTPS ... please

Guess what? You could deploy this code *right* now. Sure - we have a lot more to
talk about - like subscriptions & discounts - but the system is ready.

Oh, but there's just one thing that you *cannot* forget to do. And that's to *force*
`https` to be used on your checkout page.

Right now, there is *no* little lock icon in my browser - this page is *not* secure.
Of course it's not a problem now because I'm just coding locally.

But on production, different story. Even though you're not handling credit card
information, Stripe *does* submit that token to our server. If that submit happens
over a non-https connection, that's a security risk: there *could* be somebody
in the middle reading that token. Regardless of what they might or might not be able
to do with that, we need to avoid this.

There are *a lot* of ways to force HTTPs, but let me show you my *favorite* in Symfony.
In `OrderController`, right above `checkoutAction()`, this `@Route` annotation is
what defines the URL to this page. At the end of this, add a new option called
`schemes` set to two curly braces and a set of double-quotes with `https` inside:

[[[ code('e8984e9da9') ]]]

OK, go back and refresh! Cool! Symfony automatically redirects me to https. Life
is good.

## No HTTPS in dev Please!

Wait, life is *not* good. I *hate* needing to setup SSL certificates on my local
machine. I actually have one setup already, but other developers might not. That's
a huge pain for them... for no benefit.

Fortunately, there's a trick. Replace `https`, with `%secure_channel%`:

[[[ code('990cf4e39f') ]]]

This syntax is referencing a *parameter* in Symfony, so basically a configuration
variable. Open `parameters.yml`, add a new `secure_channel` parameter and set it
to `http`:

[[[ code('42c47e5113') ]]]

And as you know, if you add a key here, also add it to `parameters.yml.dist`:

[[[ code('5017c0cdc6') ]]]

Ok, head back to the homepage: `http://localhost:8000` and click to checkout. Hey!
We're back in `http`. When you deploy, change that setting to `https` and *boom*,
your checkout will be secure.

So there's your little trick for forcing https without being forced to hate your
life while coding.
