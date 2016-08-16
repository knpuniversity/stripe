# Give the User a Subscription (in our Database)

Ok, congrats on creating the subscription in Stripe! But now, the real work starts.
Even though Stripe stores everything about the Customer and Subscription, there are
always going to be a few things that we need to keep in *our* database, like whether
or not a user has an active subscription, and to which plan.

We're already doing this in one spot. The user table - which is modeled by this
`User` class - has a `stripeCustomerId` field. Stripe holds the customer data, but
we keep track of the customer id.

We need to do the same thing for the Stripe Subscription. It also has an id, so if
we can associate that with the User, we can quickly fetch a User's Subscription info.

## The subscription Table

There are a few good ways to store this, but I chose to create a brand new `subscription`
table. I'll open up a new tab in my terminal and use mysql to login to the database.
The `fos_user` table is the user table and I also added a new table: `subscription`.

There are a few important things. First, the `subscription` table has a relationship
back to the user table via a `user_id` foreign key column. Second, the `subscription`
table stores more than *just* the Stripe subscription id, it will also hold the
`planId` so we can instantly know which plan they have. It also holds a few other
things that will help us manage cancellations.

So our mission is clear: when a user buys a subscription, we need to create a new
row in this table, associate it with the user, and set its information properly.
This will ultimately let *us* quickly determine if a user has an active subscription
and to which plan.

## Prepping the Account Page

And that's cool because we'll be able to fill in this fancy account page I created!
All this info: yep, it's just hardcoded right now. Open up the template for this page
at `app/Resources/views/profile/account.html.twig`. Instead of "None", add an `if`
statement: `if app.user` - that's the currently-logged-in user `app.user.subscription`,
then we know they have a Subscription. Add a label that says "Active". If they don't
have a subscription, say "None".

If you refresh now... it says None. We actually *do* have a Subscription in Stripe
from a minute ago, but our database doesn't know about it. That's what we need to
fix.

## Updating the Database

Since we need to update the database during checkout, go back to `OrderController`
and find the `chargeCustomer()` method that holds all the magic. 

But instead of putting the code to update the database right here, let's add it
to `SubscriptionHelper`: this class will do all the work related to subscriptions.
Add a new method at the bottom called `public function addSubscriptionToUser()`
with two arguments: a `\Stripe\Subscription` object that was just created and the
`User` that the Subscription belongs to.

Inside, start with `$subscription = $user->getSubscription()`. The user may *already*
have a row in the `subscription` table for an expired subscription. If they do,
we'll just update that row instead of creating a second row.

But if they *don't* have a previous subscription, let's create a new one:
`$subscription = new Subscription()`. Then, `$subscription->setUser($user)`.

The only other thing we need to is update the fields on the `Subscription` object:
`stripeSubscriptionId` and `stripePlanId`. To keep things clean, open `Subscription`
and add a new method on teh bottom: `public function activateSubscription()` with
two arguments: the `$stripePlanId` and `$stripeSubscriptionId`. Set each of these
onto the corresponding properties. Also add `$this->endsAt = null`. We'll talk more
about that later, but this field will help know if a subscription has been cancelled.

Back in `SubscriptionHelper`, add `$subscription->activateSubscription()`. We need
to pass this the `stripePlanId` and the `stripeSubscriptionId`. But remember! We
have this fancy `\Stripe\Subscription` object! In the API docs, you can see its
fields, like `id` and `plan` with its *own* `id` sub-property.

Cool! Pass the method `$stripeSubscription->plan->id` and `$stripeSubscription->id`.

Booya!

Ok, time to save this to the database! Since we're using Doctrine in Symfony, we
need the EntityManager object to do this. I'll use dependency injection: add an
`EntityManager` argument to the `__construct()` method and set it on a new `$em`
property. For Symfony users, this service is using auto-wiring. So because I type-hinted
this with `EntityManager`, Symfony will automatically know to pass that as an argument.

Finally, at the bottom, add `$this->em->persist($subscription)` and
`$this->em->flush($subscription)` to *just* save the Subscription.

With all that setup, go back to `OrderController` to call this method. To do that,
we need the `\Stripe\Subscription` object. Fortunately, the `createSubscription`
method returns this. So add `$stripeSubscription = ` in front of that line. Then,
add `$this->get('subscription_helper')->addSubscriptionToUser()` passing it
`$stripeSubscription` and the currently-logged-in `$user`.

Phew! That may have seemed like a lot, but ultimately, this line just makes sure
that there is a new subscription row in our table that's associated with this user
and up-to-date with the user's subscription and plan id. 

Now, let's go try it out. Add a new subscription to your cart, fill out the fake
credit card information and hit checkout. No errors! And on the account page, hey!
The subscription is active! Our database is up-to-date.
