# Hide Those Private Keys

We already know that our Stripe account has *two* environments, and each has its
*own* two keys. This means that when we deploy, we'll need to update our code to
use these *Live* keys, instead of the ones from the Test environment.

Well... that's going to be a bummer: the public key is hard-coded right in
the middle of my template:

[[[ code('34e14ca59f') ]]]

And the private one is stuck in the center of a controller:

[[[ code('6c866048fa') ]]]

If you *love* editing random files whenever you deploy, then this is perfect!
Have fun!

But for the rest of us, we need to move these keys to a central configuration file
so they're easy to update on deploy. We also need to make sure that we don't commit
this private key to our Git repository... ya know... because it's private - even
though I keep showing you mine.

## Quick! To a Configuration File!

How you do this next step will vary for different frameworks, but is philosophically
always the same. In Symfony, we're going to move our keys to a special
`parameters.yml` file, because our project is setup to *not* commit this to Git.

Add a `stripe_secret_key` config and set its value to the key from the controller:

[[[ code('02cb508d2d') ]]]

Then add `stripe_public_key` and set that to the one from the template:

[[[ code('b96026c1bb') ]]]

In Symfony, we also maintain this other file - `parameters.yml.dist` - as a *template*
for the original, uncommitted file. This one *is* committed to the repository.
Add the keys here too, but give them fake values.

## Using the Parameters

Now that these are isolated in `parameters.yml`, we can take them out of our code.
In the controller, add `$this->getParameter('stripe_secret_key')`:

[[[ code('d9ede9813b') ]]]

Next, pass a new `stripe_public_key` variable to the template set to `$this->getParameter('stripe_public_key')`:

[[[ code('d49dedf941') ]]]

Finally, in the template - render that new variable:

[[[ code('fd86dea8ba') ]]]

Make sure we didn't break anything by finding a product and adding it to the cart.
The fact that this "Pay with Card" shows up means things are probably OK.

This was a small step, but don't mess it up! If that secret key becomes *not* so
secret, sheep-zombies will attack.
