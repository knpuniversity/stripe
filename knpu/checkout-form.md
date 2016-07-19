# Embedded Checkout Form

So you want to handle payment on the web? You *brave* soul :).

Nah, it's fine - these days, handling credit card payments is a *blast*. Especially
with Stripe - an awesome service we've used for years.

But let's be real: you're dealing with people's money, so don't muck it up! If you
screw up or handle data insecurely, there might be real consequences. And you will
*at least* have an angry customer. So I guess this tutorial is all about accepting
money and having *happy* customers.

So let's build a real-life, robust payment system so that when things go wrong - because
they will - we can fail gracefully and avoid surprises.

## Code Along with Me. Do it!

As always, I beg you, I *implore* you to code along with me!
To do that, download the course code on this page, unzip it, and move into the `start/`
directory. That will give you the same code I have here.

This is a Symfony project, but we'll avoid going too deep into that stuff because
I want to focus mostly on Stripe. Inside, open the `README.md` and follow the setup
instructions to get the project running. The last step is to open your favorite console
app, move into the directory, and run:

```bash
php bin/console server:run
```

to start up the built-in web server.

--> HERE so let's check out our site.

Before we do, let me just tell you, here at Camp University, we really thought hard about what site we could create to be the next big thing. We started thinking about those services like Dollar Shave Club, which are really, really successful, and I think we came up with the next huge subscription product. We're calling it The Sheep Shear Club. That's right, sheep from everywhere can come to our site, get a subscription, and start looking dapper.

Now to start out, we're actually just going to sell individual products, and then eventually as we get further, we have a subscription service where we're going to do all the craziness to handle subscriptions. Just so you know, there's not a lot on this site now, so there's not a lot pre-built for you. We have a log in page. Password is breakingbaad. If you log in there, then you can start adding things to your cart, and then they show up in your cart here, but notice we are missing a checkout form on our page. That's what we need to do.

To do that, first, sign up for your brand new Stripe account, which is really, really simple. I'm already signed up, so I'm going to go into my Stripe dashboard. There's a lot to look at inside the Stripe dashboard, but the first thing I want you to notice is that there is a test and live mode. We're in the test mode, so we have an entire sandbox full of users and orders and invoices, that are completely separate from once we actually from once we actually push this live. That's really cool.

Also, once you log into dashboard, when you read the Stripe documentation, it will actually pre-fill in some of your API keys because it knows who you're logged in as, so make sure you're logged in before we keep going.

Let's click on the documentation link on top, and lets add a checkout form. To start, go to embedded form. There's going to be two ways to create the checkout form. There is a built in one with Stripe, which just looks like that, and is really easy to set up, or you can make your own custom checkout form, which is what we're going to do in a little while. This is so easy, I want to start here.

To do that, copy this form down here, then go for an application and we're going to open app, resources, view, order, checkout.html.twig. This is the template that is controlling this checkout page. Down at the bottom, I already have a slot open on that page, for our checkout form. I'll paste the form [inaudible 00:04:39] right there, and you can already see as I promised, this pk_test is actually a public key that is associated with our account.

In Stripe, if you go up to your account, account settings on top, and you go to API keys, you're going to see ... You're going to see your secret and publishable key for your test environment, and also your secret and publishable key for your live environment, and I will be obviously changing my live environment right after I finish recording this. It's pretty cool because it's already taken this publishable key and it stuck that right there for it.

Without doing anymore work, let's go back, refresh the page, and there is our beginning checkout form. Obviously it's 9.99 so it needs some tweaks. To customize that, you just need to fill in these attributes. The most important ones obviously amount, so I would do {{cart.total, and cart is in object I pass into my template that keeps track of what my total is. The important part is times 100. Whenever you talk about dollar amounts with Stripe, you want to talk about in terms of cents, so if you need to charge a user 5 dollars, you need to tell Stripe you're charging them 500 cents. Our cart total is in terms of dollars, so that converts it to cents.

Then fill in anything else that's important to you, for example, data image. I'm going to change that to point at our logo.

Let's go back and refresh, we're expecting 62 dollars. We pay, there's 62 dollars, and lets just fill it out. To test carts ... Because we're using our public key for test mode, you can see the test mode up here, and that means we can use test cards from Stripe. The most common that you need to know about is all 42, 4242 4242 4242 4242. That's a functional Visa card in the test mode, and you can put in any valid dates after that, and then hit pay, and watch it.

It worked. Except, what just happened? Well, a really important thing happened. You see, the key thing with Strip is that the credit card information is never ever submitted to our servers, and that's important because we do not want the credit card information submitted to our servers. That puts huge security liability on us. When you bring up this payment and hit pay, that sends the credit card information to Stripe's server, and then using AJAX, if the card is valid, it sends us back a token, and actually puts that token inside of our form field and submits the form back to our server. The only thing that's submitted to our server is this token. That's really cool because we can capture that token and then ask Strip to charge that credit card for us.

First, let's see that token to prove it's working. Open up SOC at bundle controller, order controller, and go down to checkout actions. This is the controller that is rendering the template that we've been working in. Because there's form action equals blank string, it's actually submitting right back to this same URL. If the method is post, I want to see if that token field is being submitted.

Get the request argument by type hinting the request object as an argument, and make sure that that added the you statement that you needed right there. Then down below it will say if request error is method post, and we know that form was just submitted and we're going to dump request arrow. Get. The Stripe token, because if you read the documentation, that's the hidden field that it adds to your form before it submits.

Let's try it out, refresh the page. [inaudible 00:09:36] credit card again. Put in a fake credit card number. Hit pay, and it refreshes but thanks to our dump, down here in the web [inaudible 00:09:55] toolbar, we have this token _18 blah, blah, blah things. Our checkout is already mostly working.

Second, we're going to send this back to Stripe and tell Stripe to actually charge the credit card. Before we do that, one of the best parts of Stripe, is the fact that, are it's debugging tools. Even though we can already go into the logs area down here, and we can actually see all of the web requests that we've been making to Stripe. Like these ones on here actually from me testing earlier. These ones up here, actually the AJAX call that's used to submit the credit card information and get that token. Click that one right there and you can actually see, that this is what the Stripe java script sent to Stripe, and then this was the response that came back, which included the token. Then Stripe put that in a form for us and submitted it. If I actually search for that string that we just dumped in our controller, there it is.

All right, so let's take that token and let's actually charge our user.