# Stripe Customers + Our Users

As you can see, there are a *lot* of *objects* that you can interact with in Stripe's
API. We've only talked about one: Charge. And it's enough to collect money.

But to create a *really* nice system, you need to talk about the Customer object.
Customers give you *three* big super-powers.

First, if the same customer comes back over-and-over again, right now, they need
to enter their credit card information *every* time. But with a Customer, you can *store*
cards in Stripe and charge them using that. Second, all the charges for a customer
will show up in one spot in Stripe's dashboard, instead of all over the place. And
third, customers are needed to process *subscription* payments - something we'll
talk about in the next Stripe course.

## Storing stripeUserId on the User Table

So here's the goal: instead of creating random Charge objects, let's create a Customer
object in Stripe and *then* charge that Customer. We also need to save the customer
id to our user table so that when that user comes back in the future, we'll know
that they are already a customer in Stripe.

In this project, the name of the user table is `fos_user`, and it contains *just*
some basic fields, like email, username and few others related to things like resetting
your password.

Let's add a new column called `stripeUserId`. To do that, open a class in AppBundle
called `User`. Create a new private property called `stripeCustomerId`:

[[[ code('e3e64814e0') ]]]

Above that, we're going to use annotations to say `@ORM\Column(type="string")` to create
a varchar column. Let's even add a unique index on this field and add `nullable=true`
to allow the field to be empty in the database:

[[[ code('cb17b496b5') ]]]

At the bottom of the class, I'll use the "Code"->"Generate" menu - or `Command`+`N` on a
Mac - to generate the getters and setters for the new property:

[[[ code('8882c44fc3') ]]]

Now that our PHP code is updated, we need to actually add the new column to our table.
Since this project uses Doctrine migrations, open a new tab and run:

```bash
php bin/console doctrine:migrations:diff
```

All that did was create a new file that contains the raw SQL needed to add this
new `stripe_customer_id` column:

[[[ code('e3719bc2b5') ]]]

To execute that, run another command:

```bash
php bin/console doctrine:migrations:migrate
```

Perfect! Back in SQL, you can see the fancy new column on the table.

We are ready to create Stripe customers.
