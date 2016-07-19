# Charge It

We're going to use stripe, you're going to communicate with the stripe API a lot, which is just a normal, restful, API.

Google for stripe API docs to find this amazing page. This is going to be your best friend because it tells you every single end point on their site, how to create charges, how to create customers, and lots of other things that we're going to talk about.

Now, first of all, make sure that you have php selected up here because then it'll show you code snippets in php, which is very nice.

On the code snippets you're going to be using their custom stripe php sdk. Google for that. Open up the GitHub page.

First thing we need to do is install this. Copy the composer require line, let's switch over. Open a new tab and paste that. While we're waiting let's go right ahead and get into the code that we need to do.

Stripes give us this stripe token. This is a temporary string that gives us access to create a charge. The stripe token basically represents the credit card information without actually being credit card information. If we send this token to stripe and say, please charge this credit card $62, stripe will do it.

The easiest way to do that is by creating what stripe calls a charge. In stripe API click charges among the left and then go down to create a charge. I'm actually just going to steal all of this example code here, because it's exactly what we need. It's going to order controller. First create a new token variable, assign that to the stripe token [queer 00:02:15] parameter. Then paste that code in there.

Let's check on our composer, perfect. Just finishing. When we come back, our editor is happening again with those things.

Notice the set API key, it once again took the API key from our account. There are actually two different API keys. There's the public key, which we saw a second ago in our template. This is actually the private or secret key. You do not want to show this to anybody. I'll be changing this after I record this.

If you're going to charge, it's very simple, let's just fill in the data here. I'm going to use symphony to fetch a service called shopping cart. Call get [total 00:03:16] on it, and multiply it by 100. Now, that's very symphony specific but the point is, this should be the amount in cents that you want to charge the user. Down here, for token this is the actual token that was just submitted. Use docs [set 00:03:33] token, then you can put whatever you want down here like, first test charge. That's it.

This will make an API request without the stripe. It will create the charge or it will throw an exception if anything fails. That's it.

The last few things I'm going to down here are not things that you have to do, that are specific our application. After we check out, I'm actually going to use another method on my shopping cart to empty the cart. Of course, those things are purchased now. I'm also going to use a special function called [inaudible 00:04:12] symphony. Say, order complete. Yay. This is just a message that will show up for the user. Again, these are extras. The one thing you definitely want to do after all of this is redirect the user to some other page.

In symphony, I'm going to say, return this error redirect to route and I'm going to redirect to the home page. Make sure your somehow redirect somewhere. That is it. Let's give this guy a try.

Let's go back to our check out. Hit enter to load the page. Put in our fake credit card, any date and cvc. Let's try it.

Hey, okay. No errors. Because there were no errors, that means it worked. If something had gone wrong, the stripe sdk should have thrown an exception, but here's where things get really cool. It's really, really easy to see if it worked. Go to your stripe dashboard, hit payments. There's a payment, $62. You can check into it. You can see all the information that was used.

Congratulations guys. In about ten minutes we just successfully charged somebody's credit card.
