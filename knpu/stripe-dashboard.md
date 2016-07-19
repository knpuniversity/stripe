# Your Stripe Dashboard

So you want to handle payment on the web? You *brave*, foolish soul :).

Nah, it's fine - these days, handling credit card payments is a *blast*.
Especially with Stripe - an awesome service we've used on KnpU for years.

But let's be real: you're dealing with people's money, so don't muck it up! If
you screw up or do something insecure, there could be real consequences. You
will *at least* have an angry customer. So I guess this tutorial is all about
accepting money and having *happy* customers.

So let's build a real-life, robust payment system so that when things go wrong
- because they will - we fail gracefully, avoid surprises and make happy
customers.

## Code Along with Me. Do it!

As always, I beg you, I *implore* you, to code along with me! To do that,
download the course code on this page, unzip it, and move into the `start/`
directory. That will give you the same code I have here.

This is a Symfony project, but we'll avoid going too deep into that stuff
because I want to focus on Stripe. Inside, open the `README.md` file and follow
the setup details to get the project running. The last step is to open your
favorite console app, move into the directory, and run:

```bash
php bin/console server:run
```

to start up the built-in web server.

## Our Great Idea: Sheep Shear Club

But before you start collecting any money, you need to come up with that next,
*huge* idea. And here at KnpUniversity, we're convinced we've uncovered the next
tech unicorn.

Ready to find out what it is? Open your browser, and go to:

    http://localhost:8000

That's right: welcome to The Sheep Shear Club, your one-stop shop for artisanal
shearing accessories for the most dapper sheep. Purchase cutting-edge individual
products - like one of our After-Shear scents - or have products delivered directly
to your farm with a monthly subscription.

Gosh, it's *shear* luck that we got to this idea first. Once we finish coding the
checkout, our competition will be feeling *sheepish*.

But the site is pretty simple: we have a login page - the password is `breakingbaad`.
After you login, you can add items to your cart and they'll show up on the checkout
page. But notice, there is *no* checkout form yet. That's our job.

## Getting to Know your Stripe Dashboard

The first step towards that is to sign up with a fancy new account on Stripe. Once
you're in, you'll see this: your new e-commerce best friend: the Stripe dashboard.

There is *a lot* here, but right now I want you to notice that there are two environments:
"test" and "live". These are like two totally separate databases full of orders,
customers and more, and you can just switch between them to see your data.

Also, once you login, when you read the Stripe documentation, it will actually pre-fill
*your* account's API keys into code examples.

Let's use those docs to put in our checkout form!
