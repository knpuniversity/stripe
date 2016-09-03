# Create those Subscription Plans

Yes! You're back! You survived [Stripe part 1][stripe_charging]. Ok... that's cool,
but now we have bigger fish to fry. So fasten your seat belts, put on a pot of coffee,
and get ready to sharpen your Stripe subscription skills.

## Wake up the Sheep

To become my best friend, just code along with me! If you coded with part one of
the tutorial, you'll need a fresh copy of the code: we made a few tweaks. Download
it from this page, unzip it, and then move into the `start/` directory.

That'll give you the same code I have here. Open the README file for the setup instructions.
The last step will be to open a terminal, move into the project directory, and run:

```bash
./bin/console server:run
```

to start the built-in web server. Now for the magic: pull up the site at
`http://localhost:8000`.

Oh yea, it's the Sheep Shear Club: thanks to [part 1][stripe_charging], it's already
possible to buy individual products. Now, click "Pricing". The *real* point of the site
is to get a *subscription* that'll send your awesome sheep awesome shearing accessories
monthly. After vigorous meetings with our investors, we've decided to offer 2 plans: the
Farmer Brent at $99/month and the New Zealander at $199/month. But other than this
fancy looking page, we haven't coded anything for this yet.

## Tell Stripe about the Plans

Our first step is to tell Stripe about these two plans. To do that, open your trusty
Stripe dashboard, make sure you're in the test environment, and find "Plans" on the
left. We're going to create the two plans in Stripe by hand. The ID can be any unique
string and we'll use this forever after to refer to the plan. Use, `farmer_brent_monthly`.
Then, fill in a name - that's less important and set the price as $99 per month.
Create that plan!

And yes, later, we'll add *yearly* versions of each plan... but one step at a time!
Repeat that whole process for the New Zealander: set its ID to `new_zealander_monthly`,
give it a name and set its price and interval. Perfect!

## Manage the Plans in Our Code

Now, head back to our code and open the `src/AppBundle/Subscription` directory.
I added two classes to help us stay organized. The first is called `SubscriptionHelper`:

[[[ code('32b705f81c') ]]]

And as you can see... it's not very helpful... yet. But pretty soon, it will keep
track of the two plans we just added. We'll use the second class - `SubscriptionPlan`:

[[[ code('875c5bf68e') ]]]

To hold the data for each plan: the plan ID, name and price. But we won't save these
to the database.

In `SubscriptionHelper`, setup the two plans. For the first, use `farmer_brent_monthly`
for the key, name it `Farmer Brent` and use 99 as the price:

[[[ code('474590dda9') ]]]

Copy that and repeat the same thing: `new_zealander_monthly`, `New Zealander` and 199:

[[[ code('4b23c21913') ]]]

Love it! The 2 plans live in Stripe *and* in our code. Time to make it possible
to add these to our cart, and then checkout.


[stripe_charging]: http://knpuniversity.com/screencast/stripe
